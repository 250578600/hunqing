<?php

namespace Home\Controller;

use Common\Controller\KokoController;

class TopController extends KokoController {
	public function _initialize() {
		if (($result = parent::_initialize ()) !== false) {
			$this->user->loginWeixin ();
			if ($this->user->checkLogined ()) {
				$this->assign ( "user", $this->user->data_ );
			}
			$this->assign ( "device", \koko::checkDevice () );
		}
		return $result;
	}
	public function getWx() {
		if (\koko::checkDevice () == 'wx') {
			$wx = \koko::getObj ( 'wx' );
			if (IS_AJAX) {
				echo json_encode ( $wx->wxJsapiPackage ( $_SERVER ['HTTP_REFERER'] ) );
			} else {
				$this->assign ( 'wx', $wx->wxJsapiPackage () );
			}
		}
	}
	public function setCity($id) {
		$ret = \koko::getObj ( 'loc' )->setCity ( $id );
		if (IS_AJAX) {
			echo json_encode ( $ret );
		} else {
			return $ret;
		}
	}
	public function getLoction() {
		$loc = \koko::getObj ( 'loc' )->getCity ();
		if (IS_AJAX) {
			echo json_encode ( $loc );
		} else {
			$this->assign ( 'location', $loc );
		}
	}
}