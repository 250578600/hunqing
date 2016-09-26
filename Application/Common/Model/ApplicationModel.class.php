<?php

namespace Common\Model;

use Common\Model\KokoModel;

class ApplicationModel extends KokoModel {
	public $info = array (
			"state" => array (
					"",
					"agree" => 1,
					"refuse" => 2 
			),
			"stateName" => array (
					"未处理",
					"已同意",
					"已拒绝" 
			) 
	);
	public $state = array (
			"dcl" => array (
					0,
					"待处理" 
			) ,
			"agreed" => array (
					1,
					"已同意" 
			) ,
			"refused" => array (
					2,
					"已拒绝" 
			) ,
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int not null",
				"name varchar(20)",
				"telephone varchar(11)",
				"state tinyint not null default 0 comment '0未审核，1审核成功，2拒绝,3over'",
				"create_time int not null default 0",
				"admin_id int not null default 0",
				"processing_time int not null default 0 comment '审核时间'",
				"reason varchar(1024)" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function insert(&$data) {
		if (! isset ( $data ['user_id'] )) {
			$user = dbSaved ( 'user' );
			$data ['user_id'] = $user->logined_id;
		}
		$data ['create_time'] = NOW_TIME;
		return parent::insert ( $data );
	}
	public function getList($kokoWhere = null, $config = null) {
		if ($kokoWhere == null) {
			$user = dbSaved ( 'user' );
			$kokoWhere = "user_id = " . $user->logined_id;
		}
		if (! isset ( $config [2] ) || ! isset ( $config ['order'] ))
			$config ['order'] = "id desc";
		return parent::getList ( $kokoWhere, $config );
	}
	public function getLast() {
		$user = dbSaved ( 'user' );
		return $this->where ( "user_id = " . $user->logined_id )->order ( "id desc" )->find ();
	}
	public function process($where, $state, $config = null) {
		if ($state != 'agree' && $state != 'refuse') {
			\koko::jsonOut ( 0, "result error", true );
		}
		$ad = dbSaved ( 'admin' );
		$data = $this->getConfig ( "data", $config );
		$d = array (
				"state" => $this->info ['state'] [$state],
				"processing_time" => NOW_TIME,
				"admin_id" => $ad->logined_id 
		);
		if (! $data)
			$data = $d;
		else {
			$data = array_merge ( $d, $data );
		}
		$rs = $this->getConfig ( "text", $config );
		if ($rs) {
			$data ['reason'] = $rs;
		}
		$ret = $this->where ( $where )->save ( $data );
		if ($this->getConfig ( "return", $config )) {
			return $ret;
		} else {
			if ($ret !== false) {
				\koko::jsonOut ();
			} else {
				\koko::jsonOut ( 0, '操作失败' );
			}
		}
	}
}
