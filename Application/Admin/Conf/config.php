<?php
return array (
		// '配置项'=>'配置值'
		"ADMIN_RES_PATH" => '/Public/H-ui.admin_v2.4/',
		'SCAN_DIRS' => "Admin,Home",
		"POWER_STRUCT" => array (
				"Admin" => array (
						"管理员",
						array (
								"Admin/Controller",
								"Admin/Model",
								"Home/Model" 
						) 
				),
				"Home" => array (
						"用户",
						array (
								"Home/Controller",
								"Home/Model" 
						) 
				) 
		) 
);