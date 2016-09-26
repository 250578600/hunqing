<?php

namespace Admin\Controller;

use Common\Controller\MemberController;

class AccountController extends MemberController {
	public function _initialize() {
		if (($result=parent::_initialize ())!==false) {
			$this->db = new \Admin\Model\AdminModel ();
			$this->assign ( "ADMIN_RES_PATH", C ( 'ADMIN_RES_PATH' ) );
		}
		return $result;
	}
}