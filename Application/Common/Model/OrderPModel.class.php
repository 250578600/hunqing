<?php

namespace Common\Model;

class OrderPModel extends KokoModel implements inter\OrderInterface {
	public $goods = null;
	public $state = array (
			"dfk" => array (
					0,
					"待付款" 
			),
			"dfh" => array (
					1,
					"待发货" 
			),
			"dsh" => array (
					2,
					"待收货" 
			),
			"dpj" => array (
					3,
					"待评价" 
			),
			"over" => array (
					4,
					"已完成" 
			),
			"closed" => array (
					5,
					"已关闭" 
			) 
	);
	public $payType = array (
			"yue" => array (
					0,
					"余额支付" 
			),
			"alipay" => array (
					1,
					"支付宝" 
			),
			"alipaywap" => array (
					2,
					"支付宝移动" 
			),
			"weixin" => array (
					3,
					"微信公众支付" 
			),
			"weixinQR" => array (
					4,
					"微信二维码支付" 
			),
			"xingye" => array (
					5,
					"兴业微信支付" 
			),
			"xingye_bank" => array (
					6,
					"兴业银行支付" 
			) 
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int not null",
				"order_sn varchar(30) not null",
				"pay_sn varchar(30) not null",
				"is_return tinyint default 0 comment '是否处于退款中'",
				"return_id int not null default 0 comment '退货id'",
				"pay_type tinyint not null default 0 comment '0余额支付,1:支付宝,2:支付宝移动版,3:微信支付,4:微信二维码'",
				"sum double default 0 not null comment '商品总金额'",
				"total double default 0 not null comment '支付总金额'",
				"num int not null",
				"create_time int not null",
				"pay_time int not null default 0 comment '支付时间'",
				"over_time int not null default 0 comment '完成时间'",
				"state tinyint default 0 comment '0:未付款 1:已付款未发货 2:已发货 3:已收货未评价'",
				"checked tinyint default 0 comment '0 :管理员未查看,1已查看'",
				"msg varchar(1024) comment '留言'",
				"del tinyint default 0 comment '是否已删除'",
				"@constraint `constraint_name` foreign key (user_id) references @parent_table(id) on delete cascade on update cascade" 
		// "@index index_name(user_id)",
				);
		if (isset ( $config ["delivery"] ) && $config ["delivery"]) {
			$len = count ( $config ["table"] ["keys"] );
			$config ["table"] ["keys"] [$len - 1] = array_merge ( $config ["table"] ["keys"] [$len - 1], array (
					"shouhuo_days tinyint not null default 0",
					"fahuo_time int not null default 0",
					"shouhuo_time int not null default 0",
					"freight double default 0 not null comment '运费'",
					"freight_info varchar(1000) default '' not null comment '运费信息'",
					"baoyou tinyint default 0",
					"name varchar(10) not null default ''",
					"telephone varchar(11) not null default ''",
					"province varchar(10) not null default ''",
					"city varchar(10) not null default ''",
					"county varchar(15) default ''",
					"address varchar(128) not null default ''",
					"waybill varchar(100) default ''",
					"waybill_check_time int default 0",
					"waybill_data text default ''",
					"tuihuo text default ''" 
			) );
		}
		if (! isset ( $config ["table"] ["tables"] ["ex"] )) {
			$config ["table"] ["tables"] ['ex'] = array (
					"table" => array (
							"name" => "order_ex",
							"keys" => array (
									array (
											"order_id int not null",
											"goods_id int not null",
											"goods_ex_id int not null default 0",
											"snapshot_id varchar(40) not null",
											"title varchar(256)",
											"thumb varchar(256)",
											"num int not null default 0",
											"price double not null default 0",
											"judged tinyint(1) default 0 comment '0:未评论，1:已评论'" 
									) 
							) 
					) 
			);
			$config ['addition'] ['ex'] = 'order_id';
		}
		$config ["table"] ["tables"] ['snapshot'] = array (
				"table" => array (
						"name" => "snapshot",
						"keys" => array (
								array (
										"id varchar(32) not null primary key",
										"val text not null" 
								) 
						),
						'json_keys' => array (
								'val' 
						) 
				) 
		);
		$config ["table"] ["tables"] ['return'] = array (
				"class" => "\Common\Model\OrderReturnModel" 
		);
		$config ['ex'] = true;
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	/*
	 * $order_ex=array(array()) $order_ex=goods:array()
	 */
	public function createOrder($data, $user) {
		if (isset ( $_POST ['msg'] )) {
			$data ['msg'] = $_POST ['msg'];
		}
		$payType = $this->db->payType;
		if (! isset ( $data ['pay_type'] )) {
			$device = \koko::checkDevice ();
			if ($device == "pc") {
				$data ["pay_type"] = $payType ['alipay'] [0];
			} else if ($device == "m") {
				$data ["pay_type"] = $payType ['alipay'] [0];
			} else if ($device == "wx") {
				$data ["pay_type"] = $payType ['weixin'] [0];
			} else {
				$data ["pay_type"] = $payType ['yue'] [0];
			}
		}
		$data ["order_sn"] = \koko::createSn ();
		if ($data ["pay_type"] === $payType ['yue'] [0] && $user ['balance'] < $data ["sum"]) { // 余额支付
			\koko::jsonOut ( 0, "您的余额不足", true );
		}
		$data ["user_id"] = $user ['id'];
		$ret = $this->insert ( $data ); // 生成主订单
		return $data ["order_sn"];
	}
	public function setPayType($id, $pay_type) {
		$payType = $this->db->payType;
		if (isset ( $payType [$pay_type] )) {
			$v = $payType [$pay_type];
		} else {
			return false;
		}
		$this->where ( "id = $id" )->setField ( 'pay_type', $v );
		return $v;
	}
	public function paid($order_id, $pay_sn) {
		$ret = $this->where ( "id ='{$order_id}' and state=0" )->save ( array (
				"state" => $this->state ['dfh'] [0],
				"pay_time" => NOW_TIME,
				"pay_sn" => $pay_sn 
		) );
		if ($ret) {
			$o = $this->where ( "id ='{$order_id}'" )->find ();
			$this->tongjiToday ( 'paid' );
			$this->tongjiToday ( 'sum', $o ['sum'] - $o ['freight'], true );
		}
		return $ret;
	}
	public function show($id, $config = null) {
		$data = parent::show ( $id );
		if ($data) {
			$data ['ex'] = $this->getDbEx ( 'ex' )->where ( "order_id=" . $data ['id'] )->select ();
			$data ['waybill_data'] = json_decode ( $data ['waybill_data'], true );
			$this->addDataToArray ( $data ['ex'], 'goods', 'snapshot_id', $this->getDbEx ( 'snapshot' ), array (
					'callback' => function (&$data) {
						$data ['val'] = json_decode ( $data ['val'], true );
					} 
			) );
		}
		return $data;
	}
	public function getList($kokoWhere = NULL, $config = null) {
		$addGoods = isset ( $config ['addGoods'] ) ? $config ['addGoods'] : true;
		$data = parent::getList ( $kokoWhere, $config );
		if ($data ['list']) {
			$this->addDataToArray ( $data ['list'], 'ex', 'id', $this->getDbEx ( "ex" ), array (
					"id" => "order_id",
					"ex" => "list" 
			) );
		}
		return $data;
	}
	public function checkNewOrder() { // 检查订单是否被管理员查看过
		if (isset ( $_POST ['sn'] )) {
			$this->where ( "order_sn='{$_POST ['sn']}'" )->setField ( "checked", 1 );
		} else {
			$new = $this->where ( "checked=0" )->count ();
			$new_paid = $this->where ( "checked=0 and state=" . $this->state ['dfh'] [0] )->count ();
			echo json_encode ( array (
					"new" => $new,
					"new_paid" => $new_paid 
			) );
		}
	}
	public function checkOrderState($id = '') {
		$where = "state=" . $this->state ['dsh'] [0];
		if ($id != '') {
			$where .= ' and id=' . $id;
		} else {
			$where .= " and waybill_check_time < " . NOW_TIME;
		}
		$ret = $this->where ( $where )->field ( "id,fahuo_time,waybill,sum,user_id" )->select ();
		$auto_ids = array ();
		$days = \koko::setting ( 'order_auto_shouhuo_days/d', 14 );
		foreach ( $ret as $v ) {
			if ($days * 86400 + $v ['fahuo_time'] <= NOW_TIME) {
				$auto_ids [] = $v ['id'];
				$this->parent->addIncome ( $v ['sum'], $v ['user_id'] );
			}
			$json = file_get_contents ( "http://m.kuaidi100.com/autonumber/auto?num=" . $v ['waybill'] );
			$js = json_decode ( $json, true );
			$json = file_get_contents ( "http://m.kuaidi100.com/query?type={$js[0]['comCode']}&postid=" . $v ['waybill'] );
			$js = json_decode ( $json, true );
			$d = array (
					"waybill_data" => json_encode ( $js ['data'], JSON_UNESCAPED_UNICODE ) 
			);
			if ($js ['state'] == 3) {
				$d ["waybill_check_time"] = 1867222000;
			} else {
				$d ["waybill_check_time"] = NOW_TIME + 3600;
			}
			$this->where ( "id = " . $v ['id'] )->save ( $d );
		}
		if ($auto_ids) {
			$this->where ( "id in (" . join ( ",", $auto_ids ) . ")" )->save ( array (
					"shouhuo_time" => NOW_TIME,
					"state" => $this->state ['dpj'] [0] 
			) );
		}
		$days = \koko::setting ( 'order_auto_over_days/d', 3 );
		$ids = $this->where ( "state = {$this->state ['dpj'] [0] } and shouhuo_time < " . (NOW_TIME - $days * 86400) )->getField ( "id", true );
		$this->over ( join ( ",", $ids ) );
	}
	public function over($ids) {
		$this->where ( "id in (" . $ids . ")" )->setField ( "state", $this->state ['over'] [0] );
	}
	public function shouhuo($id, $uid = null) {
		$w = array (
				"id" => $id,
				"state" => $this->state ['dsh'] [0] 
		);
		if ($uid) {
			$w ['user_id'] = $uid;
		}
		$ret = $this->where ( $w )->save ( array (
				"shouhuo_time" => NOW_TIME,
				"state" => $this->state ['dpj'] [0] 
		) );
		if ($ret) {
			$o = $this->field ( 'sum,user_id' )->find ( $id );
			$this->parent->addIncome ( $o ['sum'], $o ['user_id'] );
		}
		\koko::jsonOut ( $ret !== false ? 1 : 0, '', 1 );
	}
}
class OrderReturnModel extends kokoModel {
	public $state = array (
			"dcl" => array (
					0,
					'待处理' 
			),
			"dfh" => array (
					1,
					'待发货' 
			),
			"dsh" => array (
					2,
					'待收货' 
			),
			"dtk" => array (
					3,
					'待退款' 
			),
			"over" => array (
					4,
					'已完成' 
			),
			"disagreed" => array (
					5,
					'已拒绝' 
			) 
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["name"] = "order_return";
		$config ["table"] ["keys"] [] = array (
				"order_id int not null",
				"money double default 0 not null comment '退款金额'",
				"state tinyint not null default 0",
				"waybill varchar(100) default ''",
				"request varchar(1000) default ''",
				"response varchar(1000) default ''",
				"create_time int",
				"handle_time int comment '处理时间'",
				"fahuo_time int comment '发货时间'",
				"over_time int comment '结束时间'",
				"@constraint `constraint_name` foreign key (order_id) references @parent_table(id) on delete cascade on update cascade" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
}