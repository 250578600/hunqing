<?php

// EPAY商户配置
$epay_config = array();

// 商户号，格式如A0000001
$epay_config['appid']		= '280138000017';

// 商户私有密钥，该密钥需要严格保密，只能出现在后台服务端代码中，不能放在可能被用户查看到的地方，如html、js代码等
// 在发送报文给收付直通车时，会使用该密钥进行签名（SHA1算法方式）
// 在收到收付直通车返回的报文时，将使用该密钥进行验签
$epay_config['commKey']		= "754078e5a695281d5d3b18c2";

// 商户客户端证书路径，该证书需要严格保密
// 在发送报文给收付直通车时，会使用该密钥进行签名（RSA算法方式）
$epay_config['mrch_cert']		= dirname(__FILE__).'/key/appsvr_client.pfx';
// 以下证书参数一般为默认值，无需更改
$epay_config['mrch_cert_pwd']	= '123456';
// 收付直通车服务器证书，RSA算法验签使用
$epay_config['epay_cert_test']	= dirname(__FILE__).'/key/appsvr_server_test.pem';
$epay_config['epay_cert_prod']	= dirname(__FILE__).'/key/appsvr_server_prod.pem';

// 二级商户名称，可为空
$epay_config['sub_mrch']	= "SDK-PHP测试商城";

// 是否为开发测试模式，true时将连接测试环境，false时将连接生产环境
$epay_config['isDevEnv']	= true;
// 是否验签，true验证应答报文签名，false不验证签名，开发调试时可修改此项为false，生产环境请更改为true
$epay_config['needChkSign'] = true;

// 代理设置，设为null为不使用代理
$epay_config['proxy_ip']	= null;
$epay_config['proxy_port']	= null;
// $epay_config['proxy_ip']	= '1.2.3.4';
// $epay_config['proxy_port']	= 8080;
