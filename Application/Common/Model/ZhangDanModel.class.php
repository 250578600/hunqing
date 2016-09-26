<?php

namespace Common\Model;

use Common\Model\KokoModel;

class ZhangDanModel extends kokoModel {
	public $type = array (
			"提成" ,
			"消费" ,
			"提现" ,
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int",
				"type tinyint",
				"create_time int",
				"money double",
				"msg varchar(1024)" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function log($user_id, $type, $money, $msg = '') {
		$this->insert ( array (
				"user_id" => $user_id,
				"type" => $type,
				"create_time" => NOW_TIME,
				"money" => $money,
				"msg" => $msg 
		) );
	}
}