<?php

namespace Admin\Controller;

class WechatController extends TopController {
	public function _initialize() {
		if (($result=parent::_initialize ())!==false) {
			$this->db = new \Common\Model\WxMenuModel ();
		}
		return $result;
	}
	public function menu() {
		if (IS_POST) {
			$ret = $this->db->init ( $_POST ['data'] );
			\koko::jsonOut ( $ret ['errmsg'] == 'ok' ? 1 : 0, $ret ['errmsg'] );
		} else {
			$this->assign ( "data", $this->db->get () );
			$this->display ();
		}
	}
	public function listing_sucai() {
		$w = array (
				"search" => array (
						"title" => $_GET ['keyword'] 
				) 
		);
		$data = $this->db->getDbEx ( 'sucai' )->getList ( $w, array (
				"20",
				"id desc" 
		) );
		$this->assign ( "data", $data );
		parent::listing ();
	}
	public function add_sucai() {
		parent::addInsert ( $this->db->getDbEx ( 'sucai' ) );
		parent::addShow ( $this->db->getDbEx ( 'sucai' ) );
	}
}