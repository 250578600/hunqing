<?php

namespace Admin\Model;

use Common\Model\TreeModel;

class AdminMenuModel extends TreeModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		if (isset ( $config ["table"] ["name"] ) == false) {
			$config ["table"] ["name"] = "admin_menu";
		}
		$config ["table"] ["keys"] [] = array (
				"url varchar(128) default ''" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
		if ($this->db) {
			$this->truncate ();
			if ($this->count () == 0) {
				$this->init ();
			}
		}
	}
	public function init() {
		$data = array (
				array (
						'name' => '商品管理',
						'url' => 'Goods',
						'children' => array (
								array (
										'name' => '商品列表',
										'url' => 'listing' 
								),
								array (
										'name' => '仓库商品',
										'url' => 'depot' 
								),
								array (
										'name' => '商品分类',
										'url' => 'fenlei' 
								),
								array (
										'name' => '商品参数管理',
										'url' => 'attr' 
								),
								array (
										'name' => '商品规格管理',
										'url' => 'spec' 
								),
								array (
										'name' => '运费管理',
										'url' => 'freight' 
								),
								array (
										'name' => '评价管理',
										'url' => 'judge' 
								) 
						) 
				),
				array (
						'name' => '会员管理',
						'url' => 'Member',
						'children' => array (
								array (
										'name' => '会员列表',
										'url' => 'listing' 
								),
								array (
										'name' => '用户组',
										'url' => 'group' 
								),
								array (
										'name' => '提现申请',
										'url' => 'tixian' 
								),
								array (
										'name' => '会员资金记录',
										'url' => 'listing_money' 
								),
								array (
										'name' => '优惠券管理',
										'url' => 'coupon' 
								) 
						) 
				),
				array (
						'name' => '订单管理',
						'url' => 'Order',
						'children' => array (
								array (
										'name' => '订单列表',
										'url' => 'index' 
								),
								array (
										'name' => '退货订单',
										'url' => 'tuihuo' 
								),
								array (
										'name' => '销售统计',
										'url' => 'listing_sales' 
								) 
						) 
				),
				array (
						'name' => '内容管理',
						'url' => 'Article',
						'children' => array (
								array (
										'name' => '内容管理',
										'url' => 'listing' 
								),
								array (
										'name' => '栏目管理',
										'url' => 'category' 
								),
								array (
										'name' => '评论管理',
										'url' => 'comment' 
								) 
						) 
				),
				array (
						'name' => '微信管理',
						'url' => 'Wechat',
						'children' => array (
								array (
										'name' => '菜单管理',
										'url' => 'menu' 
								) 
						) 
				),
				array (
						'name' => '广告管理',
						'url' => 'Adver',
						'children' => array (
								array (
										'name' => '广告列表',
										'url' => 'listing' 
								) 
						) 
				),
				array (
						'name' => '文章管理',
						'url' => 'Article',
						'freeze' => 1,
						'children' => array (
								array (
										'name' => '公告管理',
										'url' => 'listing' 
								) 
						) 
				),
				array (
						'name' => '管理员管理',
						'url' => 'Admin',
						'children' => array (
								array (
										'name' => '账号列表',
										'url' => 'listing' 
								),
								array (
										'name' => '用户组',
										'url' => 'group' 
								),
								array (
										'name' => '权限配置',
										'url' => 'power' 
								) 
						) 
				),
				array (
						'name' => '网站设置',
						'url' => 'Index',
						'children' => array (
								array (
										'name' => '菜单管理',
										'url' => 'menu' 
								),
								array (
										'name' => '系统设置',
										'url' => 'setting' 
								),
								array (
										'name' => '分销设置',
										'url' => 'fenxiao',
										'freeze' => 1 
								),
								array (
										'name' => '微信客服',
										'url' => 'https://mpkf.weixin.qq.com/' 
								),
								array (
										'name' => '留言管理',
										'url' => 'message' 
								) 
						) 
				) 
				,
				array (
						'name' => '论坛管理',
						'url' => 'Forum',
						'children' => array (
								array (
										'name' => '帖子列表',
										'url' => 'listing' 
								),
								array (
										'name' => '板块管理',
										'url' => 'group' 
								),
						) 
				),
		);
		$this->insertData ( $data );
	}
	private function insertData($data, $parent_id = 0, $permission = null) {
		foreach ( $data as $v ) {
			$d = array (
					"name" => $v ['name'] 
			);
			if (isset ( $v ['url'] )) {
				$d ['url'] = $v ['url'];
			}
			if (isset ( $v ['permission'] )) {
				$permission = $d ['permission'] = $v ['permission'];
			} elseif ($permission !== null) {
				$d ['permission'] = $permission;
			}
			if ($parent_id) {
				$d ['parent_id'] = $parent_id;
			}
			if (isset ( $v ['freeze'] )) {
				$d ['freeze'] = $v ['freeze'];
			}
			$id = $this->insert ( $d );
			if (isset ( $v ['children'] ))
				$this->insertData ( $v ['children'], $id, $permission );
		}
	}
	private function validate(&$data, $right, $controller = null) {
		foreach ( $data as $k => &$v ) {
			if ($v ['list']) {
				$this->validate ( $v ['list'], $groupid, $v ['url'] );
			} else {
				if (substr ( $v ['url'], 0, 7 ) == 'http://' || substr ( $v ['url'], 0, 8 ) == 'https://') {
					continue;
				} else {
					if (strpos ( $v ['url'], "/" ) !== false) {
						$url = explode ( "/", $v ['url'] );
						if (count ( $url ) == 2 && $url [0] != '' && $url [1] != '') {
							list ( $controller, $v ['url'] ) = $url;
						} else {
							unset ( $data [$k] );
							echo $v ['url'] . "格式错误";
						}
					}
					$ctrl = MODULE_NAME . '\\Controller\\' . $controller . 'Controller';
					if ($right !== null && isset ( $right [$ctrl] [$v ['url']] ) == false) {
						unset ( $data [$k] );
					} else {
						$v ['url'] = U ( $controller . '/' . $v ['url'] );
					}
				}
			}
		}
	}
	public function getMenu($id = 0) {
		$data = parent::show ( $id );
		$groupid = $this->parent->data_ ['groupid'];
		$right = null;
		if ($groupid) {
			$power = $this->parent->getGroupPower ();
			if (isset ( $power ['power'] ['Controller'] )) {
				$right = $power ['power'] ['Controller'];
			} else {
				$right = array ();
			}
		}
		$this->validate ( $data, $right );
		return $data;
	}
}

?>