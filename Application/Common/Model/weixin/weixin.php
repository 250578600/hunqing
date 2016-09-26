<?php
ini_set ( 'date.timezone', 'Asia/Shanghai' );
// error_reporting(E_ERROR);
require_once "lib/WxPay.Api.php";
require_once "api/WxPay.JsApiPay.php";
require_once 'api/log.php';
require_once 'lib/WxPay.Notify.php';
class weixin {
	public function pay($data) {
		// 初始化日志
		$logHandler = new CLogFileHandler ( "logs/" . date ( 'Y-m-d' ) . '.log' );
		$log = Log::Init ( $logHandler, 15 );
		
		// 打印输出数组信息
		
		// ①、获取用户openid
		$tools = new JsApiPay ();
		$openId =$data ["openid"];
		if(!$openId){
			$openId = $tools->GetOpenid ();
		}
		// ②、统一下单
		$input = new WxPayUnifiedOrder ();
		$input->SetBody ( $data ['body'] );
		$input->SetAttach ( "微信支付" );
		$input->SetOut_trade_no ( $data ['trade_no'] );
		$input->SetTotal_fee ( $data ["fee"] * 100 );
		$input->SetTime_start ( date ( "YmdHis" ) );
		$input->SetTime_expire ( date ( "YmdHis", time () + 600 ) );
		$input->SetGoods_tag ( "test" );
		$input->SetNotify_url ( $data ["notify"] );
		$input->SetTrade_type ( "JSAPI" );
		$input->SetOpenid ( $openId );
		$order = WxPayApi::unifiedOrder ( $input );
		// echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
		// printf_info($order);
		$jsApiParameters = $tools->GetJsApiParameters ( $order );
		
		// 获取共享收货地址js函数参数
		$editAddress = $tools->GetEditAddressParameters ();
		include "show.html";
	}
	function printf_info($data) {
		foreach ( $data as $key => $value ) {
			echo "<font color='#00ff55;'>$key</font> : $value <br/>";
		}
	}
}
class PayNotifyCallBack extends WxPayNotify {
	// 查询订单
	public function Queryorder($transaction_id) {
		$input = new WxPayOrderQuery ();
		$input->SetTransaction_id ( $transaction_id );
		$result = WxPayApi::orderQuery ( $input );
		if (array_key_exists ( "return_code", $result ) && array_key_exists ( "result_code", $result ) && $result ["return_code"] == "SUCCESS" && $result ["result_code"] == "SUCCESS") {
			return true;
		}
		return false;
	}
	
	// 重写回调处理函数
	public function NotifyProcess($data, &$msg) {
		$notfiyOutput = array ();
		
		if (! array_key_exists ( "transaction_id", $data )) {
			$msg = "输入参数不正确";
			return false;
		}
		// 查询订单，判断订单真实性
		if (! $this->Queryorder ( $data ["transaction_id"] )) {
			$msg = "订单查询失败";
			return false;
		}
		//  这里是测
		file_put_contents ( "1.txt", json_encode ( $data ) );
		$url = "http://" . $_SERVER ["SERVER_NAME"] . ($_SERVER ["SERVER_PORT"] == 80 ? "" : ":" . $_SERVER ["SERVER_PORT"]) . U ( 'pay/notify_do' ) . "?out_trade_no={$data['out_trade_no']}";
		$ret=file_get_contents ( $url ); 
//		file_put_contents ( "ret.txt", $ret );
//		file_put_contents ( "url.txt", $url );
		return true;
	}
}
