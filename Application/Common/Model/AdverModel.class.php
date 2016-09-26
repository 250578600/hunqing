<?php

namespace Common\Model;

use Common\Model\KokoModel;

class AdverModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"name varchar(100) not null",
				"num int not null default 0",
				"num_show int not null default 0",
				"freeze tinyint not null default 0" 
		);
		$config ['check_can_del'] = true;
		$config ["table"] ["tables"] ['ex'] = array (
				"class" => "\Common\Model\AdverExModel",
				"tableName" => 'adver_ex' 
		);
		$config ['addition'] ['ex'] = 'adver_id';
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function show($id, $config = array()) {
		$data = parent::show ( $id, $config );
		if ($data) {
			$data ['ex'] = $this->getDbEx ( 'ex' )->where ( "adver_id = " . $data ['id'] . " and freeze = 0" )->order ( "paixu desc" )->select ();
		}
		return $data;
	}
}