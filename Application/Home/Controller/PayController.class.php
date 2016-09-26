<?php

namespace Home\Controller;

use Common\Model\PaymentModel;

class PayController extends MustLoginController {
	public function _initialize() {
		if (($result=parent::_initialize ())!==false) {
			$this->db = new PaymentModel ();
		}
		return $result;
	}
	public function index() { // 统一生成支付
		if (isset ( $_POST ['sn'] )) {
			$sn = $_POST ['sn'];
		} elseif (isset ( $_GET ['sn'] )) {
			$sn = $_GET ['sn'];
		} else {
			\koko::jsonOut ( 0, "请传入订单号", false );
		}
		$order = $this->user->getDbEx ( 'order' );
		$ret = $order->show ( "order_sn='{$sn}'" );
		if ($ret ['state'] > 0) {
			if (IS_AJAX) {
				\koko::jsonOut ( 1, "", false, '/' );
			} else {
				\koko::header ( '/' );
			}
		}
		$order->startTrans ( 'paying' );
		if (isset ( $_POST ['pay_type'] )) {
			$type = $order->setPayType ( $ret ['id'], $_POST ['pay_type'] );
			if ($type !== false) {
				$ret ['pay_type'] = $type;
			}
		}
		$arg = array (
				'target' => $order,
				'order' => $ret 
		);
		$pay_sn = $this->db->insert ( $arg );
		if ($ret ['pay_type'] === 0) { // 余额支付
			if ($this->user->data_ ['balance'] < $ret ['total']) {
				$order->rollback ();
				\koko::jsonOut ( 0, "您的余额不足", true );
			} else {
				$order->commit ( 'paying' );
				\koko::jsonOut ( 1, "", true, U ( 'yue' ) . "?sn=" . $pay_sn );
			}
		} else { // /在线支付
			$body = array ();
			foreach ( $ret ['ex'] as $v ) {
				$body [] = $v ['title'];
			}
			$data = array (
					"openid" => $this->user->data_ ['openid'],
					"out_trade_no" => $pay_sn,
					"subject" => "在线支付",
					"total_fee" => $ret ['total'],
					"body" => join ( "<br>", $body ),
					"show_url" => "http://{$_SERVER['HTTP_HOST']}" . U ( 'Order/detail?sn=' . $sn ) 
			);
			session ( "jumpData", json_encode ( $data, JSON_UNESCAPED_UNICODE ) );
			session ( "pay_type", $ret ['pay_type'] );
			\koko::jsonOut ( 1, $pay_sn, false, U ( 'jump' ) );
		}
		$order->commit ( 'paying' );
	}
	public function yue() {
		if (isset ( $_GET ['sn'] )) {
			$sn = $_GET ['sn'];
		} else {
			\koko::jsonOut ( 0, "请用get传入支付号", false );
		}
		$data = $this->db->where ( "pay_sn = '$sn'" )->find ();
		if (IS_POST) {
			$pwd = I ( "post.pwd" );
			if ($this->user->checkPassword ( $pwd )) {
				$this->db->startTrans ();
				$r = $this->user->where ( "id=" . $this->user->logined_id . ' and balance >= ' . $data ['total'] )->setDec ( "balance", $data ['total'] );
				if ($r == false) {
					$this->db->rollback ();
					\koko::jsonOut ( 0, "扣费失败", true );
				} else {
					$r = $this->db->paid ( $sn );
					if ($r) {
						$this->user->getDbEx ( "zhang" )->log ( $this->user->logined_id, 1, - 1 * $data ['total'], "余额 " . ($this->user->data_ ['balance'] - $data ['total']) );
						$this->db->commit ();
						$order_sn = $this->user->getDbEx ( "order" )->where ( "id=" . $data ["order_id"] )->getField ( "order_sn" );
						\koko::jsonOut ( 1, "", false, U ( 'succ' ) . "?sn=" . $order_sn );
					}
				}
			} else {
				$this->db->rollback ();
				\koko::jsonOut ( 0, "密码错误", true );
			}
		} else {
			$data ['order'] = $this->user->getDbEx ( "order" )->show ( $data ['order_id'] );
			$this->assign ( "data", $data );
			$this->display ();
		}
	}
	public function jump() {
		$this->db->pay ();
	}
	public function alipay_notify() {
		if ($this->db->check_alipay_success ()) {
			$this->notify_do ();
		}
	}
	public function weixin_notify() {
		file_put_contents ( "weixin_notify.txt", json_encode ( $_GET ) );
		$this->db->weixin_notify ();
	}
	public function xingye_notify() {
		$this->db->xingye_notify ();
	}
	public function notify_do() { // http://localhost/index.php/Home/pay/notify_do?out_trade_no=20160224103212f5287711032
		$trade_no = $_GET ['out_trade_no'];
		$this->db->startTrans ();
		if ($this->db->paid ( $trade_no )) {
			$this->db->commit ();
			echo "result:success";
		} else {
			$this->db->rollback ();
			echo "result:error";
		}
	}
	public function success() {
		if ($this->db->check_alipay_success () || 1) {
		}
		$this->display ();
	}
	public function notify() {
		$_GET ['out_trade_no'] = $this->db->order ( "id desc" )->getField ( 'pay_sn' );
		$this->notify_do ();
		// echo file_get_contents("http://test4.fengniaozhiku.com/index.php/Home/pay/notify_do.html?out_trade_no=20160224151239a93ea365429");
	}
}
