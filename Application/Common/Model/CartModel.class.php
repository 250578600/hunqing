<?php

namespace Common\Model;

use Common\Model\KokoModel;

class CartModel extends KokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int not null",
				"goods_id int not null",
				"goods_ex_id int not null",
				"num text not null",
				"create_time int not null",
				"state tinyint default 1 comment '0未勾选，1已勾选'",
				"@constraint `constraint_name` foreign key (user_id) references @parent_table(id) on delete cascade on update cascade" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	/*
	 * $data
	 */
	public function insert(&$data) { // 在添加的时候验证是否需要规格
		if (isset ( $data ['num'] )) {
			$num = intval ( $data ['num'] );
			unset ( $data ['num'] );
		} else {
			$num = 1;
		}
		$user = $this->parent;
		$data ['user_id'] = $user->logined_id;
		$where = array (
				"user_id" => $data ['user_id'] 
		);
		if (isset ( $data ['id'] )) {
			$where ["id"] = $data ['id'];
		} else {
			$where ["goods_id"] = $data ['goods_id'];
			if (isset ( $data ['goods_ex_id'] )) {
				$where ['goods_ex_id'] = $data ['goods_ex_id'];
			} else {
				$goods = dbSaved ( 'goods' );
				if ($goods->getDbEx ( 'ex' )->where ( "goods_id = " . $data ['goods_id'] )->count ()) {
					\koko::jsonOut ( 0, '您需要传入规格id', true );
				}
			}
		}
		$ret = $this->where ( $where )->find ();
		if ($ret) {
			$n = $ret ['num'] + $num;
			if ($n > 0) {
				$r = $this->where ( $data )->save ( array (
						'num' => array (
								'exp',
								'num ' . ($num > 0 ? ('+' . $num) : $num) 
						) 
				) );
			} else {
				$r = $this->where ( $data )->delete ();
			}
		} else if ($num > 0) {
			$n = $num ;
			$r = parent::insert ( $data );
			// echo '要插入数据';
		} else {
			$r = false;
		}
		if ($r === false) {
			return \koko::jsonOut ( 0, '', true );
		} else {
			return \koko::jsonOut ( 1, array (
					'msg' => "操作成功",
					"num" => $n 
			), true );
		}
	}
	public function getData($where, $goods = null) {
		$list = $this->getList ( $where, array (
				false,
				"create_time desc",
				"justList" => true 
		) );
		if ($goods) {
			$this->addDataToArray ( $list, "goods", "goods_id", $goods, array (
					'ex_config' => array (
							"ex" => true,
							"json_keys"=>true 
					) 
			) );
		}
		return $list;
	}
	public function getCount($user_id) {
		return $this->where ( "user_id=$user_id" )->count ();
	}
	public function getNum($user_id, $goods_id) {
		$data = $this->where ( "user_id=$user_id and goods_id= $goods_id" )->find ();
		return $data ['num'];
	}
}
