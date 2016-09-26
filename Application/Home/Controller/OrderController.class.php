<?php

namespace Home\Controller;

class OrderController extends MustLoginController {
	public function _initialize() {
		if (($result = parent::_initialize ()) !== false) {
			$this->db = $this->user->getDbEx ( 'order' );
			$this->assign ( "state", $this->db->infoName ['stateName'] );
		}
		return $result;
	}
	public function create() {
		$order_data = array ();
		$snapshot = array ();
		// ////////////////////////// 生成订单商品数据
		if (isset ( $_POST ['buy'] )) {
			$num = I ( 'post.num/d', 1 );
			$id = explode ( ",", $_POST ['buy'] );
			if (isset ( $id [1] )) {
				$ex_id = $id [1];
				$id = $id [0];
			} else {
				$ex_id = 0;
			}
			$d = $this->goods->show ( $id );
			if ($d ['ex'] && $ex_id == 0) {
				\koko::jsonOut ( 0, '需要输入规格id', true );
			}
			if ($ex_id) { // 商品有规格
				$ex_d = $d ['ex'] [$ex_id];
				$o_ex = array (
						'goods_id' => $id,
						'goods_ex_id' => $ex_id,
						'title' => $d ['title'] . "(" . join ( ",", $ex_d ['specs'] ) . ")",
						'thumb' => isset ( $ex_d ['thumb'] ) && $ex_d ['thumb'] ? $ex_d ['thumb'] : $d ['thumb'],
						'price' => $ex_d ['price'] 
				);
			} else {
				$o_ex = array (
						'goods_id' => $id,
						'goods_ex_id' => 0,
						'title' => $d ['title'],
						'thumb' => $d ['thumb'],
						'price' => $d ['price'] 
				);
			}
			$snapshot_ = array (
					'id' => null,
					'val' => json_encode ( $d, JSON_UNESCAPED_UNICODE ) 
			);
			$o_ex ['freight_id'] = $d ['freight_id'];
			$o_ex ['num'] = $num;
			$o_ex ['snapshot_id'] = $snapshot_ ['id'] = md5 ( $snapshot_ ['val'] );
			$snapshot [] = $snapshot_;
			$order_data ['ex'] = array (
					$o_ex 
			);
			$order_data ['sum'] = $o_ex ['price'] * $o_ex ['num'];
		} else {
			$order_data ['sum'] = 0;
			$d = $this->user->getDbEx ( 'cart' )->getData ( array (
					'user_id' => $this->user->logined_id,
					'state' => 1 
			), $this->goods );
			foreach ( $d as $v ) {
				$ex_id = $v ['goods_ex_id'];
				if ($ex_id) { // 商品有规格
					$ex_d = $v ['goods'] ['ex'] [$ex_id];
					$o_ex = array (
							'goods_ex_id' => $ex_id,
							'title' => $v ['goods'] ['title'] . "(" . join ( ",", $ex_d ['specs'] ) . ")",
							'thumb' => isset ( $ex_d ['thumb'] ) && $ex_d ['thumb'] ? $ex_d ['thumb'] : $v ['goods'] ['thumb'],
							'price' => $ex_d ['price'] 
					);
				} else {
					$o_ex = array (
							'goods_ex_id' => 0,
							'title' => $v ['goods'] ['title'],
							'thumb' => $v ['goods'] ['thumb'],
							'price' => $v ['goods'] ['price'] 
					);
				}
				$o_ex ['freight_id'] = $v ['goods'] ['freight_id'];
				$o_ex ['num'] = $v ['num'];
				$o_ex ['goods_id'] = $v ['goods_id'];
				$snapshot_ = array (
						'id' => null,
						'val' => json_encode ( $v ['goods'], JSON_UNESCAPED_UNICODE ) 
				);
				$o_ex ['snapshot_id'] = $snapshot_ ['id'] = md5 ( $snapshot_ ['val'] );
				$snapshot [] = $snapshot_;
				$order_data ['ex'] [] = $o_ex;
				$order_data ['sum'] += $o_ex ['price'] * $o_ex ['num'];
			}
		}
		// /////////////////////////////////// 支付方式
		if (isset ( $_POST ['pay_type'] )) {
			$db = $this->db;
			$type = $this->db->payType [$_POST ['pay_type']];
			if ($type !== false) {
				$order_data ['pay_type'] = $type;
			}
		}
		// /////////////////////////////////// 优惠活动
		
		// /////////////////////////////////// 添加运费
		if ($this->db->checkConfig ( "delivery" )) { // //////////////// 添加地址信息
			$ad = $this->user->getDbEx ( 'address' );
			$yf = $this->goods->getDbEx ( 'freight' );
			$addr = $ad->getAddress ( $this->user->logined_id );
			$freight = $yf->calc ( $order_data ['ex'], $addr ['province'] );
			$order_data ['name'] = $addr ['name'];
			$order_data ['telephone'] = $addr ['telephone'];
			$order_data ['province'] = $addr ['province_'];
			$order_data ['city'] = $addr ['city_'];
			$order_data ['county'] = $addr ['county_'];
			$order_data ['address'] = $addr ['address'];
			$order_data ["freight_info"] = json ( $freight );
			$order_data ["freight"] = $freight ['freight'];
			$order_data ["total"] = $order_data ["sum"] + $freight ['freight'];
		}
		// print_r($order_data);
		// print_r($snapshot);
		// //////////////////////////////////生成订单
		$sn = $this->db->createOrder ( $order_data, $this->user->data_ );
		// //////////////////// 修改库存
		foreach ( $order_data ['ex'] as $v ) {
			$this->goods->alterStock ( $v ['goods_id'], $v ['goods_ex_id'], - 1 * $v ['num'] );
		}
		// // 保存快照
		$db = $this->db->getDbEx ( 'snapshot' );
		$rs = array ();
		foreach ( $snapshot as $v ) {
			if ($db->where ( "id='{$v['id']}'" )->getField ( "id" ) == null) {
				$r = $db->add ( $v );
				if ($r === false) {
					$this->db->del ( "order_sn = '$sn'" );
					$db->delete ( join ( ",", $rs ) );
				} else {
					$rs [] = $r;
				}
			}
		}
		// ////////////////////////////// 支付
		\koko::jsonOut ( 1, '订单生成成功', true, U ( '/Home/Pay/' ) . '?sn=' . $sn );
	}
	public function index() {
		$w = array (
				"user_id" => $this->user->logined_id,
				"_complex_" => array (
						array (
								"state" => array (
										'neq',
										$this->db->state ['closed'] [0] 
								) 
						) 
				),
				"del" => 0 
		);
		$data = $this->db->getList ( $w, array (
				20,
				"id desc" 
		) );
		$stateName = \koko::getNames ( $this->db->state );
		foreach ( $data ['list'] as &$v ) {
			$v ['stateName'] = $stateName [$v ['state']];
			switch ($v ['state']) {
				case $this->db->state ['dfk'] [0] :
					$v ['pay_url'] = U ( 'Pay/pay' ) . '?sn=' . $v ['order_sn'];
			}
		}
		$this->assign ( "data", $data );
		$this->display ();
	}
	public function detail() {
		$sn = I ( "get.sn" );
		$data = $this->db->show ( "del = 0 and order_sn = '$sn' and user_id = " . $this->user->logined_id );
		if (! $data) {
			exit ( "您所查询的订单已被删除" );
		}
		$stateName = \koko::getNames ( $this->db->state );
		$data ['stateName'] = $stateName [$data ['state']];
		$payType = \koko::getNames ( $this->db->payType );
		$data ['payType'] = $payType [$data ['pay_type']];
		if ($data ['return_id']) {
			$data ['return'] = $this->db->getDbEx ( 'return' )->find ( $data ['return_id'] );
		}
		$option = array ();
		switch ($data ['state']) {
			case $this->db->state ['dfk'] [0] :
				$data ['pay_url'] = U ( 'Pay/pay' ) . '?sn=' . $data ['order_sn'];
				$option ['@del'] = array (
						'argc' => array (
								'id' => '@' . $data ['id'] 
						) 
				);
				break;
			case $this->db->state ['dsh'] [0] :
				$option ['shouhuo'] = array (
						'call' => 'shouhuo',
						'argc' => array (
								'id' => $data ['id'],
								'uid' => $this->user->logined_id 
						) 
				);
			case $this->db->state ['dfh'] [0] :
				$db = $this->db->getDbEx ( 'return' );
				if ($db->where ( 'order_id = ' . $data ['id'] . " and state !=" . $db->state ['disagreed'] [0] )->field ( 'id' )->count () == 0) {
					$option ['add'] = array ( // / 申请退货
							"db" => "return",
							"require" => "request,money",
							"extend" => array (
									"order_id" => $data ['id'] 
							) 
					);
				}
				break;
			case $this->db->state ['dpj'] [0] :
				$this->assign ( 'judge', 1 );
				$option ['@judge'] = array (
						'call' => 'judge',
						'argc' => array (
								'order_id' => $data ['id'],
								'msgs' 
						) 
				);
		}
		$this->assign ( "data", $data );
		$this->option ( $option );
		$this->display ();
	}
	public function del($argc) {
		$id = $argc ['id'];
		$data = $this->db->show ( "id='$id' and del = 0" );
		if ($data) {
			switch ($data ['state']) {
				case $this->db->state ['dfk'] [0] :
					foreach ( $data ['ex'] as $v ) {
						$this->goods->alterStock ( $v ['goods_id'], $v ['goods_ex_id'], $v ['num'] );
					}
					break;
				case $this->db->state ['over'] [0] :
				case $this->db->state ['closed'] [0] :
					break;
				default :
					\koko::jsonOut ( 0, '订单暂时不能删除', true );
			}
			$ret = $this->db->where ( "id=" . $data ['id'] )->setField ( "del", 1 );
			\koko::jsonOut ( $ret !== false ? 1 : 0, '', true );
		} else {
			\koko::jsonOut ( 0, '', true );
		}
	}
	public function judge($argc) {
		$order_id = $argc ['order_id'];
		$msgs = $argc ['msgs'];
		$db = $this->db->getDbEx ( 'ex' );
		$oex = $db->where ( "judged=0 and order_id=" . $order_id )->getField ( "id,goods_id" );
		$data = array ();
		$ids = array ();
		if (empty ( $msgs )) {
			\koko::jsonOut ( 0, '', true );
		}
		// $msgs = \koko::arrayTranspose ( $msgs );
		foreach ( $msgs as $k => $v ) {
			if (is_numeric ( $k ) && isset ( $oex [$k] )) {
				$data [] = array (
						"user_id" => $this->user->logined_id,
						"object_id" => $oex [$k],
						"create_time" => NOW_TIME,
						"msg" => $v ['msg'],
						"stars" => $v ['stars'] 
				);
				$ids [] = $k;
			}
		}
		$ret = $this->goods->getDbEx ( "judge" )->addAll ( $data );
		if ($ret !== false) {
			$db->where ( "id in (" . join ( ",", $ids ) . ")" )->setField ( "judged", 1 );
			if ($db->where ( "judged=1 and order_id=" . $order_id )->count () == count ( $oex )) {
				$this->db->over ( $order_id );
			}
			\koko::jsonOut ( 1, '', 1 );
		} else {
			\koko::jsonOut ( 0, '', true );
		}
	}
	public function judge_() {
		$sn = I ( "get.sn" );
		$data = $this->db->show ( "order_sn = '$sn' and user_id = " . $this->user->logined_id );
		$this->assign ( 'data', $data );
		$this->display ();
	}
	public function shouhuo() {
		$sn = I ( 'post.order_sn' );
		$id = $this->db->where ( "order_sn = '$sn' and user_id=" . $this->user->logined_id )->getField ( "id" );
		if ($id) {
			$this->db->shouhuo ( $id );
		} else {
			\koko::jsonOut ( 0, '订单编号错误' );
		}
	}
	public function yanchang() {
		$sn = I ( 'post.order_sn' );
		$ret = $this->db->where ( "order_sn = '$sn' and user_id=" . $this->user->logined_id )->setInc ( "shouhuo_days", 7 );
		\koko::jsonOut ( $ret !== false ? 1 : 0, '', 1 );
	}
	public function yundan() {
		$sn = I ( "post.sn" );
		$waybill = I ( "post.waybill" );
		$ret = $this->db->update ( "order_sn='{$sn}'", array (
				"state" => 7,
				"tuihuo" => json ( array (
						"waybill" => $waybill 
				) ) 
		) );
		\koko::jsonOut ( $ret != false ? 1 : 0 );
	}
}