<?php

namespace Admin\Controller;

class ForumController extends TopController {
	public function _initialize() {
		if (($result = parent::_initialize ()) !== false) {
			$this->db = new \Common\Model\ForumModel();
		}
		return $result;
}
	public function listing(){
		$this->db->getDbEx('ex');
		$this->db->add(array(
			'post_id'=>'1',
			'at_id'=>'2',
			'reply_time'=>'2013.05.25',
			'msg'=>'福德宫')
		);
		$this->display ();
	}
  
}