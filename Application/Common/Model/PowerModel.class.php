<?php

namespace Common\Model;

use Common\Model\KokoModel;

class PowerModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"name varchar(100) not null",
				"target varchar(10) not null default 0 comment 'Admin:管理员,Home:用户'",
				"description varchar(500) not null",
				"power text" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function show($id, $config = null) {
		$ret = parent::show ( $id, $config );
		if ($ret) {
			$ret ['power'] = json_decode ( $ret ['power'] );
		}
		return $ret;
	}
	public function getList($kokoWhere = null, $config = null) {
		$ret = parent::getList ( $kokoWhere, $config );
		foreach ( $ret ['list'] as &$v ) {
			$v ['power'] = json_decode ( $v ['power'], true );
		}
		return $ret;
	}
	public function update($where, $data) {
		if (isset ( $data ['target'] )) {
			unset ( $data ['target'] );
		}
		return parent::update ( $where, $data );
	}
}