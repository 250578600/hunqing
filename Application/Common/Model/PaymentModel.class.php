<?php

namespace Common\Model;

use Common\Model\KokoModel;

class PaymentModel extends KokoModel implements inter\OrderInterface {
	public $payType = array (
			"yue" => array (
					0,
					"余额支付"
			),
			"alipay" => array (
					1,
					"支付宝"
			),
			"alipaywap" => array (
					2,
					"支付宝移动"
			),
			"weixin" => array (
					3,
					"微信公众支付"
			),
			"weixinQR" => array (
					4,
					"微信二维码支付"
			),
			"xingye" => array (
					5,
					"兴业微信支付"
			),
			"xingye_bank" => array (
					6,
					"兴业银行支付"
			)
	);
	public $url = null;
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		if (isset ( $config ["table"] ["name"] ) == false) {
			$config ["table"] ["name"] = "payment";
		}
		$config ["table"] ["keys"] [] = array (
				"id int not null primary key auto_increment",
				"user_id int default 0",
				"pay_sn varchar(30) not null",
				"order_class_name varchar(30) not null",
				"order_id varchar(30) not null",
				"state tinyint(2) not null default 0 comment '0:未成功，1:已成功'",
				"create_time int not null default 0",
				"finish_time int not null default 0" 
		);
		parent::__construct ( $name, $tablePrefix, $connection, $config );
		$this->url = "http://" . $_SERVER ["SERVER_NAME"] . ($_SERVER ["SERVER_PORT"] == 80 ? "" : ":" . $_SERVER ["SERVER_PORT"]);
	}
	public function insert(&$data) {
		$target = $data ['target'];
		$order = $data ['order'];
		if ($target->parent == null) {
			$d = array (
					"order_class_name" => "\\\\" . str_replace ( "\\", "\\\\", get_class ( $target ) ) 
			);
		} else {
			$d = array (
					"order_class_name" => "\\\\" . str_replace ( "\\", "\\\\", get_class ( $target->parent ) ) . "." . $target->config ['table_key'] 
			);
		}
		$d ["user_id"] = $order ['user_id'];
		$d ["order_id"] = $order ['id'];
		$d ["money"] = $order ['total'];
		$d ["pay_sn"] = \koko::createSn ();
		parent::insert ( $d );
		return $d ["pay_sn"];
	}
	public function pay() {
		$type = session ( 'pay_type' );
		$jumpData = session ( 'jumpData' );
		// session ( 'pay_type', null );
		// session ( 'jumpData', null );
		if (! $jumpData) {
			exit ( "支付数据错误" );
		}
		$payType = $this->db->payType;
		$jumpData = json_decode ( $jumpData, true );
		if ($type == $payType ['alipay'] [0])
			$this->alipay ( $jumpData );
		else if ($type == $payType ['alipaywap'] [0])
			$this->alipay ( $jumpData, true );
		else if ($type == $payType ['weixin'] [0])
			$this->weixin ( $jumpData );
		else if ($type == $payType ['xingye'] [0])
			$this->xingye ( $jumpData );
		else if ($type == $payType ['xingye_bank'] [0])
			$this->xingye_bank ( $jumpData );
	}
	public function paid($payment_sn) {
		$bill = $this->where ( "pay_sn='{$payment_sn}'" )->find ();
		if ($bill ['state'] == 1) {
			return true;
		}
		$ret = $this->where ( "id = " . $bill ['id'] )->save ( array (
				"state" => 1,
				"finish_time" => NOW_TIME 
		) );
		$str = "";
		if ($ret) {
			$str .= "payment success<br/>";
			$payment = $this->where ( "pay_sn='{$payment_sn}'" )->find ();
			$cls = str_replace ( '\\\\', '\\', $payment ['order_class_name'] );
			$class = explode ( ".", $cls );
			if (class_exists ( $class [0] )) {
				$str .= "find class $cls <br/>";
				$obj = new $class [0] ();
				if (isset ( $class [1] )) {
					$obj = $obj->getDbEx ( $class [1] );
				}
				$ret = $obj->paid ( $payment ["order_id"], $payment_sn );
			} else {
				$str .= "class " . $cls . "不存在 ";
				$ret = false;
			}
		} else {
			$str .= "payment error<br/>";
			$ret = false;
		}
		echo $str;
		return $ret;
	}
	public function alipay($data, $isPhone = false) { // http://127.0.0.16/index.php/Home/Pay/jump.html
		if ($isPhone) {
			require ("alipaywap/alipay.config.php");
			require ("alipaywap/lib/alipay_submit.class.php");
		} else {
			require ("alipay/alipay.config.php");
			require ("alipay/lib/alipay_submit.class.php");
		}
		$alipay_data = $data;
		// print_r($alipay_data);exit;
		// 支付类型
		$payment_type = "1";
		// 商户订单号
		$out_trade_no = $alipay_data ['out_trade_no']; // 商户网站订单系统中唯一订单号，必填
		$subject = $alipay_data ['subject']; // 订单名称// 必填
		$total_fee = $alipay_data ['total_fee']; // 付款金额// 必填
		$body = $alipay_data ['body']; // 订单描述
		$show_url = $alipay_data ['show_url']; // 商品展示地址
		                                       
		// $out_trade_no='azsdfs57'.rand(10000, 9999999);
		                                       // $body=$subject="haha";
		                                       // $total_fee=9;
		                                       // 需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html
		                                       // 防钓鱼时间戳
		$anti_phishing_key = "";
		// 若要使用请调用类文件submit中的query_timestamp函数
		
		// 客户端的IP地址
		$exter_invoke_ip = "";
		// 非局域网的外网IP地址，如：221.0.0.1
		// ///////////////////////////////////////////////
		// /手机支付的
		// 超时时间
		$it_b_pay = $_POST ['WIDit_b_pay'];
		// 选填
		
		// 钱包token
		$extern_token = $_POST ['WIDextern_token'];
		// 选填
		/**
		 * *********************************************************
		 */
		
		// 构造要请求的参数数组，无需改动
		if ($isPhone) {
			$parameter = array (
					"service" => "alipay.wap.create.direct.pay.by.user",
					"partner" => trim ( $alipay_config ['partner'] ),
					"seller_id" => trim ( $alipay_config ['seller_id'] ),
					"payment_type" => $payment_type,
					"notify_url" => $alipay_config [notify_url],
					"return_url" => $alipay_config [return_url],
					"out_trade_no" => $out_trade_no,
					"subject" => $subject,
					"total_fee" => $total_fee,
					"show_url" => $show_url,
					"body" => $body,
					"it_b_pay" => $it_b_pay,
					"extern_token" => $extern_token,
					"_input_charset" => trim ( strtolower ( $alipay_config ['input_charset'] ) ) 
			);
		} else {
			$parameter = array (
					"service" => "create_direct_pay_by_user",
					"partner" => trim ( $alipay_config ['partner'] ),
					"seller_email" => trim ( $alipay_config ['seller_email'] ),
					"payment_type" => $payment_type,
					"notify_url" => $alipay_config [notify_url],
					"return_url" => $alipay_config [return_url],
					"out_trade_no" => $out_trade_no,
					"subject" => $subject,
					"total_fee" => $total_fee,
					"body" => $body,
					"show_url" => $show_url,
					"anti_phishing_key" => $anti_phishing_key,
					"exter_invoke_ip" => $exter_invoke_ip,
					"_input_charset" => trim ( strtolower ( $alipay_config ['input_charset'] ) ) 
			);
		}
		// 建立请求
		$alipaySubmit = new \AlipaySubmit ( $alipay_config );
		$html_text = $alipaySubmit->buildRequestForm ( $parameter, "get", "确认" );
		echo $html_text;
	}
	public function weixin($data) {
		require ("weixin/weixin.php");
		
		$data_pay = array (
				"trade_no" => $data ['out_trade_no'] 
		);
		$data_pay ["body"] = $data ['body'];
		$data_pay ["fee"] = $data ['total_fee'];
		$data_pay ["openid"] = $data ['openid'];
		$data_pay ["notify"] = $this->url . U ( 'weixin_notify' );
		$wx = new \weixin ();
		// print_r($data_pay);exit;
		$wx->pay ( $data_pay );
	}
	public function check_alipay_success() {
		require ("alipay/alipay.config.php");
		require ("alipay/lib/alipay_notify.class.php");
		// 计算得出通知验证结果
		$alipayNotify = new \AlipayNotify ( $alipay_config );
		$verify_result = $alipayNotify->verifyNotify ();
		if ($verify_result) { // 验证成功
			$out_trade_no = $_POST ['out_trade_no'];
			return true;
		} else {
			// 验证失败
			return false;
		}
	}
	public function check_alipay_wap_success() {
		require ("alipaywap/alipay.config.php");
		require ("alipaywap/lib/alipay_notify.class.php");
		
		// 计算得出通知验证结果
		$alipayNotify = new \AlipayNotify ( $alipay_config );
		$verify_result = $alipayNotify->verifyNotify ();
		if ($verify_result) { // 验证成功
			return true;
		} else {
			// 验证失败
			return false;
		}
	}
	public function xingye($data) {
		$mch_id = "7551000001";
		$key = "9d101c97133837e13dde2d32a5054abb";
		
		$mch_id = "280138000017";
		$key = "754078e5a695281d5d3b18c2";
		
		$str = "sdgvesjnyrherds";
		$d = array (
				"service" => "pay.weixin.jspay",
				"mch_id" => $mch_id,
				"out_trade_no" => $data ['out_trade_no'],
				"body" => $data ['body'],
				"total_fee" => $data ['total_fee'] * 100,
				"mch_create_ip" => $_SERVER ['SERVER_ADDR'],
				"notify_url" => $this->url . U ( 'xingye_notify' ),
				"nonce_str" => $str 
		);
		$wx = \koko::getObj ( 'wx' );
		ksort ( $d );
		$sign = strtoupper ( md5 ( $wx->ToUrlParams ( $d ) . "&key=" . $key ) );
		$d ["sign"] = $sign;
		$xml = $wx->ToXml ( $d );
		$ret = $wx->postXmlCurl ( "https://pay.swiftpass.cn/pay/gateway", $xml );
		$ret = $wx->FromXml ( $ret );
		if ($ret ['status'] == 0) {
			\koko::header ( "https://pay.swiftpass.cn/pay/jspay?token_id=" . $ret ['token_id'] . "&showwxpaytitle=1" );
		} else {
			print_r ( $ret );
		}
	}
	public function xingye_bank($data) {
		// print_r ( $data );
		require_once ('xingyebank/epay.config.php');
		require_once ('xingyebank/lib/epay_core.class.php');
		$epay = new \EPay ( $epay_config );
		$type = isset ( $data ['redirect_type'] ) ? $data ['redirect_type'] : 'gp_pay';
		if ("ep_auth" === $type) { // Ex.1-1 快捷支付认证跳转
			$trac_no = $data ['trac_no'];
			$acct_type = $data ['acct_type'];
			$bank_no = $data ['bank_no'];
			$card_no = $data ['card_no'];
			
			// 【重要】出于安全考虑，在调用函数前，需要对上面的参数进行防护过滤等操作
			echo $epay->epAuth ( $trac_no, $acct_type, $bank_no, $card_no );
		} else if ("ep_authpay" === $type) { // Ex.1-9 无绑定账户的快捷支付接口
			
			$order_no = $data ['out_trade_no'];
			$order_amount = $data ['total_fee'];
			$order_title = $data ['subject'];
			$order_desc = $data ['body'];
			$remote_ip = $_SERVER ['REMOTE_ADDR']; // 客户端IP地址，可以使用自己实现的方法
			                                       
			// 【重要】出于安全考虑，在调用函数前，需要对上面的参数进行防护过滤等操作
			echo $epay->epAuthPay ( $order_no, $order_amount, $order_title, $order_desc, $remote_ip );
		} else if ("gp_pay" === $type) { // Ex.2-1 网关支付跳转
			
			$order_no = $data ['out_trade_no'];
			$order_amount = $data ['total_fee'];
			$order_title = $data ['subject'];
			$order_desc = $data ['body'];
			$remote_ip = $_SERVER ['REMOTE_ADDR']; // 客户端IP地址，可以使用自己实现的方法
			                                       
			// 【重要】出于安全考虑，在调用函数前，需要对上面的参数进行防护过滤等操作
			echo $epay->gpPay ( $order_no, $order_amount, $order_title, $order_desc, $remote_ip );
		}
	}
	
	// ////////////////////////////////////////////
	public function weixin_notify() {
		require ("weixin/weixin.php");
		$notify = new \PayNotifyCallBack ();
		$notify->Handle ( false );
	}
	public function xingye_notify() {
		$wx = \koko::getObj ( 'wx' );
		$data = $wx->FromXml ();
		file_put_contents ( "Public/wingye_notify.txt", json_encode ( $data ) );
		if ($data ['pay_result'] === '0') {
			$url = $this->url . U ( 'pay/notify_do' ) . "?out_trade_no={$data['out_trade_no']}";
			$ret = file_get_contents ( $url );
			if (strpos ( $ret, 'success' ) !== false) {
				echo 'success';
			}
		}
	}
}

?>