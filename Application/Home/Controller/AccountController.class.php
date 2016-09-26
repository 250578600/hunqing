<?php

namespace Home\Controller;

use Common\Controller\MemberController;

class AccountController extends MemberController {
	public function _initialize() {
		if (($result=parent::_initialize ())!==false) {
			$this->db = $this->user;
		}
		return $result;
	}
	public function register(){
		$loc=\koko::getObj('loc');
		$data=$loc->getBigArea();
		$area=$loc->get();
		foreach ($data as $k => &$v) {
		foreach ($v as $key => $value) {
			$v[$key]=array($value,$area['0,'.$key]);
			foreach ($v[$key][1] as $i => $j) {
				$v[$key][1][$i]=array($j,$area['0,'.$key.",".$i]);
			}
		}}
//		print_r($area);

		$step=I('get.step',1);	
		if($step==2 && !IS_AJAX){
			$this->assign("height",\koko::getNames ( $this->db->height ));
			$this->assign("xueli",\koko::getNames ( $this->db->xueli ));
			$this->assign("nianxin",\koko::getNames ( $this->db->nianxin ));
			$this->display('register_step2');
		}else{
			parent::register();//exit
		};
	}

	public function login(){
		$this->display();
	}

	public function bind_qq(){
		$this->display();
	}

	public function password_reset(){
		$this->display();
	}

}