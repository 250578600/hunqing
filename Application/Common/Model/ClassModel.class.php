<?php

namespace Common\Model;

class ClassModel extends TreeModel {
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"attr_ids varchar(500) default ''",
				"spec_ids varchar(500) default ''",
				"inherit tinyint default 1 comment '是否继承父级的参数规格'" 
		);
		$config ["table"] ["tables"] ['spec'] = array (
				"class" => "\Common\Model\AttributeModel",
				"tableName" => 'class_spec',
				"config" => array (
						"unset_type" => "checkbox,radio" 
				) 
		);
		$config ["table"] ["tables"] ['attr'] = array (
				"class" => "\Common\Model\AttributeModel",
				"tableName" => 'class_attr' 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function dispose(&$data, $d) {
		foreach ( $data as &$v ) {
			$v ['attr'] = $v ['attr_ids'] ? $this->getDbEx ( "attr" )->where ( "id in (" . $v ['attr_ids'] . ")" )->select () : array ();
			$v ['spec'] = $v ['spec_ids'] ? $this->getDbEx ( "spec" )->where ( "id in (" . $v ['spec_ids'] . ")" )->select () : array ();
			$v ['attr'] = \koko::turnIdToKey ( $v ['attr'] );
			$v ['spec'] = \koko::turnIdToKey ( $v ['spec'] );
			if ($v ['inherit']) {
				isset ( $d ['attr'] ) and $v ['attr'] = array_merge ( $d ['attr'], $v ['attr'] );
				isset ( $d ['spec'] ) and $v ['spec'] = array_merge ( $d ['spec'], $v ['spec'] );
			}
			if ($v ['list']) {
				$this->dispose ( $v ['list'], $v );
			}
		}
	}
	public function show($id = 0, $config = null) {
		$data = parent::show ( $id, $config );
		if ($this->checkConfig ( 'dispose', $config )) {
			$this->dispose ( $data, array () );
		}
		return $data;
	}
}