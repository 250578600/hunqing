<?php

namespace Admin\Controller;

class GoodsController extends TopController {
	public function _initialize() {
		if (($result = parent::_initialize ()) !== false) {
			$this->db = $this->goods;
		}
		return $result;
	}
	public function index() {
	}
	public function depot() {
		$w = array (
				"is_on" => 0,
				"state",
				"fenlei_id",
				'_complex' => array (
						"id" => 'keyword',
						"title" => array (
								'like',
								array (
										'keyword',
										3 
								) 
						),
						'_logic' => 'or' 
				) 
		);
		$data = $this->db->getList ( $w, 20 );
		foreach ( $data ['list'] as &$v ) {
			$v ['judge'] = $this->db->getDbEx ( 'judge' )->where ( "object_id = " . $v ['id'] )->count ();
		}
		$this->option ( array (
				"switcher" => array (
						"keys" => "is_on" 
				),
				"del" 
		) );
		$this->assign ( "data", $data );
		$this->assign ( "fenlei", $this->db->getDbEx ( "fenlei" )->show () );
		$this->display ();
	}
	public function listing() {
		$w = array (
				"is_on" => 1,
				"state",
				"fenlei_id",
				'_complex' => array (
						"id" => 'keyword',
						"title" => array (
								'like',
								array (
										'keyword',
										3 
								) 
						),
						'_logic' => 'or' 
				)
		);
		$data = $this->db->getList ( $w, 20 );
//		 echo $this->db->_sql ();
		foreach ( $data ['list'] as &$v ) {
			$v ['judge'] = $this->db->getDbEx ( 'judge' )->where ( "object_id = " . $v ['id'] )->count ();
		}
		$this->option ( array (
				"switcher" => array (
						"keys" => "is_on" 
				) 
		) );
		$this->assign ( "data", $data );
		
		$this->assign ( "fenlei", $this->db->getDbEx ( "fenlei" )->show () );
		$this->display ();
	}
	public function add() {
		$id = I ( "get.id/d" );
		if (is_numeric ( $id )) {
			$data = $this->db->show ( $id );
			$this->assign ( "data", $data );
		}
		$this->assign ( "fenlei", $this->db->getDbEx ( "fenlei" )->show ( 0, array (
				'dispose' => true 
		) ) );
		$this->assign ( "freightTemplates", $this->db->getDbEx ( "freight" )->getTemplates () );
		$this->option ( array (
				"add" => array (
						"edit" => $id,
						"extend" => array (
								"owner_id" => $this->ad->logined_id 
						),
						"function" => array (
								"json_encode" => array (
										"keys" => "imgs,attr",
										"argc" => array (
												"" => "",
												JSON_UNESCAPED_UNICODE 
										) 
								) 
						) 
				) 
		) );
		$this->display ();
	}
	public function fenlei() {
		$db = $this->db->getDbEx ( "fenlei" );
		$this->assign ( "data", $db->show ( 0, array (
				'showAll' => 1 
		) ) );
		$this->option ( array (
				"del,fenlei",
				"add" => array (
						"db" => "fenlei",
						"edit" => "*" 
				),
				"switcher" => array (
						"db" => "fenlei",
						"keys" => "freeze,inherit" 
				) 
		) );
		$this->assign ( "attr", $db->getDbEx ( 'attr' )->select () );
		$this->assign ( "spec", $db->getDbEx ( 'spec' )->select () );
		$this->display ();
	}
	public function attr() {
		$db = $this->db->getDbEx ( "fenlei" )->getDbEx ( "attr" );
		$this->assign ( "data", $db->select () );
		$this->assign ( "info", $db->info );
		$this->option ( array (
				"del,fenlei.attr",
				"add" => array (
						"db" => "fenlei.attr",
						"edit" => "*" 
				) 
		) );
		$this->assign ( "pageName", "参数" );
		$this->display ();
	}
	public function spec() {
		$db = $this->db->getDbEx ( "fenlei" )->getDbEx ( "spec" );
		$this->assign ( "data", $db->select () );
		$this->assign ( "info", $db->info );
		$this->option ( array (
				"del,fenlei.spec",
				"add" => array (
						"db" => "fenlei.spec",
						"edit" => "*" 
				) 
		) );
		$this->assign ( "pageName", "规格" );
		$this->display ( "attr" );
	}
	public function freight() {
		$db = $this->db->getDbEx ( "freight" );
		/*
		 * $list = array ( array ( 'price' => 10, 'num' => 1, 'freight_id' => 1 ), array ( 'price' => 10, 'num' => 1, 'freight_id' => 1 ) ); $db->calc ( $list, 1 );
		 */
		$this->assign ( "data", $db->getList ( '', array (
				20,
				"ex" => true 
		) ) );
		$this->option ( array (
				"del,freight",
				"add" => array (
						"db" => "freight",
						"edit" => "*" 
				),
				"switcher" => array (
						"db" => "freight",
						"keys" => "freeze" 
				) 
		) );
		$this->assign ( "info", $db->info );
		$this->assign ( "area", \koko::splitArray ( \KokoTools\location::getBigArea (), 3 ) );
		$this->display ();
	}
	public function judge() {
		$w = array (
				"object_id" => 'gid',
				"msg" => array (
						'like',
						array (
								'keyword',
								3 
						) 
				) 
		);
		$data = $this->db->getDbEx ( 'judge' )->getList ( $w, 20 );
		$this->db->addDataToArray ( $data ['list'], 'user', 'user_id', $this->user );
		$this->db->addDataToArray ( $data ['list'], 'goods', 'object_id', $this->goods );
		$this->assign ( "data", $data );
		$this->option ( array (
				'del,judge',
				'switcher,judge' => array (
						'keys' => 'freeze' 
				) 
		) );
		$this->display ();
	}
}