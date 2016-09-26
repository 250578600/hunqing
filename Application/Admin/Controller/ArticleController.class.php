<?php

namespace Admin\Controller;

class ArticleController extends TopController {
	public function _initialize() {
		if (($result = parent::_initialize ()) !== false) {
			$this->db = new \Common\Model\ArticleModel ();
		}
		return $result;
	}
	public function listing() {
		$w = array (
				"category_id",
				"title" => array (
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
		$this->option ( array (
				"add" => array (
						"edit" => "*",
						"keys" => "paixu" 
				),
				"switcher" => array (
						"keys" => "freeze" 
				),
				"del" 
		) );
		$this->assign ( "category", $this->db->getDbEx ( 'category' )->show () );
		$this->display ();
	}
	public function add() {
		$id = I ( "get.id/d", null );
		if ($id) {
			$data = $this->db->find ( $id );
		}
		$option = array (
				"add" => array (
						"require" => "title,content,category_id" 
				) 
		);
		if ($id) {
			$this->assign ( "data", $data );
			$option ['add'] ['edit'] = $id;
		}
		$this->option ( $option );
		$this->assign ( "info", $this->db->info );
		$this->assign ( "category", $this->db->getDbEx ( 'category' )->show () );
		$this->display ();
	}
	public function category() {
		$db = $this->db->getDbEx ( "category" );
		$data = array ();
		$db->turnToLine ( $db->show ( 0, array (
				'showAll' => 1 
		) ), $data );
		$this->assign ( "data", $data );
		$this->option ( array (
				"del,category",
				"add" => array (
						"db" => "category",
						"edit" => "*" 
				),
				"switcher" => array (
						"db" => "category",
						"keys" => "freeze" 
				) 
		) );
		$this->display ();
	}
	public function comment() {
		$db = $this->db->getDbEx ( "comment" );
		$w = array (
				"title" => array (
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
		$data = $db->getList ( $w, 20 );
		$this->db->addDataToArray ( $data ['list'], 'article_title', 'id', $this->db, array (
				"ex_config" => array (
						"fields" => "id,title" 
				),
				"id" => "article_id" 
		) );
		$this->assign ( "data", $data );
		$this->option ( array (
				"del,comment" 
		) );
		$this->display ();
	}
}