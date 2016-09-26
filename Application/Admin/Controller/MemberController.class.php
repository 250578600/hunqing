<?php

namespace Admin\Controller;

class MemberController extends TopController {
	public function _initialize() {
		if (($result = parent::_initialize ()) !== false) {
			$this->db = $this->user;
		}
		return $result;
	}
	public function listing() {
		$w = array (
				"state",
				"id",
				"_complex" => array (
						"_logic" => "or",
						"telephone" => array (
								"like",
								array (
										"keyword",
										3 
								) 
						),
						"nickname" => array (
								"like",
								array (
										"keyword",
										3 
								) 
						),
						"email" => array (
								"like",
								array (
										"keyword",
										3 
								) 
						) 
				),
				"_date" => array (
						"key" => "create_time",
						"from" => "from",
						"to" => "to" 
				),
				"_complex_" => array (
						array () 
				) 
		);
		$data = $this->db->getList ( $w, array (
				20,
				"id desc" 
		) );
		// echo $this->db->getLastSql();
		$this->ad->addDataToArray ( $data ['list'], 'shang', 'shang0', $this->user );
		$this->assign ( "data", $data );
		$this->assign ( "group", $this->db->getGroupName (true) );
		$this->assign ( "info", $this->db->info );
		$this->option ( array (
				"switcher" => array (
						"keys" => "freeze" 
				),
				"del"
		) );
		$this->display ();
	}
	public function group() {
		$db = $this->db->getDbEx ( 'group' );
		$data = $db->getList ( '', array (
				20,
				"power" => true 
		) );
		$data ['list'] = \koko::turnIdToKey ( $data ['list'] );
		$this->assign ( "data", $data );
		$this->assign ( "power", $this->ad->getDbEx ( 'power' )->where ( "target = 'Home'" )->getField ( "id,name,description" ) );
		$this->option ( array (
				"del,group",
				"add,group" => array (
						"db" => "group",
						"edit" => "*" ,
						"action"=>'add'
				),
				"switcher" => array (
						"db" => "group",
						"keys" => 'default' 
				) 
		) );
		$this->display ();
	}
	public function detail() {
		$id = I ( "get.id/d", null );
		$data = $this->db->find ( $id );
		$this->assign ( "data", $data );
		$this->display ();
	}
	public function password() {
		$id = I ( "get.id/d", null );
		$this->option ( array (
				"pwd" => array (
						"call" => "setPassword",
						"argc" => array (
								"pwd",
								"@" . $id 
						) 
				) 
		) );
		$this->assign ( "data", $this->db->show ( $id ) );
		$this->display ();
	}
	public function add() {
		$id = I ( "get.id/d", null );
		if ($id) {
			$data = $this->db->find ( $id );
		}
		$option = array (
				"add" => array (
						"keys" => "username,nickname,name,telephone,password,group,sex,email,province,city,county,address,balance,freeze,groupid",
						"require" => "nickname,telephone,groupid",
						"no_edit" => "username" ,
						"function" => array (
								"this.pwdJiami" => "password" 
						) ,
				) 
		);
		if ($id) {
			$this->assign ( "data", $data );
			$option ['add'] ['edit'] = $id;
			$option ['add'] ['ignore'] = "password";
		}
		$this->option ( $option );
		$this->assign ( "group", $this->db->getGroupName () );
		$this->display ();
	}
	public function listing_money() {
		$data = $this->db->getDbEx ( 'balance_log' )->getList ( $w, array (
				20,
				"id desc" 
		) );
		$this->ad->addDataToArray ( $data ['list'], 'user', 'user_id', $this->user );
		$this->assign ( "data", $data );
		$this->display ();
	}
	public function listing_tixian() {
		$w = array (
				"equal" => array (
						"state" => $_GET ['state'],
						"id" => $_GET ['id'] 
				),
				"search" => array (
						"name" => $_GET ['keyword'] 
				) 
		);
		$tx = $this->db->getDbEx ( "tixian" );
		$data = $tx->getList ( $w, array (
				20,
				"id desc" 
		) );
		$this->db->addDataToArray ( $data ['list'], 'user', 'user_id', $this->db );
		$this->db->addDataToArray ( $data ['list'], 'admin', 'admin_id', $this->ad );
		$this->assign ( "data", $data );
		$this->assign ( "sex", $this->db->info ['sex'] );
		$this->assign ( "state", $tx->info ['stateName'] );
		$this->assign ( "typeName", $tx->typeName );
		$this->display ();
	}
	public function xia() {
		$id = I ( "get.id/d" );
		$g = I ( "get.g" );
		$w = array (
				"equal" => array (
						"shang0" => $id,
						"groupid" => $g 
				) 
		);
		$data = $this->db->getList ( $w, array (
				20,
				"id desc" 
		) );
		$o = new \Home\Model\OrderModel ();
		$w = array (
				"date" => array (
						"key" => "create_time",
						"from" => $_GET ['from'],
						"to" => $_GET ['to'] 
				) 
		);
		$w = $this->user->createWhere ( $w );
		if (! $w) {
			$w = 1;
		}
		foreach ( $data ['list'] as &$v ) {
			$v ['xiao2'] = $o->where ( "$w and groupid =2 and state = 9 and user_id = " . $v ['id'] )->sum ( "sum - freight" );
			$v ['xiao3'] = $o->where ( "$w and groupid =3 and state = 9 and user_id = " . $v ['id'] )->sum ( "sum - freight" );
			$ids = $this->db->where ( "shang0=" . $v ['id'] )->getField ( "id", true );
			if ($ids) {
				$v ['xiao'] = $o->where ( "$w and groupid =2 and state = 9 and user_id in (" . join ( ",", $ids ) . ")" )->sum ( "sum - freight" );
			} else {
				$v ['xiao'] = 0;
			}
		}
		$this->assign ( "sex", $this->db->info ['sex'] );
		$this->assign ( "shenfen", $this->db->shenfen );
		$this->assign ( "data", $data );
		$this->assign ( "xiao", $this->db->find ( $id ) );
		$this->display ();
	}
	public function coupon() {
		$w = array ();
		$this->assign ( "data", $this->db->getDbEx ( 'coupon' )->getList ( $w, 20 ) );
		$this->option ( array (
				"del,coupon",
				"add,coupon" => array (
						"edit" => "*" 
				),
				"switcher,coupon" => "freeze" 
		) );
		$this->display ();
	}
	public function ajax() {
		if (isset ( $_POST ['goods'] )) {
			$db = $this->goods;
		}
		switch ($_POST ['action']) {
			case 'setShang' :
				$ret = $this->db->setShang ( $_POST ['id'], 0 );
				\koko::jsonOut ( $ret !== false ? 1 : 0, '', true );
			default :
				parent::ajax ();
		}
		if (isset ( $ret ) && $ret === false) {
			\koko::jsonOut ( 0 );
		}
	}
}