<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */

//file_put_contents('xxxx.txt',serialize($_POST)."\r\n\r\n",FILE_APPEND);
//$_POST=unserialize('a:22:{s:12:"payment_type";s:1:"1";s:7:"subject";s:25:"商品10元，运费20元";s:8:"trade_no";s:28:"2015112721001004610205494669";s:11:"buyer_email";s:16:"250578600@qq.com";s:10:"gmt_create";s:19:"2015-11-27 11:52:08";s:11:"notify_type";s:17:"trade_status_sync";s:8:"quantity";s:1:"1";s:12:"out_trade_no";s:25:"2015112711452180963489e52";s:9:"seller_id";s:16:"2088021995049584";s:11:"notify_time";s:19:"2015-11-27 11:52:08";s:4:"body";s:6:"描述";s:12:"trade_status";s:13:"TRADE_SUCCESS";s:19:"is_total_fee_adjust";s:1:"N";s:9:"total_fee";s:4:"0.01";s:11:"gmt_payment";s:19:"2015-11-27 11:52:08";s:12:"seller_email";s:16:"371654645@qq.com";s:5:"price";s:4:"0.01";s:8:"buyer_id";s:16:"2088302466358614";s:9:"notify_id";s:34:"67e72e7867aa43141d4a553a095586bkpi";s:10:"use_coupon";s:1:"N";s:9:"sign_type";s:3:"RSA";s:4:"sign";s:172:"Rg5YxsVh2xqHfwk48nCjR89Rs9UDJWTD+kGU7m1Te6Qs1VAeJURJQtMYela2LQeK7qCyXp4rgraLjD6OG1sQNbRLMCRewlhlbJhPglybD88BfZdXC988hbbmJ+v24L3mvWfl57m8OlpRsKLjHY7om5Xg2Z96fDgN/RMdvrJjS+U=";}');
//print_r($_POST);
require_once("alipay.config.php");
require_once("lib/alipay_notify.class.php");

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if($verify_result) {//验证成功
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代
echo "cg";
    file_put_contents('xx.txt',"succ\r\n",FILE_APPEND);
	
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
	
    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
	
	//商户订单号
	$out_trade_no = $_POST['out_trade_no'];

	//支付宝交易号
	$trade_no = $_POST['trade_no'];

	//交易状态
	$trade_status = $_POST['trade_status'];


    if($_POST['trade_status'] == 'TRADE_FINISHED') {
		//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
			//如果有做过处理，不执行商户的业务程序
				
		//注意：
		//退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知

        //调试用，写文本函数记录程序运行情况是否正常
        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    }
    else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
		//判断该笔订单是否在商户网站中已经做过处理
			//如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
			//请务必判断请求时的total_fee、seller_id与通知时获取的total_fee、seller_id为一致的
			//如果有做过处理，不执行商户的业务程序
				
		//注意：
		//付款完成后，支付宝系统发送该交易状态通知

        //调试用，写文本函数记录程序运行情况是否正常
        //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
    }

	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
        
	echo "success";		//请不要修改或删除
	$url="http://{$_SERVER['HTTP_HOST']}/index.php/Home/Index/notify/?order_sn={$_POST['out_trade_no']}";
    file_get_contents($url);
//    file_put_contents('xx.txt',$url,FILE_APPEND);
	echo "\r\nsuccess";		//请不要修改或删除
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
    echo "fail";
//    file_put_contents('xx.txt',"faild\r\n",FILE_APPEND);

    //调试用，写文本函数记录程序运行情况是否正常
    //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
}
?>