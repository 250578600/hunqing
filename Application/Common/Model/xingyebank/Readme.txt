
┌───────────────┐
│     兴业银行 收付直通车      │
│          代码示例            │
└───────────────┘

	开发语言：PHP 5.3+
	版本：1.1.2 beta

────────────
注意事项
────────────

1.SDK中的代码仅为示例，供商户参考。若商户应用于生产环境中，需要自行进行定制修改，并考虑安全方面的问题。
2.SDK提供的接口对传入参数没有作检查和过滤，商户在传入参数前，需要自行对传入的参数进行检查和过滤。
  特别是生成跳转页面代码的接口中（如网关支付接口等），请务必对传入参数进行安全性检查，防止出现XSS攻击等安全问题。
3.SDK示例中的商户号(appid)和商户秘钥（commKey）仅为示例，实际使用时需要向运营人员索取测试环境的商户号和商户秘钥并
  设置，否则使用SDK中示例的商户号和商户秘钥时，收付直通车会返回“MAC校验失败”或“未签署商户合约或合约无效”。


────────────
文件结构说明
────────────
    epay_acquire_php_utf-8
        │
        │  epay.config.php			配置文件
        │  epay_notify.php			回调通知页面
        │  epay_redirect.php			跳转接口页面
        │  example.php				API调用示例（请直接打开源代码查看）
        │  index.php				跳转示例页面
        │  Readme.txt				说明文件
        │
        ├─lib
        │       ca-bundle.crt			信任CA证书库
        │       epay_core.class.php		收付直通车API类
        │       epay_util.class.php		收付直通车工具类
        │
	└─key
                 appsvr_client.pfx		商户示例证书
                 appsvr_server_prod.pem		生产环境服务端证书
                 appsvr_server_test.pem		测试环境服务端证书


────────────
环境配置说明
────────────
1.本SDK需要使用php_curl扩展作为http通讯使用、使用php_openssl扩展作为RSA签名验签使用。
  因此，请确保商户系统运行的PHP环境中包含php_curl和php_openssl扩展。

2.若你的环境无法启用php_curl扩展，需要自行修改epay_util.class.php文件中的getHttpPostResponse方法。
  若你的环境无法启用php_openssl扩展，需要自行修改epay_core.class.php文件中的Signature和VerifyMac方法。

3.epay_util.class.php文件中上方5个常量为客户端失败时（如网络异常等），返回的错误提示，非服务端返回内容。
  因此，你可以自己修改为需要的内容，也可以直接使用原有文件中的内容。

4.调试时，可以去掉epay_util.class.php文件getHttpPostResponse方法中var_dump(curl_error($curl));和
  var_dump(curl_getinfo($curl));两行前面的注释，来显示curl通讯过程中的信息。
  调试时，如果出现“应答消息验签失败，交易未决”的提示，可以临时设置$epay_config['needChkSign'] = false。
  注意生产上需要设置该值为true，来验证应答报文签名。

5.epay.config.php中的参数为示例参数，商户需要修改为自己的配置。
  商户系统可以根据需要在其它地方初始化这些参数，只要在实例化EPay类时，传入配置参数数组即可。

6.日期时间必须为北京时间，注意操作系统或php.ini中的时区和时间设置。
  如果收付直通车提示“订单已过期”等类似提示，请检查系统时间是否正确。


────────────
商户需要修改或配置的文件
────────────
epay_notify.php：
需要删除原有的示例业务逻辑代码，并添加自己的业务逻辑处理代码。

epay_redirect.php
该页面仅供参考，商户可以实现自己的页面。
例如只使用网关支付方式的话，可以去掉里面快捷支付的相关内容。
同时该页里面也需要加上商户自己的业务逻辑，如订单状态变更为支付中等。
需要注意的时，SDK中接口不对传入参数进行检查，因此，在调用SDK中的接口前，商户系统务必对用户输入的参数进行合法性检查和过滤，防止出现安全问题。


────────────
可供商户调用的接口说明
────────────
建议配合收付直通车代收接口文档查看。
[重要]各传入参数SDK都不作任何检查、过滤，请务必在传入前进行安全检查或过滤，特别是生成HTML代码的方法，务必保证传入参数的安全性，否则会导致安全问题。

Class EPay:

实例化：
$epay	= new EPay($epay_config);

★ 快捷支付部分：

* 快捷支付账户认证跳转页面生成接口
* 该方法将生成跳转页面的全部HTML代码，商户直接输出该HTML代码至某个URL所对应的页面中，即可实现跳转，可以参考示例epay_redirect.php中的用法
* @param string $trac_no		商户跟踪号
* @param string $acct_type		卡类型：0-储蓄卡,1-信用卡,2-企业账户
* @param string $bank_no		人行联网行号
* @param string $card_no		账号
* @return	string			跳转页面HTML代码
public function epAuth($trac_no, $acct_type, $bank_no, $card_no, $user_name = null, $cert_no = null, $card_phone = null, $expireDate = null, $cvn = null)


* 快捷支付认证接口（同步接口，需短信确认）
* @param string $trac_no		商户跟踪号
* @param string $acct_type		卡类型：0-储蓄卡,1-信用卡
* @param string $bank_no		人行联网行号
* @param string $card_no		账号
* @param string $user_name		姓名
* @param string $cert_no		证件号码
* @param string $card_phone		联系电话
* @param string $expireDate		信用卡有效期（仅信用卡时必输，格式MMYY）
* @param string $cvn			信用卡CVN（仅信用卡时必输）
* @return	string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function epAuthSyncWithSms($trac_no, $acct_type, $bank_no, $card_no, $user_name, $cert_no, $card_phone, $expireDate = null, $cvn = null)


