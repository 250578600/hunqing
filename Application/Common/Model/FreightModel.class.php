<?php

namespace Common\Model;

class FreightModel extends KokoModel {
	public $info = array (
			"pricing_method" => array (
					"件数计费",
					"重量计费",
					"体积计费" 
			) 
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"shop_id int not null default 0",
				"title varchar(30) not null",
				"name varchar(30) not null",
				"telephone varchar(20) not null",
				"province int not null",
				"city int not null",
				"county int not null",
				"address varchar(200) not null",
				"price double(9,2) not null",
				"freeze tinyint not null default 0",
				"info varchar(200)" 
		);
		$config ['ex'] = true;
		$config ["table"] ["tables"] ['ex'] = array (
				"table" => array (
						"name" => "freight_ex",
						"keys" => array (
								array (
										"freight_id int not null",
										"shipping_type varchar(100) not null default 0",
										"pricing_method tinyint not null default 0",
										"first_price double(9,2) not null",
										"first_num tinyint not null default 1 comment '首重/首件数量'",
										"continued_price double(9,2) not null comment '续重续件价格'",
										"baoyou_price double(9,2) default 0 comment '包邮价格'",
										"area mediumtext not null",
										"info varchar(200)",
										"@constraint `constraint_name` foreign key (freight_id) references @parent_table(id) on delete cascade on update cascade" 
								) 
						),
						"json_keys" => array (
								"area" 
						),
						'transpose_keys' => array (
								"area" 
						)
				) 
		);
		$config ['addition'] ['ex'] = 'freight_id';
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	/*
	 * $list=array(array('price'=>10,'num'=>3,'freight_id'=>1),array('price'=>10,'num'=>3,'freight_id'=>1))
	 */
	public function calc($list, $province) {
		$data = array ();
		foreach ( $list as $v ) {
			$data [$v ['freight_id']] [] = $v;
		}
		$out = array ();
		$total = 0;
		foreach ( $data as $k => $list ) {
			$num = 0;
			$goods_total = 0;
			foreach ( $list as $l ) {
				$num += $l ['num'];
				$goods_total += $l ['num'] * $l ['price'];
			}
			if ($num) {
				$freight = $this->show ( $k );
				$found = false;
				foreach ( $freight ['ex'] as $area ) {
					foreach ( $area ['area'] as $p ) {
						if ($p == $province) {
							$found = true;
							break;
						}
					}
					if ($found) {
						if ($goods_total >= $area ['baoyou_price']) {
							$out [] = array (
									'freight' => 0,
									'msg' => '运费模板包邮' 
							);
						} else {
							$price = $area ['first_price'];
							$num -= $area ['first_num'];
							if ($num > 0) {
								$price += $area ['continued_price'] * $num;
							}
							$out [] = array (
									'freight' => $price,
									'msg' => '正常计算邮费' 
							);
							$total += $price;
						}
						break;
					}
				}
				if ($found === false) {
					$total += $freight ['price'];
					$out [] = array (
							'freight' => $freight ['price'],
							'msg' => '邮费模板无匹配,使用默认邮费' 
					);
				}
			}
		}
		return array (
				"freight" => $total,
				"detail" => $out 
		);
	}
	public function getInfo($freight_id, $province) {
		$freight = $this->show ( $freight_id );
		foreach ( $freight ['ex'] as $area ) {
			foreach ( $area ['area'] as $p ) {
				if ($p == $province) {
					return $area ['info'];
				}
			}
		}
		return $freight ['info'];
	}
	public function getTemplates($shop_id = 0) {
		return $this->where ( "shop_id = $shop_id and freeze = 0" )->select ();
	}
}

