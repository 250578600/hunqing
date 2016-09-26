<?php

namespace Common\Model;

use Common\Model\KokoModel;

class GroupModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"name varchar(100) not null",
				"description varchar(500) not null",
				"power_ids varchar(256)",
				"point int default 0",
				"`default` tinyint default 0" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function dispose(&$data) {
		if (empty ( $data ['power_ids'] )) {
			return;
		}
		$power = new PowerModel ();
		$a = $power->getList ( "id in ({$data['power_ids']})" );
		$m = array ();
		$c = array ();
		foreach ( $a ['list'] as $v ) {
			foreach ( $v ['power'] ['Controller'] as $k1 => $v1 ) {
				foreach ( $v1 as $k2 => $v2 ) {
					$c [$k1] [$k2] = 1;
				}
			}
			foreach ( $v ['power'] ['Model'] as $k1 => $v1 ) {
				foreach ( $v1 as $k2 => $v2 ) {
					$m [$k1] [$k2] = 1;
				}
			}
		}
		$data ['power'] = array (
				"Controller" => $c,
				"Model" => $m 
		);
	}
	public function show($id, $config = null) {
		$ret = parent::show ( $id, $config );
		if (! $ret) {
			\koko::jsonOut ( 0, "该用户组不存在", true );
		}
		if ($ret && $this->getConfig ( "power", $config )) {
			$this->dispose ( $ret );
		}
		return $ret;
	}
	public function getList($kokoWhere = null, $config = null) {
		$ret = parent::getList ( $kokoWhere, $config );
		if ($this->getConfig ( "power", $config )) {
			foreach ( $ret ['list'] as &$v ) {
				$this->dispose ( $v );
			}
		}
		return $ret;
	}
}