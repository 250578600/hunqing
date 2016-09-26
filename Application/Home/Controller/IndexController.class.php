<?php

namespace Home\Controller;

class IndexController extends TopController {
	public function _initialize() {
		if (($result = parent::_initialize ()) !== false) {
			$this->db = $this->goods;
		}
		return $result;
	}
	public function index() {
		print_r(session());
		$this->user->getDbEx ( 'cart' );
		$this->user->getDbEx ( 'address' );
		$this->getLoction ();
		$adver = new \Common\Model\AdverModel ();
		$this->assign ( "banner", $adver->show ( 111 ) );
		$order = array (
				"id desc",
				"price",
				"price desc",
				"sales",
				"sales desc" 
		);
		$o = I ( "get.order" );
		if (! $o) {
			$o = 0;
		}
		$data = $this->goods->getList ( "is_on = 1", array (
				"order" => $order [$o] 
		) );
		$this->assign ( 'data', $data ['list'] );
		$this->assign ( 'o', $o );
		$this->getWx ();
		$this->display ();
	}
	public function fenlei() {
		$this->assign ( 'data', $this->goods->getDbEx('fenlei')->show() );
		$this->display ();
	}
	public function listing() {
		$w = array (
				'fenlei_id' => 'fid' 
		);
		$this->assign ( 'data', $this->goods->getList ( $w, 20 ) );
		$this->display ();
	}
	public function detail() {
		$id = I ( 'get.id/d' );
		if (! $id) {
			exit ( "id error" );
		}
		$data = $this->goods->show ( $id, array (
				"click" => true 
		) );
		// $a = current ( $data ['ex'] );
		// $data ['price'] = $a;
		$this->assign ( "data", $data );
		$judge = $this->goods->getDbEx ( "judge" )->getList ( "freeze = 0 and object_id = " . $id, array (
				20,
				"id desc" 
		) );
		$this->db->addDataToArray ( $judge ['list'], 'user', 'user_id', $this->user );
		$this->assign ( "judge", $judge );
		$d = array (
				"user_id" => $this->user->id,
				"object_id" => $id 
		);
		$option = array (
				"add" => array (
						"db" => '@user.cart',
						"requir" => "goods_id,num" 
				),
				"collect" => array (
						"db" => "collection",
						"action" => "add" 
				) 
		);
		$col = $this->db->getDbEx ( 'collection' )->where ( $d )->find ();
		$this->assign ( 'collected', $col ? 1 : 0 );
		if ($col) {
			$option ['collect'] ['action'] = 'del';
			$option ['collect'] ['id'] = $col ['id'];
		} else {
			$option ['collect'] ['extend'] = $d;
		}
		$this->option ( $option );
		$this->display ();
	}
}


