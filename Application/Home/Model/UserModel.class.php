<?php

namespace Home\Model;

use \Common\Model\MemberModel;

class UserModel extends MemberModel {

	public $height = array (
			"h150-160" => array (
					1,
					"150-160cm" 
			),
			"h161-170" => array (
					2,
					"161-170cm" 
			),
			"h171-180" => array (
					3,
					"171-180cm" 
			),
			"h181-190" => array (
					4,
					"181-190cm" 
			),
			"h190-" => array (
					5,
					"191cm-" 
			)
	);
	public $xueli = array(
			's' => array (
					1,
					"小学"
			),
			'm' => array (
					2,
					"初中"
			),
			'm1' => array (
					3,
					"中专"
			),
			'm2' => array (
					4,
					"高中"
			),
			'l' => array (
					5,
					"大专"
			),
			'l1' => array (
					6,
					"本科"
			),
			'l2' => array (
					7,
					"硕士"
			),
			'l3' => array (
					8,
					"博士"
			)
	);
	public $nianxin = array (
			"nx5-10" => array (
					1,
					"5-10W" 
			),
			"nx11-20" => array (
					2,
					"11-20W" 
			),
			"nx21-30" => array (
					3,
					"21-30W" 
			),
			"nx31-40" => array (
					4,
					"31-40W" 
			),
			"nx41-50" => array (
					5,
					"41-50W" 
			),
			"nx50-100" => array (
					6,
					"50-100W" 
			),
			"nx100-" => array (
					7,
					"100W以上" 
			) 
	);
	public $after1 = array(
			1990,1991,1992,1993,1994,1995,1996,1997,1998,1999
	);
	public $after2 = array(
			1,2,3,4,5,6,7,8,9,10,11,12
	);
	public $after3 = array(
			1,2,3,4,5,6,7,8,9,10,11,12
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ['checkReg'] = array (
		//		"telephone_code" => true 
		);
		$config ['loginType'] = array (
				"id",
				"username",
				"telephone",
				"email" 
		);
		$config ['weixinLogin'] = true;
		$config ['simulateWeixin'] = false;
		$config ['weixinRegister'] = true;
		$config ['useWeixinDataOnReg'] = true;
		
		$config ['distribution'] = array (
				array (
						"balance" => 8,
						"point" => 9 
				) 
		);
		$config ["ex"]=true;
		$config ["table"] ["tables"] ['ex'] = array (
				"table" => array (
						"keys" => array (
								array (
										"height int default 0",
										"weight int default 0",
										"birthday varchar(20) ",
										"xueli tinyint ",
										"nianxin tinyint ",
										"interest varchar(1024) default ''" 
								)
								 
						),
						"transpose"=>true 
				) 
		);
		$config ["table"] ["tables"] ['withdraw'] = array (
				"class" => "\Common\Model\WithdrawModel" 
		);
		$config ["table"] ["tables"] ['checkin'] = array (
				"class" => "\Common\Model\CheckinModel" 
		);
		$config ["table"] ["tables"] ['notify'] = array (
				"class" => "\Common\Model\NotifyModel" 
		);
		$config ["table"] ["tables"] ['balance_log'] = array (
				"class" => "\Common\Model\LogModel",
				"tableName" => "balance_log" 
		);
		$config ["table"] ["tables"] ['cart'] = array (
				"class" => "\Common\Model\CartModel",
				"tableName" => "cart" 
		);
		$config ["table"] ["tables"] ['address'] = array (
				"class" => "\Common\Model\AddressModel",
				"tableName" => "address" 
		);
		$config ["table"] ["tables"] ['order'] = array (
				"class" => "\Home\Model\OrderModel",
				"tableName" => "Order" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function notifyShang($shang, $ji, $nickname) {
		$this->getDbEx ( 'notify' )->notify ( "您的团队中新增了一个{$ji}级成员：$nickname", $shang );
	}
	public function addIncome($money, $id) {
		if (! $id) {
			\koko::jsonOut ( 0, "id错误", true );
		}
		parent::addIncome ( $money, $id );
		$user = $this->find ( $id );
		if ($user ['groupid'] == 1) {
			$this->where ( "id = " . $user ['id'] )->setField ( "groupid", 2 );
		}
	}
}