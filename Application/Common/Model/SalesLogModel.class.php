<?php

namespace Common\Model;

use Common\Model\KokoModel;

class SalesLogModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int",
				"city_id int",
				"create_time int",
				"order_id int",
				"money double",
				"msg varchar(1024)" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function log($user_id,$city_id, $order_id, $money, $msg = '') {
		$this->insert ( array (
				"user_id" => $user_id,
				"city_id" => $city_id,
				"create_time" => NOW_TIME,
				"money" => $money,
				"order_id" => $order_id,
				"msg" => $msg 
		) );
	}
}