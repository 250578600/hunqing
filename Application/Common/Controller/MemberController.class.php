<?php

namespace Common\Controller;

class MemberController extends KokoController {
	public function logout() {
		$this->db->logout ();
		header ( 'location: ' . (MODULE_NAME == 'Admin' ? U ( '/Admin' ) : '/') );
	}
	public function login() {
		if ($_POST) {
			$this->db->login ( $_POST );
			if ($this->db->data_ ['freeze']) {
				$this->db->logout ();
				\koko::jsonOut ( 0, "您的账号已经被冻结，请联系管理员", true );
			}
			$ref_name = MODULE_NAME . '_login_ref';
			$ref = session ( $ref_name );
			session ( $ref_name, null );
			\koko::jsonOut ( 1, '登录成功', true, $ref );
			exit ();
		}
		if ($this->db->checkLogined ()) {
			header ( 'location: ' . (MODULE_NAME == 'Admin' ? U ( '/Admin' ) : '/') );
			exit ();
		}
		if (\koko::checkDevice () == 'wx') {
			$this->display ( "login.m" );
		} else {
			$this->display ();
		}
	}
	public function register() {
		if ($_POST) {
			$this->db->register ( $_POST );
			exit ();
		}
		$this->display ();
	}
	public function password() {
		if ($_POST) {
			if ($this->db->checkLogined () == false) {
				\koko::jsonOut ( 0, "未登录", true );
			}
			if ($this->db->checkPassword ( $_POST ['oldpwd'] )) {
				if ($this->db->checkPassword ( $_POST ['password'] ) || $this->db->setPassword ( $_POST ['password'] )) {
					\koko::jsonOut ();
				}
				\koko::jsonOut ( 0, "密码修改失败", true );
			} else {
				\koko::jsonOut ( 0, "旧密码不正确", true );
			}
			exit ();
		}
		$this->display ();
	}
	public function forget() {
		if ($_POST) {
			$user = $this->db->where ( "email='{$_POST['email']}'" )->find ();
			if ($user == null) {
				\koko::jsonOut ( 0, "您输入的邮箱不存在", true );
			} else {
				$pwd = rand ( 100000, 999999 );
				$this->db->setPassword ( $pwd, $user ['id'] );
				$msg = "您的新密码是：$pwd" . ($user ['username'] ? ",登录账号:" . $user ['username'] : '');
				$ret = $this->db->sendEmail ( $_POST ['email'], $msg );
				if ($ret == false) {
					\koko::jsonOut ( 0, "邮件发送失败", true );
				} else {
					\koko::jsonOut ( 1, "邮件发送成功", true );
				}
			}
			exit ();
		}
		$this->display ();
	}
	public function sendTelephoneCode() {
		\koko::sendTelephoneCode ( $_POST ['telephone'] );
		\koko::jsonOut ();
	}
	public function code() {
		\koko::getVerification ();
	}
}