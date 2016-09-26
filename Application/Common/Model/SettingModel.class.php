<?php

namespace Common\Model;

use Common\Model\KokoModel;

class SettingModel extends KokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"name varchar(100) not null unique",
				"value varchar(1024) not null" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
		if ($this->count () == 0) {
			$this->init ();
		}
	}
	public function get($name = null) {
		if ($name == null) {
			$ret = $this->getField ( "name,value" );
			return $ret;
		} else {
			$ret = $this->where ( "name = '$name'" )->getField ( "value" );
			return $ret;
		}
	}
	public function set($name, $value = null) {
		S ( 'setting', null );
		if (is_array ( $name )) {
			$ret_faild= array ();
			foreach ( $name as $k => $v ) {
				$r = $this->set ( $k, $v );
				if ($r === false) {
					$ret_faild [] = $v;
				}
			}
			$ret_len = count ( $ret_faild );
			$name_len = count ( $name );
			if ($ret_len == $name_len) {
				\koko::jsonOut ( 0, '', true );
			} else if ($ret_len == 0) {
				\koko::jsonOut ();
			} else {
				\koko::jsonOut ( 1, "部分保存失败:" . join ( ",", $ret_faild ), true );
			}
			return;
		} else {
			$d = $this->where ( "name = '$name'" )->find ();
			if ($d) {
				return $this->where ( "id = " . $d ['id'] )->setField ( "value", $value );
			} else {
				return $this->add ( array (
						"name" => $name,
						"value" => $value 
				) );
			}
		}
	}
	public function init() {
		$default = array (
				"web_name" => "我的小站",
				"index_title" => "这个是首页的标题",
				"keywords" => "",
				"description" => "",
				"upload_path" => "/Public/upload",
				"copyright" => "中国制造",
				"ipc" => "88888888",
				"script" => "",
				"smtp_host" => "smtp.qq.com",
				"smtp_port" => "25",
				"email_account" => "132456@qq.com",
				"email_password" => "123456",
				"admin_email" => "123456@qq.com",
				"wx_appid" => 'wxb0aa2e57f7f01749',
				"wx_mchid" => '100009',
				"wx_appsecret" => '3ded1a83616400b3cda875e71006b086',
				"wx_secretkey" => '3ded1a83616400b3cda875e71006b086' 
		);
		foreach ( $default as $k => $v ) {
			$this->set ( $k, $v );
		}
	}
}
