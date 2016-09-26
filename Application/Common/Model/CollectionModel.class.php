<?php

namespace Common\Model;

use Common\Model\KokoModel;

class CollectionModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int not null",
				"object_id int not null comment '收藏对象'",
				"create_time int not null",
				"@constraint `constraint_name` foreign key (object_id) references @parent_table(id) on delete cascade on update cascade" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function insert(&$data) {
		if ($this->checkConfig ( 'repeat' ) == false && $this->checkAdded ( $data ['object_id'], $data ['user_id'] )) {
			\koko::jsonOut ( 0, "不能重复添加数据", true );
		} else {
			return parent::insert ( $data );
		}
	}
	public function getList($kokoWhere = null, $config = null) {
		$data = parent::getList ( $kokoWhere, $config );
		if ($data ['list']) {
			$obj = $this->parent;
			if (is_object ( $obj )) {
				$this->addDataToArray ( $data ['list'], 'object', 'object_id', $obj );
			}
		}
		return $data;
	}
	public function checkAdded($obj_id, $user_id) {
		$ret = $this->where ( array (
				"user_id" => $user_id,
				"object_id" => $obj_id 
		) )->field ( 'id' )->count ();
		return $ret ? true : false;
	}
}