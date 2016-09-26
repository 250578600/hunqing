<?php

namespace Common\Model;

use Common\Model\KokoModel;

class CommentModel extends KokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$table = array (
				"id int not null primary key auto_increment",
				"user_id int not null",
				"object_id int not null comment '评论对象'",
				"msg text not null",
				"create_time int not null",
				"stars tinyint default 0 not null",
				"at_id int default 0 not null",
				"freeze tinyint not null default 0" 
		);
		if ($this->checkConfig ( "no_foreign", $config )==false) {
			$table [] = "@constraint `constraint_name` foreign key (object_id) references @parent_table(id) on delete cascade on update cascade";
		}
		$config ["table"] ["keys"] [] = $table;
		$config ['table'] ['noParents'] = true;
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
}

?>