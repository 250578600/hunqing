<?php

namespace Home\Model;

use Common\Model\GoodsPModel;

class GoodsModel extends GoodsPModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		if (isset ( $config ["table"] ["name"] ) == false) {
			$config ["table"] ["name"] = "goods";
		}
		$config ["table"] ["keys"] [] = array (
				"tag varchar(10) default ''",
				"video varchar(256) default ''" 
		);
		$config ['fenlei'] = true;
		$config ['ex'] = true;
		$config ['history'] = false;
		$config ["table"] ["tables"] ['collection'] = array (
				"class" => "\Common\Model\CollectionModel" 
		);
		$config ["table"] ["tables"] ['history'] = array (
				"class" => "\Common\Model\CollectionModel"
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function show($id, $config = null) {
		$data = parent::show ( $id, $config );
		if ($data) {
			$data ['ex'] = \koko::turnIdToKey ( $data ['ex'], "id" );
		}
		return $data;
	}
	public function getList($kokoWhere, $config = null) {
		$data = parent::getList ( $kokoWhere, $config );
		return $data;
	}
}













