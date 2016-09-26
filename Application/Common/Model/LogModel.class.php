<?php

namespace Common\Model;

use Common\Model\KokoModel;

class LogModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int",
				"create_time int",
				"value double",
				"msg varchar(100)" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function log($user_id, $money, $msg = '') {
		$this->insert ( array (
				"user_id" => $user_id,
				"create_time" => NOW_TIME,
				"value" => $money,
				"msg" => $msg
		) );
	}
}