<?php
// 商户回调接口的页面
// 收付直通车在支付成功、认证成功等事件发生时，会异步调用商户提供的URL回调地址，并把参数送过去
// 商户根据接收到的回调通知，需要按以下步骤进行处理:
// 1、取出所有参数
// 2、对MAC进行验签操作，确保消息来自收付直通车而不是伪造的【非常重要】
// 3、根据通知类型(event参数)，确定收到的通知类型
// 4、根据其它参数，如order_no等，进行相应的商户业务逻辑处理
// 本页面中的代码仅供参考，商户需要自行编码实现自己的逻辑，注意加上HTML头部等完整格式
require_once('epay.config.php');
require_once('lib/epay_core.class.php');

$method = $_SERVER['REQUEST_METHOD'];
$epay = new EPay($epay_config);

if('GET' === $method && $epay -> VerifyMac($_GET, $epay_config['commKey']) ||
	'POST' === $method && $epay -> VerifyMac($_POST, $epay_config['commKey'])) {	//验签成功
	
	if('GET' === $method) {				//前台通知
		
		// 商户可以在这边进行 [前台] 回调通知的业务逻辑处理
		// 注意：后台通知和前台通知有可能同时到来，注意 [需要防止重复处理]
		// 前台跳转回来的通知，需要显示内容，如支付成功等
		if("NOTIFY_ACQUIRE_SUCCESS" === $_GET["event"]) {			//支付成功通知
			
			$order_no = $_GET["order_no"];
			// $order_amount = ......
			// 商户可以从$_GET中获取通知中的数据
			// 然后进行支付成功后的业务逻辑处理，这里为写入notify_log.txt文件
			file_put_contents("notify_log.txt", "[前台通知]订单".$order_no."支付成功@".date('YmdHis')."\r\n", FILE_APPEND);
			
			// 这里是用户跳转到商户回调地址时显示的内容
			echo "订单".$order_no."支付成功@".date('YmdHis');
			
		} else if("NOTIFY_ACQUIRE_FAIL" === $_GET["event"])	{		// 支付失败通知
			
			// 支付失败业务逻辑处理
			
		} else if("NOTIFY_REFUND_SUCCESS" === $_GET["event"]) {		// 退款成功通知
			
			// 退款成功业务逻辑处理
			
		} else if("NOTIFY_AUTH_SUCCESS" === $_GET["event"]) {		// 快捷支付认证成功通知
			
			// 认证成功业务逻辑处理
		}
		
	} else if('POST' === $method) {		// 后台通知
		
		// 商户可以在这边进行 [后台] 回调通知的业务逻辑处理
		// 注意：后台通知和前台通知有可能同时到来，注意 [需要防止重复处理]
		if("NOTIFY_ACQUIRE_SUCCESS" === $_POST["event"]) {			// 支付成功通知
			
			// 支付成功业务逻辑处理
			$order_no = $_POST["order_no"];
			file_put_contents("notify_log.txt", "[后台通知]订单".$order_no."支付成功@".date('YmdHis')."\r\n", FILE_APPEND);
			
			//后台通知用户不会看到页面，所以不需要显示页面内容
			
		} else if("NOTIFY_ACQUIRE_FAIL" === $_POST["event"])	{	// 支付失败通知
			
			// 支付失败业务逻辑处理
			
		} else if("NOTIFY_REFUND_SUCCESS" === $_POST["event"]) {	// 退款成功通知
			
			// 退款成功业务逻辑处理
			
		} else if("NOTIFY_AUTH_SUCCESS" === $_POST["event"]) {		// 快捷支付认证成功通知
			
			// 认证成功业务逻辑处理
			file_put_contents("notify_log.txt", "[后台通知]认证成功@".date('YmdHis')."\r\n", FILE_APPEND);
		}
	}

} else {					// 验签失败
	
	// 不应当进行业务逻辑处理，即把该通知当无效的处理
	// 商户可以在此记录日志等
	echo "验签失败";
}
