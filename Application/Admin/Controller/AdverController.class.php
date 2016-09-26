<?php

namespace Admin\Controller;

class AdverController extends TopController {
	public function _initialize() {
		if (($result=parent::_initialize ())!==false) {
			$this->db = new \Common\Model\AdverModel ();
		}
		return $result;
	}
	public function listing() {
		$w = array ();
		$this->assign ( "data", $this->db->getList ( $w, array (
				20,
				'id desc' 
		) ) );
		$this->db->getDbEx ( 'ex' );
		$this->option ( array (
				"switcher" => array (
						"keys" => "freeze" 
				),
				"del",
				"add" => array (
						"edit" => "*" 
				) 
		) );
		$this->display ();
	}
	public function ex() {
		$id = I ( 'get.id' );
		if ($id == '') {
			$this->error ( 'id 错误' );
		}
		$w = array (
				"adver_id" => $id 
		);
		$this->assign ( "data", $this->db->getDbEx ( 'ex' )->getList ( $w, 20 ) );
		$this->option ( array (
				"switcher" => array (
						"db" => "ex",
						"keys" => "freeze" 
				),
				"del,ex",
				"add" => array (
						"db" => "ex",
						"edit" => "*" 
				) 
		) );
		$this->display ();
	}
}