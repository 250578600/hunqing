<?php

namespace Common\Model;

use Common\Model\KokoModel;

class CouponModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int not null",
				"money float(9,2) not null comment '面值'",
				"code varchar(20) not null comment '优惠码'",
				"create_time int not null",
				"end_time int not null comment '过期时间'",
				"is_used tinyint default 0  comment '是否已使用'",
				"freeze tinyint default 0  comment '冻结'",
				"@index index_name(code)" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function create($user_id, $money, $days) {
		$data = array (
				'user_id' => $user_id,
				'money' => $money,
				'end_time' => NOW_TIME + $days * 86400,
				'code' => md5 ( get_client_ip () . rand ( 1, 99999 ) ) 
		);
		return parent::insert ( $data );
	}
}