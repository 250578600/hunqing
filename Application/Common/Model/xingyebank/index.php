<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>收付直通车示例页面</title>
    <style type="text/css">
    body{font-family:Microsoft YaHei,Tahoma,Helvetica,Arial,sans-serif;}
    .header{border-bottom:3px solid #28529A;}
    .fix{margin:0 auto;width:790px;}
    .header .inner{color:#004392;font-size:18px;height:50px;line-height:40px;position:relative;}
    .fixtop{padding:15px 0;}
    input[type=submit]{border:2px solid #28529A;background-color:white;margin:10px 0;font-size:14px;padding:5px;}
    input[type=text]{border:1px solid grey;font-size:16px;}
    </style>
  </head>
  
  <body>
  	<div class="header"><h2 class="inner fix">兴业银行 收付直通车 跳转接口示例</h2></div>
  	<div class="fixtop fix">
  	<h4>！！ 请务必先参看example.php和Readme.txt中的说明。</h4>
  	<hr />
  	
    <p><b>Ex.1-1</b> 快捷支付认证：</p>
    <form name="epauth" action="epay_redirect.php" method="post">
    <input type="text" name="trac_no" value="<?php echo 'TN'.date('YmdHis'); ?>" width="32" maxlength="32" placeholder="商户系统跟踪号" />
    <input type="text" name="acct_type" value="0" style="width:100px" maxlength="32" placeholder="账号类型" />
    <input type="text" name="bank_no" value="105100000017" width="32" maxlength="12" placeholder="人行联网行号" />
    <input type="text" name="card_no" value="6222801234567888953" width="32" maxlength="20" placeholder="账号" />
    <input type="hidden" name="redirect_type" value="ep_auth" /><br />
    <input type="submit" value="点此跳转至快捷认证" />
    </form>
    <hr />
    
    <p><b>Ex.1-9</b> 无绑定账户的快捷支付接口：</p>
    <form name="epauthpay" action="epay_redirect.php" method="post">
    <input type="text" name="order_no" value="<?php echo 'SDK'.date('YmdHis'); ?>" width="32" maxlength="32" placeholder="商户订单号" />
    <input type="text" name="order_amount" value="1.00" style="width:100px" maxlength="32" placeholder="订单金额" />
    <input type="text" name="order_title" value="SDK测试订单" width="32" maxlength="12" placeholder="订单标题" />
    <input type="text" name="order_desc" value="欢迎使用收付直通车" width="32" maxlength="32" placeholder="订单描述" />
    <input type="hidden" name="redirect_type" value="ep_authpay" /><br />
    <input type="submit" value="点此跳转至无签约快捷支付" />
    </form>
    <hr />
    
    <p><b>Ex.2-1</b> 网关支付：</p>
    <form name="gppay" action="epay_redirect.php" method="post">
    <input type="text" name="order_no" value="<?php echo 'SDK'.date('YmdHis'); ?>" width="32" maxlength="32" placeholder="商户订单号" />
    <input type="text" name="order_amount" value="1.00" style="width:100px" maxlength="32" placeholder="订单金额" />
    <input type="text" name="order_title" value="SDK测试订单" width="32" maxlength="12" placeholder="订单标题" />
    <input type="text" name="order_desc" value="欢迎使用收付直通车" width="32" maxlength="32" placeholder="订单描述" />
    <input type="hidden" name="redirect_type" value="gp_pay" /><br />
    <input type="submit" value="点此跳转至网关支付" />
    </form>
    </div>
  </body>
</html>
