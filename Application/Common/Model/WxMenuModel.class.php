<?php

namespace Common\Model;

class WxMenuModel extends TreeModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"type varchar(20)",
				"value varchar(200)" 
		);
		$config ["table"] ["tables"] ['sucai'] = array (
				"class" => "\Common\Model\ArticleModel",
				"tableName" => "sucai"
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function tmp($name = null) {
		static $cache = array ();
		if ($name == null) {
			return $cache;
		} else {
			$k = bin2hex ( $name );
			$cache [$k] = $name;
			return $k;
		}
	}
	public function get($json = false) {
		$ret = parent::show ();
		$out = array ();
		foreach ( $ret as $v ) {
			$a = array (
					'name' => $this->tmp ( $v ['name'] ) 
			);
			if ($v ['list']) {
				$a ['sub_button'] = array ();
				foreach ( $v ['list'] as $v2 ) {
					$a ['sub_button'] [] = array (
							'type' => $v2 ['type'],
							'name' => $this->tmp ( $v2 ['name'], 'set' ),
							($v2 ['type'] == 'view' ? 'url' : 'key') => $v2 ['value'] 
					);
				}
			} else {
				$a ['type'] = $v ['type'];
				$a [($a ['type'] == 'view' ? 'url' : 'key')] = $v ['value'];
			}
			$out [] = $a;
		}
		$data = array (
				"button" => $out 
		);
		$data = stripslashes ( json_encode ( $data ) );
		$cache = $this->tmp ();
		foreach ( $cache as $k => $v ) {
			$data = str_replace ( $k, $v, $data );
		}
		if ($json) {
			return $data;
		} else {
			return json_decode($data,true);
		}
	}
	public function init($data) {
		$this->truncate ();
		$this->insertData ( $data );
		$wx = \koko::getObj('wx');
		$json = $this->get ( true );
		file_put_contents('out.txt', $json);
		$ret = $wx->wxMenuCreate ( $json );
		file_put_contents('ret.txt', $ret);
		return $ret;
	}
	private function insertData($data, $parent_id = 0) {
		foreach ( $data as $v ) {
			$d = array (
					"name" => $v ['name'] 
			);
			if (! isset ( $v ['sub_button'] )) {
				$d ['type'] = $v ['type'];
				$d ['value'] = $v ['value'];
			}
			if ($parent_id) {
				$d ['parent_id'] = $parent_id;
			}
			$id = $this->insert ( $d );
			if (isset ( $v ['sub_button'] ))
				$this->insertData ( $v ['sub_button'], $id );
		}
	}
}

/*
 * $data = array (
				"button" => array (
						array (
								'name' => "心动行动",
								'sub_button' => array (
										array (
												"type" => 'view',
												'name' => "首页",
												"url" => "http://wx.cdqiai.com/" 
										),
										array (
												"type" => 'view',
												'name' => "活动",
												"url" => "http://wx.cdqiai.com/index.php/home/index/huodong" 
										),
										array (
												"type" => 'view',
												'name' => "往期内容",
												"url" => "http://mp.weixin.qq.com/mp/getmasssendmsg?__biz=MzA3NTEwMDM0Mg==#wechat_webview_type=1&wechat_redirect" 
										) 
								),
						),
						array (
								'name' => "代言人",
								'sub_button' => array (
										array (
												"type" => 'view',
												'name' => "立即购买",
												"url" => "http://wx.cdqiai.com/" 
										),
										array (
												"type" => 'view',
												'name' => "我的订单",
												"url" => "http://wx.cdqiai.com/index.php/home/index/center#order" 
										),
										array (
											//	"type" => 'view',
												"type" => 'click',
												'name' => "我的二维码",
											//	"url" => "http://wx.cdqiai.com/index.php/home/index/center#ticket" 
												"key" => "erweima"  
										),
										array (
												"type" => 'view',
												'name' => "会员中心",
												"url" => "http://wx.cdqiai.com/index.php/home/index/center" 
										)
								) 
						),
						array (
								'name' => "美丽秘方",
								'sub_button' => array (
										array (
												"type" => 'view',
												'name' => "品牌介绍",
											//	"url" => "http://www.cdqiai.com/single.aspx?m=about&id=1" 
												"url" => "http://mp.weixin.qq.com/s?__biz=MzA3NTEwMDM0Mg==&mid=402370101&idx=1&sn=c424e13d25196dfa4a114b4c8c798215#rd" 
										),
										array (
												"type" => 'click',
												'name' => "代言指南",
												"key" => "daiyan" 
										),
										array (
												"type" => 'view',
												'name' => "线下合作",
												"url" => "http://wx.cdqiai.com/index.php/home/index/page?id=4" 
										),
										array (
												"type" => 'view',
												'name' => "快递查询",
												"url" => "http://m.kuaidi100.com/" 
										) ,
										array (
												"type" => 'view',
												'name' => "微官网",
												"url" => "http://www.cdqiai.com/default.aspx?shouji.html" 
										) 
								) 
						) 
				) 
		);
 */