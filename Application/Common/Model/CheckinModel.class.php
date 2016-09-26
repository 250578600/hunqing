<?php

namespace Common\Model;

use Common\Model\KokoModel;

class CheckinModel extends kokoModel {
	public $user = null;
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int not null",
				"date int" 
		);
		$this->user = dbSaved ( 'user' );
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function checkToday() {
		if ($this->where ( "user_id = " . $this->user->logined_id . " and date = " . strtotime ( date ( 'Y-m-d' ) ))->count ()) {
			return true;
		} else {
			return false;
		}
	}
	public function checkIn() {
		if ($this->checkToday ()) {
			\koko::jsonOut ( 0, "今天已经签到了", true );
		}
		return $this->add ( array (
				"user_id" => $this->user->logined_id,
				"date" => strtotime ( date ( 'Y-m-d' ) )
		) );
	}
	public function getHistory() {
		return $this->where ( "user_id = " . $this->user->logined_id )->select ();
	}
}