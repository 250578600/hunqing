<?php

namespace Home\Controller;

class MustLoginController extends TopController {
	public function _initialize() {
		if (($result=parent::_initialize ())!==false) {
			if ($this->user->checkLogined () == false) {
				session ( MODULE_NAME . '_login_ref', \koko::curPageURL () );
				$url = U ( '/Home/Account/login' );
				if (IS_AJAX) {
					\koko::jsonOut ( 0, '您需要登录', true, $url );
				} else {
					\koko::header ( $url );
				}
			} else {
				$this->validate ();
			}
			if ($this->user->data_ ['groupid'] && $this->user->data_ ['freeze']) {
				$this->user->logout ();
				exit ( "您的账号已经被冻结，请联系管理员" );
			}
		}
		return $result;
	}
}