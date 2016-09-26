<?php

namespace Admin\Controller;

use Think\Controller;

class OrderController extends TopController {
	public function _initialize() {
		if (($result = parent::_initialize ()) !== false) {
			$this->db = $this->user->getDbEx ( 'order' );
		}
		return $result;
	}
	public function index() {
		$w = array (
				"order_sn" => "keyword",
				"_date" => array (
						"key" => "create_time",
						"from" => "from",
						"to" => "to" 
				),
				"state" 
		);
		$data = $this->db->getList ( $w, 20 );
		$this->ad->addDataToArray ( $data ['list'], 'snapshot', 'snapshot_id', $this->db->getDbEx ( 'snapshot' ), array (
				'recursion' => array (
						'key' => 'ex',
						'config' => array (
								'ex_config' => array (
										'json_keys' => true 
								) 
						) 
				) 
		) );
		$this->assign ( 'data', $data );
		$db = $this->db;
		$info = array (
				'payType' => \koko::getNames ( $this->db->payType ),
				'stateName' => \koko::getNames ( $this->db->state ),
				'state' => $this->db->state 
		);
		if (! isset ( $_GET ['excel_export'] )) {
			$this->assign ( $info );
			$this->option ( array (
					'@waybill' => array (
							'call' => 'addWaybill',
							'argc' => array (
									'sn',
									'waybill' 
							) 
					) 
			) );
			$this->display ();
		} else {
			$title = array (
					"订单编号",
					"用户id",
					"支付方式",
					"运费",
					"支付金额",
					"订单状态",
					"生成时间",
					"付款时间",
					"留言",
					"订单详情" 
			);
			$array = array ();
			foreach ( $data ['list'] as $v ) {
				$d = array (
						$v ['order_sn'],
						$v ['user_id'],
						$info ['payType'] [$v ['pay_type']],
						$v ['freight'],
						$v ['total'],
						$v ['stateName'] [$v ['state']],
						date ( 'Y-m-d H:i:s', $r ['create_time'] ),
						date ( 'Y-m-d H:i:s', $r ['end_time'] ),
						$v ['msg'] 
				);
				$og = array (
						array (
								'标题',
								'货号',
								'数量',
								'价格' 
						) 
				);
				foreach ( $v ['ex'] as $v2 ) {
					$og [] = array (
							$v2 ['title'],
							$v2 ['snapshot'] ['goods_sn'],
							$v2 ['num'],
							$v2 ['price'] 
					);
				}
				$d [] = $og;
				$array [] = $d;
			}
			$obj = \koko::getObj ( 'excel' );
			$obj->export ( '', $title, $array );
		}
	}
	public function detail() {
		$sn = I ( "get.sn" );
		$data = $this->db->show ( "order_sn = '$sn'" );
		$stateName = \koko::getNames ( $this->db->state );
		$data ['stateName'] = $stateName [$data ['state']];
		$this->assign ( "data", $data );
		$this->option ( array (
				'del' => $data ['id'] 
		) );
		$this->display ();
	}
	public function tuihuo() {
		$w = array (
				"_date" => array (
						"key" => "create_time",
						"from" => "from",
						"to" => "to" 
				),
				"state" 
		);
		$db = $this->db->getDbEx ( 'return' );
		$data = $db->getList ( $w, 20 );
		$this->ad->addDataToArray ( $data ['list'], 'order', 'order_id', $this->db );
		$this->assign ( 'data', $data );
		$this->assign ( 'state', \koko::getNames ( $db->state ) );
		$this->option ( array (
				'@agree' => array (
						'argc' => array (
								'id' => 'id',
								'msg' => 'msg',
								'type' => 'type' 
						) 
				) 
		) );
		$this->display ();
	}
	protected function addWaybill($argc) {
		$sn = $argc ['sn'];
		$waybill = $argc ['waybill'];
		$this->db->startTrans ();
		$ret = $this->db->where ( "order_sn='{$sn}'" )->save ( array (
				"state" => $this->db->state ['dsh'] [0],
				"waybill" => $waybill,
				"fahuo_time" => NOW_TIME 
		) );
		if ($ret !== false && $this->addWaybill_after ( $sn )) {
			$this->db->commit ();
			\koko::jsonOut ();
		} else {
			$this->db->rollback ();
			\koko::jsonOut ( 0, "添加运单号失败" );
		}
	}
	public function checkNewOrder() {
		$this->db->checkNewOrder ();
	}
	public function shouhuo() {
		$sn = I ( "post.sn" );
		$ret = $this->db->update ( "order_sn='{$sn}'", array (
				"state" => 8 
		) );
		if ($ret) {
			$d = $this->db->show ( "order_sn='{$sn}'" );
			$r = dbSaved ( 'user' )->where ( "id=" . $d ['user_id'] )->save ( array (
					"balance" => array (
							"exp",
							"balance + " . ($d ['sum'] - $d ['freight']) 
					) 
			) );
			$this->db->getDbEx ( 'sales' )->log ( $d ['user_id'], $d ['city_id'], $d ['id'], $d ['freight'] - $d ['sum'], '退货' );
		}
		\koko::jsonOut ( $ret != false ? 1 : 0 );
	}
	protected function agree($argc) {
		$id = $argc ['id'];
		$msg = $argc ['msg'];
		$type = $argc ['type'];
		$db = $this->db->getDbEx ( 'return' );
		if ($type == 'agree') {
			$data = $db->find ( $id );
			if (! $data) {
				\koko::jsonOut ( 0, "该申请id不存在", true );
			}
			if ($data ['state'] == $db->state ['dcl'] [0]) {
				$order = $this->db->find ( $data ['order_id'] );
				$save = array (
						"state" => $this->state ['dfh'] [0],
						"handle_time" => NOW_TIME,
						"response" => $msg 
				);
				if ($order ['state'] == $this->db->state ['dfh'] [0]) { // 如果商家未发货,则直接跳入待退款操作
					$save ['state'] = $db->state ['dtk'] [0];
				}
				$ret = $db->where ( array (
						"id" => $id 
				) )->save ( $save );
				if ($ret) {
					$this->db->where ( "id=" . $order ['id'] )->save ( array (
							'state' => $this->db->state ['closed'] [0] 
					) );
					// / 这里是程序自动退款
				}
				\koko::jsonOut ( $ret !== false ? 1 : 0, '', true );
			} else {
				$name = \koko::getNames ( $this->state );
				\koko::jsonOut ( 0, '该申请正在' . $name [$data ['state']], true );
			}
		} else {
			$ret = $db->where ( array (
					"id" => $id 
			) )->save ( array (
					"state" => $db->state ['disagreed'] [0],
					"handle_time" => NOW_TIME,
					"response" => $msg 
			) );
			\koko::jsonOut ( $ret !== false ? 1 : 0, $ret, true );
		}
	}
}