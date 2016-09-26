<?php

namespace Common\Model;

use Common\Model\KokoModel;

class TreeModel extends KokoModel {
	public $rules = array (
			array (
					"parent_id",
					"number",
					"id必须是数字" 
			) 
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"name varchar(256) not null",
				"parent_id int default 0",
				"depth int default 1 comment '节点深度'",
				"children text default ''",
				"paixu int default 0",
				"freeze tinyint(1) default 0 comment '1隐藏,0显示'",
				"@index index_name (depth)" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function insert(&$data) {
		$this->startTrans ();
		if (isset ( $data ['parent_id'] ) && $data ['parent_id'] != 0) {
			$p = $this->where ( "id=" . $data ['parent_id'] )->find ();
			if ($p == null) {
				$this->rollback ();
				\koko::jsonOut ( - 1, "父id:{$data['parent_id']}不存在", true );
			}
			$data ["depth"] = $p ['depth'] + 1;
		}
		$id = parent::insert ( $data, $this->rules );
		if ($id && isset ( $data ['parent_id'] ) && $data ['parent_id'] != 0) {
			$children = $this->setChildren ( $p ['children'], $id, '+' );
			$ret = $this->where ( "id=" . $data ['parent_id'] )->save ( array (
					"children" => $children 
			) );
			if (! $ret) {
				$this->rollback ();
				\koko::jsonOut ( - 1, "父id:{$data['parent_id']}保存子节点失败", true );
			}
		}
		if ($id) {
			$this->commit ();
			return $id;
		} else {
			$this->rollback ();
			\koko::jsonOut ( - 1, "插入失败", true );
		}
	}
	public function update($where, $data) {
		$this->startTrans ();
		if (isset ( $data ['parent_id'] )) {
			$pid = $data ['parent_id'];
			$cur = $this->where ( $where )->find ();
			// /////// 设置旧的父id的children
			$p_old = $this->where ( "id=" . $cur ['parent_id'] )->find ();
			$children = $this->setChildren ( $p_old ['children'], $cur ['id'] );
			$this->where ( "id=" . $cur ['parent_id'] )->save ( array (
					"children" => $children 
			) );
			// /////// 设置新的父id的children
			if ($pid > 0) {
				$p_new = $this->where ( "id=$pid" )->find ();
				$children = $this->setChildren ( $p_new ['children'], $cur ['id'], '+' );
				$this->where ( "id=$pid" )->save ( array (
						"children" => $children 
				) );
			} else {
				$p_new ['depth'] = 0;
			}
			if ($p_old ['depth'] != $p_new ['depth']) {
				$data ['depth'] = $p_new ["depth"] + 1;
				$this->updateChildren ( $$cur ['children'], $data ['depth'] + 1 );
			}
		}
		$ret = parent::update ( $where, $data, $this->rules );
		if ($ret !== false) {
			$this->commit ();
			return true;
		} else {
			\koko::jsonOut ( - 1, "修改失败", true );
		}
	}
	public function del($where, $delChildren = true) {
		$this->startTrans ();
		if (is_numeric ( $where )) {
			$where = "id = " . $where;
		}
		$me = $this->where ( $where )->find ();
		if ($me) {
			$r = $this->where ( $where )->delete ();
			if ($r) {
				if ($me ['parent_id'] != 0) { // 不是顶级节点
					$p = $this->where ( 'id=' . $me ['parent_id'] )->find ();
					$children = $this->setChildren ( $p ['children'], $me ["id"] );
					if (! $delChildren && $me ['children']) {
						$children .= ($children ? ',' : '') . $me ['children'];
					}
					$r2 = $this->where ( 'id=' . $me ['parent_id'] )->save ( array (
							'children' => $children 
					) );
					if (! $r2) {
						$this->rollback ();
						\koko::jsonOut ( - 1, "父id:{$me['parent_id']}保存子节点失败", true );
					}
				} else { // 顶级节点
				}
				if ($delChildren) {
					$this->delChildren ( $me ['children'] );
				} else {
					if ($me ['children']) {
						$this->where ( "id in ({$me['children']})" )->save ( array (
								"parent_id" => $me ['parent_id'] 
						) );
						$this->updateChildren ( $me ['children'], $me ['depth'] );
					}
				}
				$this->commit ();
				return true;
			} else {
				$this->rollback ();
				\koko::jsonOut ( - 1, "删除失败", true );
			}
		} else {
			$this->rollback ();
			\koko::jsonOut ( - 1, "该数据不存在", true );
		}
	}
	private function setChildren($children, $id, $op = '-') {
		if ($op == "-") {
			$children = explode ( ",", $children );
			foreach ( $children as $k => $v ) {
				if ($v == $id) {
					unset ( $children [$k] );
					break;
				}
			}
			return join ( ",", $children );
		} else {
			if ($children == '') {
				$children = $id;
			} else {
				$children .= "," . $id;
			}
			return $children;
		}
	}
	private function updateChildren($children, $depth) { // 更新深度
		if ($children) {
			$this->where ( "id in ($children)" )->setField ( "depth", $depth );
			$childrenList = $this->where ( "id in ($children)" )->select ();
			foreach ( $childrenList as $v ) {
				$this->updateChildren ( $v ['children'], $depth + 1 );
			}
		}
	}
	private function delChildren($children) {
		if ($children) {
			$childrenList = $this->where ( "id in ($children)" )->select ();
			$this->where ( "id in ($children)" )->delete ();
			foreach ( $childrenList as $v ) {
				$this->delChildren ( $v ['children'] );
			}
		}
	}
	public function show($id = 0, $config = null) { // id 是节点 获取id节点下面的所有数据
		$data = $this->where ( $this->getConfig ( 'showAll', $config ) ? '1' : 'freeze = 0' )->order ( "depth,paixu" )->select ();
		$data = \koko::turnIdToKey ( $data );
		$tops = array ();
		foreach ( $data as $v ) {
			if ($v ['parent_id'] == $id)
				$tops [] = $v ['id'];
		}
		$out = $this->getChildren ( $data, join ( ",", $tops ) );
		if ($config === true) { // 显示根节点
			$data [$id] ['list'] = $out;
			return $data [$id];
		}
		return \koko::turnIdToKey ( $out );
	}
	private function getChildren(&$data, $children) {
		if ($children) {
			$childrenList = array ();
			$children = explode ( ",", $children );
			foreach ( $children as $v ) {
				$childrenList [] = $data [$v];
			}
			foreach ( $childrenList as &$v ) {
				$v ['list'] = \koko::turnIdToKey ( $this->getChildren ( $data, $v ['children'] ) );
			}
			return $childrenList;
		} else
			return null;
	}
	public function turnToLine($data, &$out) {
		foreach ( $data as $v ) {
			$children = $v ['list'];
			unset ( $v ['list'] );
			$out [$v ['id']] = $v;
			if ($children) {
				$this->turnToLine ( $children, $out );
			}
		}
	}
	public function switcher($id, $key, $value = null) {
		\koko::jsonOut ( 'save' );
		$ret = parent::switcher ( $id, $key, $value );
		if ($key == 'freeze' && $ret ['state']) {
			$this->where ( "parent_id=".$id['id'] )->setField ( 'freeze', $ret ['msg'] );
		}
		\koko::jsonOut ( 'out' );
	}
}
