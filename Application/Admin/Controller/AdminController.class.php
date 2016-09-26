<?php

namespace Admin\Controller;

class AdminController extends TopController {
	public function _initialize() {
		if (($result = parent::_initialize ()) !== false) {
			$this->db = $this->ad;
		}
		return $result;
	}
	public function listing() {
		$w = array (
				"groupid" => array (
						"neq",
						0 
				),
				"username" => array (
						"like",
						array (
								"keyword",
								3 
						) 
				),
				"_date" => array (
						"key" => "create_time",
						"from" => "from",
						"to" => "to" 
				) 
		);
		$data = $this->db->getList ( $w, array (
				20,
				"id desc" 
		) );
		$this->assign ( "data", $data );
		$this->assign ( "info", $this->db->info );
		$this->assign ( "group", $this->db->getGroupName () );
		$this->option ( array (
				"switcher" => array (
						"keys" => "freeze" 
				),
				"del" 
		) );
		$this->display ();
	}
	public function add() {
		$id = I ( "get.id/d", null );
		if ($id) {
			$data = $this->db->find ( $id );
		}
		$option = array (
				"add" => array (
						"keys" => "password,username,telephone,sex,email,groupid,info",
						"require" => "username,telephone,password",
						"function" => array (
								"this.pwdJiami" => "password" 
						)
				) 
		);
		if ($id) {
			$this->assign ( "data", $data );
			$option ['add'] ['edit'] = $id;
			$option ['add'] ['ignore'] = "password";
		}
		$this->option ( $option );
		$this->assign ( "info", $this->db->info );
		$this->assign ( "group", $this->db->getGroupName () );
		$this->display ();
	}
	public function power() {
		$db = $this->ad->getDbEx ( 'power' );
		$data = $db->getList ( '', 20 );
		$data ['list'] = \koko::turnIdToKey ( $data ['list'] );
		$this->assign ( "data", $data );
		$this->assign ( "modules", $this->getAllMethod () );
		$this->assign ( "info", C ( 'POWER_STRUCT' ) );
		$this->option ( array (
				"del,power",
				"add" => array (
						"db" => "power",
						"function" => array (
								"json_encode" => "power" 
						),
						"edit" => "*" 
				) 
		) );
		$this->display ();
	}
	public function group() {
		$db = $this->ad->getDbEx ( 'group' );
		$data = $db->getList ( '', array (
				20,
				"power" => true 
		) );
		$data ['list'] = \koko::turnIdToKey ( $data ['list'] );
		$this->assign ( "data", $data );
		$this->assign ( "power", $this->ad->getDbEx ( 'power' )->where ( "target = 'Admin'" )->getField ( "id,name,description" ) );
		$this->option ( array (
				"del,group",
				"add" => array (
						"db" => "group",
						"edit" => "*" 
				),
				"switcher" => array (
						"db" => "group",
						"keys" => 'default' 
				) 
		) );
		$this->display ();
	}
	public function getAllMethod() {
		// return unserialize ( S ( 'out' ) );
		$space = explode ( ",", C ( 'SCAN_DIRS' ) );
		$out = array ();
		$modelAction = array (
				"insert",
				"update",
				"del",
				"switcher",
				"call" 
		);
		$GLOBALS ['no_database'] = true;
		$layers = array (
				"Controller",
				"Model" 
		);
		foreach ( $space as $module ) {
			$koko = "koko";
			foreach ( $layers as $type ) {
				$dir = APP_PATH . $module . '/' . $type . '/';
				if (is_dir ( $dir )) {
					if ($handle = opendir ( $dir )) {
						while ( ($file = readdir ( $handle )) !== false ) {
							if ($file != "." && $file != ".." && substr ( $file, - 10 ) == '.class.php') {
								$path = $dir . $file;
								if (is_file ( $path )) {
									$class = str_replace ( "/", "\\", substr ( $path, 13, strlen ( $path ) - 23 ) );
									if (class_exists ( $class )) {
										file_put_contents ( "123vv.txt", $class . "\r\n", FILE_APPEND );
										$obj = new $class ();
										if (is_subclass_of ( $obj, "Think\Model" ) == false && is_subclass_of ( $obj, "Think\Controller" ) == false) {
											continue;
										}
										if ($type == 'Model') {
											$out [$module] [$type] [$obj->config ["table"] ["name"]] = $modelAction;
										} else {
											$ret = $this->analyseClass ( $obj, $koko );
											if ($ret) {
												$out [$module] [$type] [substr ( $class, 1 )] = $ret;
											}
										}
										if ($type == 'Model' && strpos ( $class, "Common" ) == false) {
											foreach ( $obj->config ["table"] ['tables'] as $k => $v ) {
												$o = $obj->getDbEx ( $k );
												$out [$module] [$type] [$o->config ["table"] ["name"]] = $modelAction;
											}
										}
									}
								}
							}
						}
						closedir ( $handle );
					}
				}
			}
		}
		unset ( $GLOBALS ['no_database'] );
		// print_r ( $out );
		S ( 'out', serialize ( $out ) );
		return $out;
	}
	private function analyseClass($obj, &$koko) {
		$method = get_class_methods ( $obj );
		$parentClass = get_parent_class ( $obj );
		if ($parentClass) {
			if (strpos ( strtolower ( $parentClass ), "koko" ) === false) {
				$ret = $this->analyseClass ( $parentClass, $koko );
				$a = array ();
				foreach ( $ret as $v ) {
					$a [$v] = 1;
				}
				foreach ( $method as $v ) {
					$a [$v] = 1;
				}
				$method = array_keys ( $a );
				sort ( $method );
			} else {
				if ($koko == 'koko') {
					$koko = get_class_methods ( new $parentClass () );
				}
			}
		}
		if ($koko != 'koko') {
			$method = array_diff ( $method, $koko );
		}
		return $method;
	}
}