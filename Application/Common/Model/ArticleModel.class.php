<?php

namespace Common\Model;

use Common\Model\KokoModel;

class ArticleModel extends KokoModel {
	public $state = array (
			"待审核",
			"已发布",
			"已拒绝" 
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int default 0 comment '发布者id'",
				"category_id int default 0 comment '栏目id'",
				"create_time int not null",
				"title varchar(256) not null",
				"thumb varchar(256)",
				"keywords varchar(256)",
				"description varchar(1024)",
				"state tinyint not null default 0 comment '状态：0待审核，1已发布，2已拒绝'",
				"deny_reason varchar(512) default ''",
				"freeze tinyint default 0  comment '冻结'",
				"click int default 0",
				"content text" 
		);
		$config ["table"] ["tables"] ['category'] = array (
				"class" => "\Common\Model\TreeModel" 
		);
		$config ["table"] ["tables"] ['comment'] = array (
				"class" => "\Common\Model\CommentModel"
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
}

?>