<?php

namespace Common\Model;

use Common\Model\KokoModel;

class LimitModel extends KokoModel {
	protected $_validate = array (
			array (
					'keyword',
					'',
					'不能重复操作',
					0,
					'unique',
					1 
			) 
	); // 在新增的时候验证name字段是否唯一
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"keyword varchar(50) not null primary key",
				"create_time int default 0",
				"run int default 1",
				"top int default 1" 
		);
		$config ['table'] ['noParents'] = true;
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function run($key) {
		$data ['keyword'] = $key;
		$data ['create_time'] = NOW_TIME;
		if ($this->where ( "keyword = '$key'" )->count ()) {
			return $this->where ( "keyword = '$key'" )->setInc ( "run" );
		}
		return parent::insert ( $data );
	}
	public function checkExisted($key) {
		return $this->where ( "keyword = '$key'" )->count () ? 1 : 0;
	}
}

?>