<?php

namespace Common\Model;

use Common\Model\KokoModel;

class NotifyModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int",
				"create_time int",
				"view_time int default 0",
				"msg varchar(100)" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function notify($msg, $user_id) {
		$this->insert ( array (
				"user_id" => $user_id,
				"create_time" => NOW_TIME,
				"msg" => $msg 
		) );
	}
	public function view($id) {
		$data = $this->find ( $id );
		if ($data ['view_time'] == 0) {
			$user = dbSaved ( 'user' );
			$user_id = $user->logined_id;
			if ($user_id == $data ['user_id']) {
				$this->where ( "id=$id" )->setField ( "view_time", NOW_TIME );
			}
		}
		return $data;
	}
}