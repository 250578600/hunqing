<?php

namespace Admin\Controller;

class PromotionController extends TopController {
	public function _initialize() {
		if (($result=parent::_initialize ())!==false) {
			$this->db = new \Home\Model\GoodsModel ();
		}
		return $result;
	}
	public function index() {
	}
	public function bdbxf() {
		if (IS_POST) {
			F ( "dbd_days", $_POST ['days'] );
			\koko::jsonOut ( 1, '', true );
		}
		$w = array (
				"equal" => array (
						"province" => $_GET ['province'],
						"city" => $_GET ['city'],
						"state" => $_GET ['state'],
						"id" => $_GET ['id'],
						"fenlei_id" => $_GET ['fenlei_id'],
						"bdb_switcher" => $_GET ['switcher'] 
				),
				"search" => array (
						"title" => $_GET ['keyword'] 
				) 
		);
		$data = $this->db->getList ( $w, 20 );
		$this->assign ( "data", $data );
		$this->assign ( "brand", $this->db->getTypeObj ( "brand" )->show () );
		$this->assign ( "days", F ( "dbd_days" ) );
		parent::listing ();
	}
	public function xsms() {
		$w = array (
				"equal" => array (
						"province" => $_GET ['province'],
						"city" => $_GET ['city'],
						"state" => $_GET ['state'],
						"id" => $_GET ['id'],
						"fenlei_id" => $_GET ['fenlei_id'],
						"daze_switcher" => $_GET ['switcher'] 
				),
				"search" => array (
						"title" => $_GET ['keyword'] 
				) 
		);
		$data = $this->db->getList ( $w, 20 );
		$this->assign ( "data", $data );
		$this->assign ( "brand", $this->db->getTypeObj ( "brand" )->show () );
		parent::listing ();
	}
	public function jing() {
		if (IS_POST) {
			F ( "zuidi", $_POST ['zuidi'] );
			\koko::jsonOut ( 1, '', true );
		}
		$w = array (
				"equal" => array (
						"province" => $_GET ['province'],
						"city" => $_GET ['city'],
						"state" => $_GET ['state'],
						"id" => $_GET ['id'],
						"fenlei_id" => $_GET ['fenlei_id'],
						"jing_switcher" => $_GET ['switcher'] 
				),
				"search" => array (
						"title" => $_GET ['keyword'] 
				) 
		);
		$data = $this->db->getList ( $w, 20 );
		$this->assign ( "data", $data );
		$this->assign ( "brand", $this->db->getTypeObj ( "brand" )->show () );
		$this->assign ( "zuidi", F ( "zuidi" ) );
		parent::listing ();
	}
}