<?php

namespace Common\Model;

use Common\Model\KokoModel;

class AttributeModel extends KokoModel {
	public $info = array (
			"type" => array (
					"text" => "文本框",
					"number" => "文本框(仅限数字)",
					"radio" => "单选框",
					"checkbox" => "多选框",
					"select" => "下拉框" 
			) 
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"name varchar(50) not null comment '规格名称'",
				"keyname varchar(50) default '' comment '规格键名'",
				"type varchar(50) not null comment '输入类型eg:text radio select checkbox'",
				"`values` text default '' comment '可输入的值'" 
		);
		$ut = $this->getConfig ( 'unset_type', $config );
		if ($ut) {
			$ut = explode ( ",", $ut );
			foreach ( $ut as $v ) {
				unset ( $this->info ['type'] [$v] );
			}
		}
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
}
