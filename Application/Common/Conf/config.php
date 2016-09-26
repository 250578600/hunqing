<?php
$config = array (
		// '配置项'=>'配置值'
		"checkTable" => 1,
		// 数据库配置信息
		'DB_TYPE' => 'mysql', // 数据库类型
		'DB_HOST' => 'sql.fengniaozhiku.com', // 服务器地址
		'DB_NAME' => 'hunqing', // 数据库名
		'DB_USER' => 'hunqing', // 用户名
		'DB_PWD' => 'uyEAS5rm45u9whCC', // 密码
		'DB_PORT' => 3306, // 端口
		'DB_PREFIX' => 'think_', // 数据库表前缀
		'DB_CHARSET' => 'utf8'  // 字符集
);
$config ['KokoTools'] = array (
		"loc" => "\KokoTools\location" ,
		"wx" => "\KokoTools\WxApi",
		"excel"=> "\\KokoTools\\excel",
);
return $config;