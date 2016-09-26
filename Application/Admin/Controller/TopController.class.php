<?php

namespace Admin\Controller;

use Common\Controller\KokoController;

class TopController extends KokoController {
	public $ad = null;
	public function _initialize() {
		if (($result=parent::_initialize ())!==false) {
			$this->ad = new \Admin\Model\AdminModel ();
			if (! $this->ad->checkLogined ()) {
				session ( MODULE_NAME . '_login_ref', \koko::curPageURL () );
				header ( "location:" . U ( '/Admin/Account/login' ) );
				exit ();
			} else {
				$this->validate ();
			}
			if ($this->ad->data_ ['groupid'] && $this->ad->data_ ['freeze']) {
				$this->ad->logout ();
				exit ( "您的账号已经被冻结，请联系管理员" );
			}
			$this->assign ( "admin", $this->ad->data_ );
			$this->assign ( "ADMIN_RES_PATH", C ( 'ADMIN_RES_PATH' ) );
		}
		return $result;
	}
	public function comment() {
		$this->display ();
	}
	public function copy() {
		if (method_exists ( $this->db, 'copy' )) {
			$this->db->copy ();
		} else {
			$this->db->jsOut ( 0, '没有copy权限' );
		}
	}
	public function error($str) {
		exit ( $str );
	}
}