<?php

namespace Home\Model;

use Common\Model\OrderPModel;

class OrderModel extends OrderPModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"groupid int default 0" 
		);
		$config ['delivery'] = true;
		$this->goods = new GoodsModel ();
		$config ["table"] ["tables"] ['sales'] = array (
				"class" => "\Common\Model\SalesLogModel" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function paid($order_id, $pay_sn) {
		$ret = parent::paid ( $order_id, $pay_sn );
		return $ret;
	}
}













