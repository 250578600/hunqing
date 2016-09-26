<?php

namespace Common\Model;

use Common\Model\KokoModel;

class ForumModel extends kokoModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {

		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"fenlei_id int not null comment '板块id'",
				"user_id int not null comment '发帖人'",
				"title varchar(100) not null",
				"create_time int not null",
				"reply_time int not null",
				"top_time int not null",
				"is_jing tinyint not null comment '是否加精'" 
		);
		$config ['table'] ['tables'] ['ex'] = array (
				"table" => array (
						"keys" => array (
								array (
										"post_id int not null comment '帖子id'",
										"user_id int not null comment '回复人'",
										"at_id int not null",
										"reply_time int not null",
										"msg text not null",
										"@index index_name(user_id)",
										"@constraint `constraint_name` foreign key (post_id) references @parent_table(id) on delete cascade on update cascade" 
								) 
						) 
				) 
		);
		$config ['table'] ['tables'] ['jinyan'] = array (
				"table" => array (
						"keys" => array (
								array (
										"user_id int not null",
										"type tinyint not null comment '0:全论坛禁言,1:板块禁言,2:帖子禁言'",
										"val int not null",
										"end_time int not null" 
								) 
						) 
				) 
		);
		$config ['table'] ['tables'] ['zan'] = array (
				"class" => "\Common\Model\CollectionModel" 
		);
		$config ['table'] ['tables'] ['collection'] = array (
				"class" => "\Common\Model\CollectionModel" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function zan($u){
		
	}
}