<?php

namespace Admin\Model;

use Common\Model\MemberModel;

class AdminModel extends MemberModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ['weixinLogin'] = true;
		$config ["table"] ['noParents'] = true;
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"username varchar(30) not null unique",
				"telephone varchar(20)",
				"email varchar(30) default ''",
				"password varchar(32) not null",
				"thumb varchar(200)",
				"sex tinyint default 0",
				"create_time int not null",
				"login_time int not null",
				"groupid int(2) not null comment '0:最高管理员'",
				"freeze tinyint default 0  comment '冻结'",
				"state tinyint default 0  comment '用户状态'",
				"deny_reason varchar(512) default ''",
				"info varchar(512) default ''",
				// ////////////////////////////////////////////////////////////
				"openid varchar(50) default ''" 
		);
		$config ["table"] ["tables"] ['power'] = array (
				"class" => "\Common\Model\PowerModel",
				"tableName" => "power" 
		);
		$config ["table"] ["tables"] ['menu'] = array (
				"class" => "\Admin\Model\AdminMenuModel",
				"tableName" => "admin_menu" 
		);
		$config ['loginType'] = array (
				"username" 
		);
		$config ['checkLogin'] = array (
				"code" => true 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
		if ($this->db && $this->where ( "username = 'admin'" )->count () == 0) {
			$this->register ( array (
					"username" => 'admin',
					'password' => '123456',
					'groupid' => 0 
			) );
		}
	}
	public function show($id, $o = null) {
		$data = parent::show ( $id );
		if ($data) {
			$data ['images'] = unserialize ( $data ['images'] );
			if (S ( "kokoShow" )) {
				$data = $o;
			}
		}
		return $data;
	}
}