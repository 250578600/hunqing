<?php

namespace Common\Model;

use Common\Model\KokoModel;

class AddressModel extends KokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		if (isset ( $config ["table"] ["name"] ) == false) {
			$config ["table"] ["name"] = "address";
		}
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int not null",
				"name varchar(256) not null",
				"telephone varchar(11) default ''",
				"province int default 0",
				"city int default 0",
				"county int default 0",
				"province_ varchar(10) default 0",
				"city_ varchar(10) default 0",
				"county_ varchar(15) default 0",
				"address varchar(256) default ''",
				"`default` tinyint not null default 0 comment '1默认选中，0非默认'"  ,
				"@constraint `constraint_name` foreign key (user_id) references @parent_table(id) on delete cascade on update cascade" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	private function addName(&$data) {
		$loc = \koko::getObj('loc')->get ();
		$data ['province_'] = $loc ['0'] [$data ['province']];
		$data ['city_'] = $loc ['0,' . $data ['province']] [$data ['city']];
		$data ['county_'] = $loc ['0,' . $data ['province'] . ',' . $data ['city']] [$data ['county']];
	}
	public function insert(&$data) {
		$this->addName ( $data );
		$this->startTrans ();
		if (! isset ( $data ['user_id'] )) {
			$user = dbSaved ( 'user' );
			$data ['user_id'] = $user->logined_id;
		}
		
		if ($data ['default'] == 1) {
			$this->where ( "user_id=" . $data ['user_id'] )->save ( array (
					"default" => 0 
			) );
		}
		$id = parent::insert ( $data );
		if ($id) {
			$this->commit ();
			return $id;
		} else {
			$this->rollback ();
			$this->jsonOut ( 0, "插入失败", true );
		}
	}
	public function update($where, $data) {
		$this->addName ( $data );
		$this->startTrans ();
		if (! isset ( $where ['user_id'] )) {
			$user = dbSaved ( 'user' );
			$where ['user_id'] = $user->logined_id;
		}
		if ($data ['default'] == 1) {
			$this->where ( "user_id=" . $where ['user_id'] )->save ( array (
					"default" => 0 
			) );
		}
		$ret = parent::update ( $where, $data );
		if ($ret !== false) {
			$this->commit ();
			return true;
		} else {
			$this->jsonOut ( - 1, "修改失败", true );
		}
	}
	public function getAddresses($user_id = null) {
		if ($user_id == null) {
			$user = dbSaved ( 'user' );
			$user_id = $user->logined_id;
		}
		return $this->where ( "user_id=" . $user_id )->select ();
	}
	public function setDefault($id, $user_id = null) {
		if ($user_id == null) {
			$user = dbSaved ( 'user' );
			$user_id = $user->logined_id;
		}
		$this->startTrans ();
		$ret = $this->where ( "id = $id and user_id=" . $user_id )->count ();
		if ($ret == 0) {
			$this->rollback ();
			$this->jsonOut ( 0, "更新的id错误", true );
		}
		$this->where ( "user_id=" . $user_id )->setField ( "default", 0 );
		$ret = $this->where ( "id = $id and user_id=" . $user_id )->setField ( "default", 1 );
		if ($ret !== false) {
			$this->commit ();
		} else {
			$this->rollback ();
		}
		return $ret;
	}
	public function getAddress($user_id = null) {
		if ($user_id == null) {
			$user = dbSaved ( 'user' );
			$user_id = $user->logined_id;
		}
		$addr = $this->where ( "`default`=1 and user_id=" . $user_id )->find ();
		if ($addr == null) {
			$addrs = $this->getAddresses ( $user_id );
			if (count ( $addrs ) > 0)
				$addr = $addrs [0];
		}
		return $addr;
	}
}