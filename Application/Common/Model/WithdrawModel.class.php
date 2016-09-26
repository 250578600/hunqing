<?php

namespace Common\Model;

use Common\Model\KokoModel;

class WithdrawModel extends ApplicationModel {
	public $typeName = array (
			"支付宝",
			"微信",
			"现金",
			"银行" 
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int not null",
				"money double not null default 0",
				"bank_name varchar(30)",
				"bank_address varchar(30)",
				"account varchar(100) not null comment '提款帐号'",
				"account_type tinyint(2) not null default 0 comment '提款帐号类型:0支付宝，1微信，2现金，3银行'",
				"create_time int not null",
				"processing_time int not null default 0",
				"state tinyint(1) default 0 comment '0未处理，1已完成，2已拒绝'",
				"reason varchar(1024) default ''" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
	}
	public function insert(&$data) {
		$user = dbSaved ( 'user' );
		$data ['user_id'] = $user->logined_id;
		$u = $user->data;
		if (! isset ( $data ['money'] ) || ! is_numeric ( ($data ['money']) )) {
			\koko::jsonOut ( 0, "您输入的金额不正确", true );
		}
		if ($u ['balance'] < intval ( $data ['money'] )) {
			\koko::jsonOut ( 0, "您的余额不足", true );
		}
		if (isset ( $data ['account'] ) == false) {
			\koko::jsonOut ( 0, "请填写提现打款账户", true );
		}
		$ret = parent::insert ( $data );
		return $ret;
	}
	public function process($where, $state, $config = null) {
		$user = dbSaved ( 'user' );
		if ($this->getConfig ( "prompt", $config )) {
			$config ['return'] = true;
			$data = $this->where ( $where )->find ();
			$user_id = $data ['user_id'];
			if ($state == 'agree') {
				if ($data ['money'] == $this->getConfig ( "text", $config )) {
					$balance = $user->where ( "id = " . $user_id )->getField ( "balance" );
					if ($data ['money'] > $balance) {
						\koko::jsonOut ( 0, "该用户余额不足,操作失败", true );
					}
					$ret = parent::process ( $where, $state, $config );
					if ($ret !== false) {
						if ($user->data_ ['tixian_notify']) {
							$user->getDbEx ( 'notify' )->notify ( "您的申请提现{$data ['money']}元已同意", $user_id );
						}
						$user->where ( "id = " . $user_id )->save ( array (
								"withdraw" => array (
										"exp",
										"withdraw + " . $data ['money'] 
								),
								"balance" => array (
										"exp",
										"balance - " . $data ['money'] 
								) 
						) );
						$balance = $user->where ( "id=" . $user_id )->getField ( "balance" );
						$user->getDbEx ( "zhang" )->log ( $user_id, 2, - 1 * $data ['money'], "余额 " . ($balance) );
					}
				} else {
					\koko::jsonOut ( 0, "您输入的金额不正确", true );
				}
			} else {
				$ret = parent::process ( $where, $state, $config );
				if ($ret !== false) {
					if ($user->data_ ['tixian_notify']) {
						$user->getDbEx ( 'notify' )->notify ( "您的申请提现{$data ['money']}元已拒绝，原因是：" . $this->getConfig ( "text", $config ), $user_id );
					}
				}
			}
		}
	}
}