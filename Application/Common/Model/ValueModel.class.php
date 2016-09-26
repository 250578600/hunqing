<?php

namespace Common\Model;

use Common\Model\KokoModel;

class ValueModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"k varchar(1024)",
				"v varchar(1024) not null",
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
}