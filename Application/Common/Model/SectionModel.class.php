<?php

namespace Common\Model;

class SectionModel extends TreeModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["tables"] ['banzhu'] = array (
				"table" => array (
						"keys" => array (
								array (
										"user_id int not null",
										"fenlei_id int not null comment '板块id'" ,
										"jing tinyint default 0",
										"del tinyint default 0",
										"ding tinyint default 0 comment '置顶'",
										"jinyan tinyint default 0 comment '禁言'",
								) 
						) 
				) 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
}