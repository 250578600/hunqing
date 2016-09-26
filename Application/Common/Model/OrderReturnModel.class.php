<?php

namespace Common\Model;

use Common\Model\KokoModel;

class OrderReturnModel extends kokoModel {
	public $stateName = array (
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
			"refused" => array (
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
	public function agree($id,$msg) {
		$data = $this->find ( $id );
		if ($data ['state'] == $this->stateName ['dcl'] [0]) {
			$order=$this->parent->find($data['order_id']);
			if($order['state']==){
				
			}
		} else {
			$name = \koko::getNames ( $this->stateName );
			\koko::jsonOut ( 0, '该申请正在' . $name [$data ['state']], true );
		}
		$this->parent->find ();
	}
}