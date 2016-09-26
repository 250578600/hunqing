<?php

namespace Home\Controller;

class CenterController extends MustLoginController {
	public function _initialize() {
		if (($result = parent::_initialize ()) !== false) {
			$this->db = $this->user;
		}
		return $result;
	}
	public function qrcode() {
		$file = $this->db->getMyQR ();
		$this->assign ( 'qrcode', '/' . $file );
		$this->display ();
	}
	public function index() {
		$this->display ();
	}
	public function collection() {
		$data = $this->goods->getDbEx ( 'collection' )->getList ( "user_id = " . $this->user->logined_id, 20 );
		$this->assign ( 'data', $data );
		$this->display ();
	}
	public function address() {
		$data = $this->user->getDbEx ( 'address' )->getAddresses ( $this->user->logined_id );
		$this->assign ( 'data', $data );
		$this->option ( array (
				"del,address",
				"switcher,address" => array (
						"keys" => "default" 
				) 
		) );
		$this->display ();
	}
	public function addr() {
		$id = I ( 'get.id/d' );
		if ($id) {
			$data = $this->user->getDbEx ( 'address' )->where ( array (
					"user_id" => $this->user->logined_id,
					'id' => $id 
			) )->find ();
			if (! $data) {
				exit ( "没有找到数据" );
			}
			$this->assign ( 'data', $data );
		}
		$this->option ( array (
				'add' => array (
						'db' => 'address',
						"edit" => $id,
						"extend" => array (
								"user_id" => $this->user->logined_id 
						) 
				) 
		) );
		$this->display ();
	}
	public function cart() {
		$this->assign ( "data", $this->db->getDbEx ( 'cart' )->getData ( array (
				'user_id' => $this->user->logined_id 
		), $this->goods ) );
		$this->option ( array (
				"del,cart",
				"add" => array (
						"db" => 'cart',
						"requir" => "goods_id,num" 
				),
				"switcher,cart" => array (
						"keys" => "state" 
				) 
		) );
		$this->display ();
	}
	public function confirm() {
		$addr = $this->db->getDbEx ( 'address' );
		$a = $addr->getAddress ( $this->user->logined_id, I ( "get.addr_id/d" ) );
		$this->assign ( "addr", $a );
		if (isset ( $_GET ['buy'] ) && preg_match ( "/^\d(,\d)*$/", $_GET ['buy'] )) {
			$id = explode ( ",", $_GET ['buy'] );
			if (isset ( $id [1] )) {
				$ex_id = $id [1];
				$id = $id [0];
			}
			$d = $this->goods->show ( $id );
			$data = array (
					'goods_id' => $id,
					'num' => I ( 'get.num/d', 1 ) 
			);
			if (isset ( $ex_id )) {
				$data ['goods_ex_id'] = $ex_id;
			}
			$data = array (
					$data 
			);
		} else {
			$cart = $this->db->getDbEx ( 'cart' );
			$data = $cart->getData ( array (
					'user_id' => $this->user->logined_id,
					'state' => 1 
			), $this->goods );
		}
		
		$this->assign ( 'data', $data );
		if ($a) {
			$yf = $this->goods->getDbEx ( 'freight' );
			$goods = array ();
			foreach ( $data as $v ) {
				$goods [] = array (
						'price' => $v ['goods_ex_id'] == 0 ? $v ['goods'] ['price'] : $v ['goods'] ['ex'] [$v ['goods_ex_id']] ['price'],
						'num' => $v ['num'],
						'freight_id' => $v ['goods'] ['freight_id'] 
				);
			}
			$this->assign ( "freight", $yf->calc ( $goods, $a ['province'] ) );
		} else {
			$this->assign ( "freight", 0 );
		}
		$this->display ();
	}
	public function withdraw() {
		$u = $this->user->data;
		if (! $u ['password']) {
			$this->user->header ( U ( 'password' ) );
		} elseif (! $u ['bank_name']) {
			$this->user->header ( U ( 'bank' ) );
		} else {
			$this->display ();
		}
	}
}