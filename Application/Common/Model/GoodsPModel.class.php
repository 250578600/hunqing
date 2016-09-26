<?php

namespace Common\Model;

use Common\Model\KokoModel;
use Common\Model\data\location;

class GoodsPModel extends KokoModel {
	protected $_validate = array (
			array (
					'username',
					'',
					'帐号名称已经存在！',
					0,
					'unique',
					1 
			) 
	); // 在新增的时候验证name字段是否唯一
	public $state = array (
			"待提交",
			"审核中",
			"已发布",
			"已拒绝" 
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$table = array (
				"shop_id int not null default 0 comment '商户id'",
				"owner_id int not null comment '商品上传者id'",
				"brand_id int default 0 comment '品牌id'",
				"fenlei_id int default 0",
				"title varchar(255) not null",
				"keywords varchar(255) not null",
				"description varchar(1024)",
				"thumb varchar(255) not null default ''",
				"goods_sn varchar(30) default '货号'",
				"imgs text not null default '' comment '多张图片'",
				"create_time int not null",
				"state tinyint not null default 0 comment '状态：0待提交，1审核中，2已发布，3已拒绝'",
				"deny_reason varchar(512) default ''",
				"is_on tinyint default 1 comment '0:下架，1上架'",
				"on_time int default 0 comment '上架时间'",
				"off_time int default 0 comment '下架时间'",
				"is_refined tinyint default 0 comment '0:1 加精'",
				"is_delete tinyint default 0 comment '0:1 已删除'",
				"click int default 0",
				"sales int default 0",
				"collection int default 0",
				"allow_jugde tinyint default 0 comment '0:1 允许评论'",
				"judge_num int default 0 comment '评价次数'",
				"judge_fen int default 0 comment '评价分'",
				"price double not null",
				"stock int not null comment '库存'",
				"attr text default '' comment '商品参数'",
				"freight_id int not null default 0 comment '运费模板id,id为0代表本商品包邮'",
				"info text" 
		);
		$config ['table'] ['json_keys'] [] = "imgs,attr";
		if ($this->getConfig ( 'promote', $config )) {
			$table [] = array (
					"is_promote tinyint default 0 comment '是否促销'",
					"promote_price double default 0",
					"promote_start_date int default 0",
					"promote_end_date int default 0" 
			);
		}
		if ($this->getConfig ( 'ex', $config )) {
			if (! isset ( $config ["table"] ["tables"] ["ex"] )) {
				$config ["table"] ["tables"] ['ex'] = array (
						"table" => array (
								"name" => "goods_spec",
								"keys" => array (
										array (
												"id int not null primary key auto_increment",
												"goods_id int not null",
												"specs text default ''",
												"thumb varchar(255) not null default ''",
												"price double not null default 0",
												"stock int not null default 0 comment '库存'",
												"@constraint `constraint_name` foreign key (goods_id) references @parent_table(id) on delete cascade on update cascade" 
										) 
								),
								"transpose"=>true,
								'json_keys' => array (
										"specs" 
								),
								'transpose_keys' => array (
										"specs" 
								) 
						) 
				);
				$config ['addition'] ['ex'] = 'goods_id';
			}
		}
		$config ['table'] ['keys'] [] = $table;
		$config ["table"] ["tables"] ['fenlei'] = array (
				"class" => "\Common\Model\ClassModel",
				"tableName" => 'goods_fenlei' 
		);
		$config ["table"] ["tables"] ['brand'] = array (
				"class" => "\Common\Model\TreeModel",
				"tableName" => 'goods_brand' 
		);
		$config ["table"] ["tables"] ['judge'] = array (
				"class" => "\Common\Model\CommentModel" 
		);
		$config ["table"] ["tables"] ['freight'] = array (
				"class" => "\Common\Model\FreightModel" 
		);
		$config ["table"] ["tables"] ['collection'] = array (
				"class" => "\Common\Model\CollectionModel" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function master(&$data) {
		$data ['price'] = null;
		$data ['stock'] = 0;
		foreach ( $data ['ex'] as $k => &$v ) {
			if ($data ['price'] === null || $v ['price'] < $data ['price']) {
				$data ['price'] = $v ['price'];
			}
			$data ['stock'] += $v ['$stock'];
		}
	}
	public function alterStock($id, $ex_id, $num) {
		if (! $ex_id) {
			$db = $this;
		} else {
			$db = $this->getDbEx ( 'ex' );
			$id = $ex_id;
		}
		if ($num > 0) {
			$db->where ( "id=$id" )->setInc ( 'stock', $num );
		} else {
			$db->where ( "id=$id" )->setDec ( 'stock', $num * - 1 );
		}
	}
	public function show1($id, $config = null) {
		$data = parent::show ( $id, $config );
		if ($data) {
			if ($this->checkConfig ( 'ex' )) {
				$data ['ex'] = $this->getDbEx ( 'ex' )->where ( 'goods_id=' . $data ['id'] )->select ();
				foreach ( $data ['ex'] as $k => $v ) {
					$data ['ex'] [$k] ['specs'] = json_decode ( $v ['specs'], true );
				}
			}
			if ($this->getConfig ( 'click', $config )) {
				$this->where ( 'id=' . $id )->setInc ( "click" );
				if ($this->checkConfig ( 'history' )) {
					$this->getDbEx ( 'history' )->jia ( $id );
				}
			}
		}
		return $data;
	}
	public function getList1($kokoWhere = null, $config = null) {
		$list = parent::getList ( $kokoWhere, $config );
		if ($list ['list']) {
			if ($this->checkConfig ( 'ex' )) {
				$this->addDataToArray ( $list ['list'], 'ex', 'id', $this->getDbEx ( 'ex' ), array (
						"id" => "goods_id",
						"ex" => "key" 
				) );
			}
		}
		return $list;
	}
	public function del($where) {
		if (is_numeric ( $where )) {
			$where = "id = " . $where;
		}
		if ($this->checkConfig ( 'ex' )) {
			$list = $this->where ( $where )->field ( "id" )->select ();
			$ids = array ();
			foreach ( $list as $v ) {
				$ids [] = $v ['id'];
			}
			$this->startTrans ();
			$r = $this->getDbEx ( 'ex' )->where ( 'goods_id in (' . join ( ',', $ids ) . ')' )->delete ();
			if ($r !== false) {
				$ret = $this->where ( $where )->delete ();
				if ($ret) {
					$this->commit ();
					return $ret;
				} else {
					$this->rollback ();
					\koko::jsonOut ( 0, '删除主表失败', true );
				}
			} else {
				$this->rollback ();
				\koko::jsonOut ( 0, '删除副表失败', true );
			}
		} else {
			$ret = $this->where ( $where )->delete ();
			if ($ret) {
				return $ret;
			} else {
				\koko::jsonOut ( 0, '删除失败', true );
			}
		}
	}
	public function copy() {
		if (! isset ( $_POST ['id'] ) || ! is_numeric ( $_POST ['id'] )) {
			\koko::jsonOut ( 0, "id error", true );
		}
		$data = $this->show ( $_POST ['id'] );
		unset ( $data ['id'] );
		$this->startTrans ();
		$ret = $this->add ( $data );
		if ($ret) {
			if ($this->checkConfig ( 'ex' )) {
				$exData = $this->getDbEx ( 'ex' )->where ( "goods_id=" . $_POST ['id'] )->select ();
				foreach ( $exData as &$v ) {
					unset ( $v ['id'] );
					$v ['goods_id'] = $ret;
				}
				$r = $this->getDbEx ( $ex )->addAll ( $exData );
				if (! $r) {
					$this->rollback ();
					\koko::jsonOut ( 0, "复制失败" );
				}
			}
			$this->commit ();
			\koko::jsonOut ();
		} else {
			$this->rollback ();
			\koko::jsonOut ( 0, "复制失败" );
		}
	}
	public function switcher($id, $key, $state = 1) {
		if ($key == "banner_show" && $state) {
			$ret = $this->where ( "id=" . $id )->getField ( 'banner' );
			if ($ret == '' || $ret == null)
				\koko::jsonOut ( 0, "您未上传该商品banner图，请上传后再进行操作", true );
		}
		parent::switcher ( $id, $key, $state );
	}
	public function checkExKeys($line) {
		$exKeys = $this->config ['table'] ['tables'] ['ex'] ['notEmpty'];
		foreach ( $line as $k => $v ) {
			if (isset ( $exKeys [$k] ) && $v === '') {
				return false;
			}
		}
		return true;
	}
}