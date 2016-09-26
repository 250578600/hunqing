<?php

namespace Admin\Controller;

class IndexController extends TopController {
	public function _initialize() {
		if (($result=parent::_initialize ())!==false) {
			$this->db = $this->ad;
		}
		return $result;
	}
	public function index() {
		$c = $this->ad->getDbEx ( 'menu' );
		$this->assign ( "list", $c->getMenu () );
		// $this->assign ( "list", $c->show ( 1 ) );
		$this->display ();
	}
	public function webset() {
		$db = D ( "Setting" );
		if (IS_POST) {
			foreach ( $_POST ['data'] as $k => $v ) {
				$db->set ( $k, $v );
			}
			\koko::jsonOut ();
			return;
		} else {
			$d = $db->getField ( "name,value" );
			foreach ( $d as &$v ) {
				$v = unserialize ( $v );
			}
			$this->assign ( "data", $d );
			$this->display ();
		}
	}
	public function menu() {
		$this->assign ( 'data', $this->ad->getDbEx ( 'menu' )->show ( 0, array (
				'showAll' => 1 
		) ) );
		$this->option ( array (
				"del,menu",
				"add" => array (
						"db" => "menu",
						"edit" => "*" 
				),
				"switcher" => array (
						"db" => "menu",
						"keys" => "freeze" 
				) 
		) );
		$this->display ();
	}
	public function fenxiao() {
		if (IS_POST) {
			F ( 'distribution', $_POST ['distribution'] );
			\koko::jsonOut ();
			return;
		} else {
			$this->assign ( "distribution", F ( 'distribution' ) );
			$this->display ();
		}
	}
	public function setting() {
		$this->assign ( 'data', D ( 'Setting' )->get () );
		$this->option ( array (
				"save" => array (
						"db" => "&Setting",
						"call" => "set",
						"argc" => array (
								"d" 
						) 
				),
				"del" 
		) );
		$this->display ();
	}
	public function profile() {
		if ($_POST) {
			if ($this->ad->checkPassword ( $_POST ['password'] )) {
				unset ( $_POST ['password'] );
				$ret = $this->ad->update ( "id=" . $this->ad->logined_id, $_POST );
				if ($ret === false) {
					\koko::jsonOut ( 0, "修改失败" );
				} else {
					\koko::jsonOut ();
				}
			} else {
				\koko::jsonOut ( 0, "密码错误" );
			}
			exit ();
		}
		$this->display ();
	}
	public function message() {
		$w = array (
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
		$this->assign ( "data", $data );
		$this->option ( array (
				'del,judge',
				'switcher,judge' => array (
						'keys' => 'freeze'
				)
		) );
		$this->display();
	}
}