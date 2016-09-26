<?php

namespace Common\Model;

use Common\Model\KokoModel;

class ImagesModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int",
				"title varchar(100)",
				"src varchar(500)",
				"href varchar(100)",
				"paixu int default 0" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
}