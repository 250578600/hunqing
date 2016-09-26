<?php

namespace Common\Model;

use Common\Model\KokoModel;

class AdverExModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"adver_id int not null",
				"title varchar(100)",
				"src varchar(500) not null",
				"href varchar(100)",
				"paixu smallint default 0",
				"freeze tinyint not null default 0",
				"@constraint `constraint_name` foreign key (adver_id) references @parent_table(id) on delete cascade on update cascade" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function switcher($id, $key, $value = null) {
		\koko::jsonOut ( 'save' );
		$ret = parent::switcher ( $id, $key, $value );
		if ($ret ['state'] == 1) {
			$pid = $this->where ( "id=" . $id )->getField ( "adver_id" );
			$this->parent->where ( "id=$pid" )->setField ( "num_show", $this->where ( "adver_id = $pid and freeze = 0" )->count () );
		}
		\koko::jsonOut ( "out" );
		return $ret;
	}
	public function insert(&$data) {
		$ret = parent::insert ( $data );
		if ($ret) {
			$adver_id = $data ['adver_id'];
			$this->parent->where ( "id=$adver_id" )->save ( array (
					"num" => $this->where ( "adver_id=$adver_id" )->count (),
					"num_show" => $this->where ( "adver_id=$adver_id and freeze = 0" )->count () 
			) );
		}
		return $ret;
	}
	public function del($id) {
		$adver_id = $this->where ( $where )->getField ( "adver_id" );
		$ret = parent::del ( $id );
		if ($ret) {
			$this->parent->where ( "id=$adver_id" )->save ( array (
					"num" => $this->where ( "adver_id=$adver_id" )->count (),
					"num_show" => $this->where ( "adver_id=$adver_id and freeze = 0" )->count () 
			) );
		}
		return $ret;
	}
}