* 快捷认证短信验证码确认接口
* @param string $trac_no		发起同步认证时的商户跟踪号
* @param string $sms_code		6位数字短信验证码
* @return string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function epAuthCheckSms($trac_no, $sms_code)


* 快捷支付账户解绑接口
* @param string $card_no		账号
* @return string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function epAuthCancel($card_no)


* 快捷支付账户认证结果查询接口
* @param string $trac_no		商户跟踪号
* @return	string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function epAuthQuery($trac_no)


* 快捷支付交易接口
* @param string $order_no		订单号
* @param string $order_amount		金额，单位元，两位小数，例：8.00
* @param string $order_title		订单标题
* @param string $order_desc		订单描述
* @param string $card_no		支付卡号
* @return	string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function epPay($order_no, $order_amount, $order_title, $order_desc, $card_no)


* 快捷支付交易查询接口
* @param string $order_no		订单号
* @param string $order_date		订单日期，格式yyyyMMdd，为null时使用当前日期
* @return	string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function epQuery($order_no, $order_date = null)


* 快捷支付退款交易接口
* @param string $order_no		待退款订单号
* @param string $order_date		订单下单日期，格式yyyyMMdd
* @param string $order_amount		退款金额（不能大于原订单金额）
* @return	string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function epRefund($order_no, $order_date, $order_amount)


* 快捷支付退款交易结果查询接口
* @param string $order_no		退款的订单号
* @param string $order_date		订单日期，格式yyyyMMdd，为null时使用当前日期
* @return	string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function epRefundQuery($order_no, $order_date = null)


* 无绑定账户快捷支付跳转页面生成接口
* 该方法将生成跳转页面的全部HTML代码，商户直接输出该HTML代码至某个URL所对应的页面中，即可实现跳转，可以参考epay_redirect.php中相关示例
* @param string $order_no		订单号
* @param string $order_amount		金额，单位元，两位小数，例：8.00
* @param string $order_title		订单标题
* @param string $order_desc		订单描述
* @param string $remote_ip		客户端IP地址
* @return	string			跳转页面HTML代码
public function epAuthPay($order_no, $order_amount, $order_title, $order_desc, $remote_ip, $bank_no = null, $acct_type = null, $card_no = null, $user_name = null, $cert_no = null, $card_phone = null, $expireDate = null, $cvn = null)


★ 网关支付部分：

* 网关支付交易跳转页面生成接口
* 该方法将生成跳转页面的全部HTML代码，商户直接输出该HTML代码至某个URL所对应的页面中，即可实现跳转，可以参考epay_redirect.php中示例
* @param string $order_no		订单号
* @param string $order_amount		金额，单位元，两位小数，例：8.00
* @param string $order_title		订单标题
* @param string $order_desc		订单描述
* @param string $remote_ip		客户端IP地址
* @return	string			跳转页面HTML代码
public function gpPay($order_no, $order_amount, $order_title, $order_desc, $remote_ip)


* 网关支付交易查询接口
* @param string $order_no		订单号
* @param string $order_date		订单日期，格式yyyyMMdd，为null时使用当前日期
* @return	string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function gpQuery($order_no, $order_date = null)


* 网关支付退款交易接口
* @param string $order_no		待退款订单号
* @param string $order_date		订单下单日期，格式yyyyMMdd
* @param string $order_amount		退款金额（不能大于原订单金额）
* @return	string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function gpRefund($order_no, $order_date, $order_amount)


* 网关支付退款交易结果查询接口
* @param string $order_no		退款的订单号
* @param string $order_date		订单日期，格式yyyyMMdd，为null时使用当前日期
* @return	string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function gpRefundQuery($order_no, $order_date = null)


★ 智能代付部分：

* 智能代付单笔付款接口
* @param string $order_no		订单号
* @param string $to_bank_no		收款行行号
* @param string $to_acct_no		收款人账户
* @param string $to_acct_name		收款人户名
* @param string $acct_type		账户类型：0-储蓄卡,1-信用卡,2-对公账户
* @param string $trans_amt		付款金额
* @param string $trans_usage		用途
* @return string			json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function pyPay($order_no, $to_bank_no, $to_acct_no, $to_acct_name, $acct_type, $trans_amt, $trans_usage)


* 智能代付单笔订单查询接口
* @param string $order_no      		订单号
* @return string               		json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function pyQuery($order_no)


* 智能代付商户信息查询接口
* @return string               		json格式结果，返回结果包含字段请参看收付直通车代收接口文档
public function pyGetMrch()


★ 对账文件下载：

* 对账文件下载接口
* @param string $rcpt_type		回单类型：0-快捷入账回单；1-快捷出账回单；2-快捷手续费回单；3-网关支付入账回单；4-网关支付出账回单；5-网关支付手续费回单；6-代付入账回单；7-代付出账回单；8-代付手续费回单
* @param string $trans_date		交易日期，格式yyyyMMdd
* @param string $save_file_name		保存下载内容至以该变更为名的文件
* @return	string			当下载成功时，返回SUCCESS_RESULT常量值；当下载失败时，返回失败信息json字符串
public function dlSettleFile($rcpt_type, $trans_date, $save_file_name)

* 行号文件下载接口
* @param string $download_type		文件类型：01-行号文件
* @param unknown $save_file_name	保存下载内容至以该变更为名的文件
* @return string			当下载成功时，返回SUCCESS_RESULT常量值；当下载失败时，返回失败信息json字符串
public function dlFile($download_type, $save_file_name)
