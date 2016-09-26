<?php
require_once('epay.config.php');
require_once('lib/epay_core.class.php');

// 【重要】该页面仅为示例页面，在正式使用时，应当先对传入参数进行安全性检查和过滤，防止XSS等攻击。SDK中的方法没有对传入的参数进行任何检查和过滤。

$type = $_POST['redirect_type'];

$epay = new EPay($epay_config);

if("ep_auth" === $type) {						// Ex.1-1 快捷支付认证跳转

	$trac_no	= $_POST['trac_no'];
	$acct_type	= $_POST['acct_type'];
	$bank_no	= $_POST['bank_no'];
	$card_no	= $_POST['card_no'];
	
	// 【重要】出于安全考虑，在调用函数前，需要对上面的参数进行防护过滤等操作
	echo $epay -> epAuth($trac_no, $acct_type, $bank_no, $card_no);

} else if("ep_authpay" === $type) { 			// Ex.1-9 无绑定账户的快捷支付接口

	$order_no		= $_POST['order_no'];
	$order_amount	= $_POST['order_amount'];
	$order_title	= $_POST['order_title'];
	$order_desc		= $_POST['order_desc'];
	$remote_ip		= $_SERVER['REMOTE_ADDR'];	// 客户端IP地址，可以使用自己实现的方法
	
	// 【重要】出于安全考虑，在调用函数前，需要对上面的参数进行防护过滤等操作
	echo $epay -> epAuthPay($order_no, $order_amount, $order_title, $order_desc, $remote_ip);

} else if("gp_pay" === $type) {				// Ex.2-1 网关支付跳转

	$order_no		= $_POST['order_no'];
	$order_amount	= $_POST['order_amount'];
	$order_title	= $_POST['order_title'];
	$order_desc		= $_POST['order_desc'];
	$remote_ip		= $_SERVER['REMOTE_ADDR'];	// 客户端IP地址，可以使用自己实现的方法
	
	// 【重要】出于安全考虑，在调用函数前，需要对上面的参数进行防护过滤等操作
	echo $epay -> gpPay($order_no, $order_amount, $order_title, $order_desc, $remote_ip);

}
