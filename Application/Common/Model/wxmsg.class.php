<?php

namespace Common\Model;

class wxmsg {
	private $token; // 公共平台申请时填写的token
	private $account;
	private $password;
	
	// 每次登录后将cookie, webToken缓存起来, 调用其它api时直接使用
	// 注: webToken与token不一样, webToken是指每次登录后动态生成的token, 供难证用户是否登录而用
	private $cookiePath; // 保存cookie的文件路径
	private $webTokenPath; // 保存webToken的文路径
	                       
	// 缓存的值
	private $webToken; // 登录后每个链接后都要加token
	private $cookie;
	
	// 构造函数
	public function __construct($account = null, $passwd = null, $use_cache = true) {
		// 配置初始化
		$this->account = $account;
		$this->password = $passwd;
		$this->account = "kghgc@brc.com.cn";
		$this->password = "Brc888888";
		$this->cookiePath = dirname ( __FILE__ ) . '\cookie';
		$this->webTokenPath = dirname ( __FILE__ ) . '\webToken';
		// 从缓存中读取cookie, webToken 判断是否已经登录(未登录则执行登录操作)
		$this->getCookieAndWebToken ( $use_cache );
	}
	
	/**
	 * 从缓存中得到cookie和webToken
	 * 
	 * @param $use_cache 是否使用cookie文件缓存        	
	 * @return mix
	 */
	public function getCookieAndWebToken($use_cache) {
		$this->cookie = file_exists ( $this->cookiePath ) ? file_get_contents ( $this->cookiePath ) : '';
		$this->webToken = file_exists ( $this->webTokenPath ) ? file_get_contents ( $this->webTokenPath ) : '';
		// 如果有缓存信息, 则验证下有没有过时, 此时只需要访问一个api即可判断
		if ($use_cache && $this->cookie && $this->webToken) {
			$re = $this->getUserInfo ( '1' );
			if (is_array ( $re )) {
				return true;
			} else {
				return $this->login ();
			}
		} else {
			return $this->login ();
		}
	}
	
	/**
	 * 模拟登录获取cookie和webToken
	 */
	public function login() {
		$url = "https://mp.weixin.qq.com/cgi-bin/login?lang=zh_CN";
		$post ["username"] = $this->account;
		$post ["pwd"] = md5 ( $this->password );
		$post ["f"] = "json";
		$re = $this->submit ( $url, $post );
		// 保存cookie
		$this->cookie = $re ['cookie'];
		file_put_contents ( $this->cookiePath, $this->cookie );
		
		// 得到token
		$this->getWebToken ( $re ['body'] );
		
		return true;
	}
	
	/**
	 * 获取粉丝的用户信息
	 * 
	 * @param string $fakeId
	 *        	用户的fakeId
	 * @return mix 返回用于信息
	 */
	public function getUserInfo($fakeId) {
		$url = "https://mp.weixin.qq.com/cgi-bin/getcontactinfo?t=ajax-getcontactinfo&lang=zh_CN&token={$this->webToken}&fakeid=$fakeId";
		$re = $this->submit ( $url, array (), $this->cookie );
		$result = json_decode ( $re ['body'], 1 );
		/**
		 * array (size=1)
		 * 'base_resp' =>
		 * array (size=2)
		 * 'ret' => int -3
		 * 'err_msg' => string 'invalid session' (length=15)
		 */
		if (! $result || (! empty ( $result ['base_resp'] ) && $result ['base_resp'] ['ret'] == - 3)) {
			$this->login ();
		}
		return $result;
	}
	public function getBossInfo() {
		$url = "https://mp.weixin.qq.com/cgi-bin/settingpage?t=setting/index&action=index&token={$this->webToken}&lang=zh_CN";
		$res = $this->get ( $url, $this->cookie );
		preg_match_all ( '/<div class=\"meta_content\">(.*)<\/div>/U', $res ['body'], $matchs );
		$bossInfo = array ();
		if (count ( $matchs ) == 2) {
			foreach ( $matchs [1] as $k => $v ) {
				switch ($k) {
					case 0 :
						preg_match ( '/src=\"(.*)\"/', $v, $match_img );
						$bossInfo ['logo'] = 'https://mp.weixin.qq.com' . $match_img ['1'];
						break;
					case 1 :
						$bossInfo ['mp_name'] = trim ( strip_tags ( $v ) );
						break;
					case 2 :
						$bossInfo ['email'] = trim ( strip_tags ( $v ) ); // 登录邮箱
						$bossInfo ['mp_username'] = trim ( strip_tags ( $v ) );
						break;
					case 3 :
						$bossInfo ['mp_fakeid'] = trim ( strip_tags ( $v ) );
						$bossInfo ['openid'] = trim ( strip_tags ( $v ) );
						break;
					case 4 :
						$bossInfo ['mp_wxid'] = trim ( strip_tags ( $v ) );
						break;
					case 8 :
						$bossInfo ['address'] = trim ( strip_tags ( $v ) );
						break;
				}
			}
		}
		return $bossInfo;
	}
	public function downloadImage($imgUrl) {
		$res = $this->get ( $imgUrl, $this->cookie );
		return $res ['body'];
	}
	
	/**
	 * 登录后从结果中解析出webToken
	 * 
	 * @param [String] $logonRet        	
	 * @return mix
	 */
	private function getWebToken($logonRet) {
		$logonRet = json_decode ( $logonRet, true );
		print_r ( $logonRet );
		$msg = $logonRet ["redirect_url"]; // /cgi-bin/indexpage?t=wxm-index&lang=zh_CN&token=1455899896
		$msgArr = explode ( "&token=", $msg );
		if (count ( $msgArr ) != 2) {
			return false;
		} else {
			$this->webToken = $msgArr [1];
			file_put_contents ( $this->webTokenPath, $this->webToken );
			return true;
		}
	}
	
	/**
	 * 主动发消息
	 * 
	 * @param string $id
	 *        	用户的fakeid
	 * @param string $content
	 *        	发送的内容
	 * @return mix
	 */
	public function send($id, $content) {
		$post = array ();
		$post ['tofakeid'] = $id;
		$post ['type'] = 1;
		$post ['content'] = $content;
		$post ['ajax'] = 1;
		$url = "https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response&token={$this->webToken}";
		$re = $this->submit ( $url, $post, $this->cookie );
		return $re ['body'];
		// return json_decode($re['body']);
	}
	
	/**
	 * 批量发送
	 * 
	 * @param [array] $ids
	 *        	用户的fakeid集合
	 * @param [type] $content
	 *        	[description]
	 * @return mix
	 */
	public function batSend($ids, $content) {
		$result = array ();
		foreach ( $ids as $id ) {
			$result [$id] = $this->send ( $id, $content );
		}
		return $result;
	}
	
	/**
	 * 发送图片
	 * 
	 * @param int $fakeId
	 *        	[description]
	 * @param int $fileId
	 *        	图片ID
	 * @return mix
	 */
	public function sendImage($fakeId, $fileId) {
		$post = array ();
		$post ['tofakeid'] = $fakeId;
		$post ['type'] = 2;
		$post ['fid'] = $post ['fileId'] = $fileId; // 图片ID
		$post ['error'] = false;
		$post ['ajax'] = 1;
		$post ['token'] = $this->webToken;
		
		$url = "https://mp.weixin.qq.com/cgi-bin/singlesend?t=ajax-response&lang=zh_CN";
		$re = $this->submit ( $url, $post, $this->cookie );
		
		return json_decode ( $re ['body'] );
	}
	
	/*
	 * 得到最近发来的信息 [0] => Array ( [id] => 189 [type] => 1 [fileId] => 0 [hasReply] => 0 [fakeId] => 1477341521 [nickName] => lealife [remarkName] => [dateTime] => 1374253963 ) [ok]
	 */
	public function getLatestMsgs($page = 0) {
		// frommsgid是最新一条的msgid
		$frommsgid = 100000;
		$offset = 50 * $page;
		$url = "https://mp.weixin.qq.com/cgi-bin/message?t=message/list&count=999999&day=7&offset={$offset}&token={$this->webToken}&lang=zh_CN";
		$re = $this->get ( $url, $this->cookie );
		// 解析得到数据 list : ({"msg_item":[{"id":}, {}]})
		$match = array ();
		preg_match ( '/["\' ]msg_item["\' ]:\[{(.+?)}\]/', $re ['body'], $match );
		if (count ( $match ) != 2) {
			return "";
		}
		$match [1] = "[{" . $match [1] . "}]";
		return json_decode ( $match [1], 1 );
	}
	
	// 解析用户信息
	// 当有用户发送信息后, 如何得到用户的fakeId?
	// 1. 从web上得到最近发送的信息
	// 2. 将用户发送的信息与web上发送的信息进行对比, 如果内容和时间都正确, 那肯定是该用户
	// 实践发现, 时间可能会不对, 相隔1-2s或10多秒也有可能, 此时如果内容相同就断定是该用户
	// 如果用户在时间相隔很短的情况况下输入同样的内容很可能会出错, 此时可以这样解决: 提示用户输入一些随机数.
	
	/**
	 * 通过时间 和 内容 双重判断用户
	 * 
	 * @param [type] $createTime        	
	 * @param [type] $content        	
	 * @return mix
	 */
	public function getLatestMsgByCreateTimeAndContent($createTime, $content) {
		$lMsgs = $this->getLatestMsgs ( 0 );
		// 最先的数据在前面
		$contentMatchedMsg = array ();
		foreach ( $lMsgs as $msg ) {
			// 仅仅时间符合
			if ($msg ['dateTime'] == $createTime) {
				// 内容+时间都符合
				if ($msg ['content'] == $content) {
					return $msg;
				}
				// 仅仅是内容符合
			} else if ($msg ['content'] == $content) {
				$contentMatchedMsg [] = $msg;
			}
		}
		// 最后, 没有匹配到的数据, 内容符合, 而时间不符
		// 返回最新的一条
		if ($contentMatchedMsg) {
			return $contentMatchedMsg [0];
		}
		return false;
	}
	
	/**
	 * 获取用户分组信息
	 * 
	 * @return mixed string
	 */
	public function getGroups() {
		$url = "https://mp.weixin.qq.com/cgi-bin/contactmanage?t=user/index&lang=zh_CN&token={$this->webToken}&pagesize=10&pageidx=0&type=0&groupid=0";
		$re = $this->get ( $url, $this->cookie );
		$result = json_decode ( $re ['body'], 1 );
		$match = array ();
		preg_match ( '/["\' ]groups["\' ]:\[{(.+?)}\]/', $re ['body'], $match );
		if (count ( $match ) != 2) {
			return "";
		}
		$match [1] = "[{" . $match [1] . "}]";
		return json_decode ( $match [1], 1 );
	}
	
	/**
	 * 获取fakeid
	 * 
	 * @param $pageNum 每页显示数量        	
	 * @return array Array
	 *         (
	 *         [0] => Array
	 *         (
	 *         [id] => 2858907422 //fakeid
	 *         [nick_name] => 黄海 //昵称
	 *         [remark_name] => //备注
	 *         [group_id] => 0 //分组ID
	 *         )
	 *         )
	 */
	public function getFakeid($pageNum) {
		$url = "https://mp.weixin.qq.com/cgi-bin/contactmanage?t=user/index&pagesize={$pageNum}&pageidx=0&type=0&token={$this->webToken}&lang=zh_CN";
		$re = $this->get ( $url, $this->cookie );
		print_r ( $re );
		preg_match ( '/["\' ]contacts["\' ]:\[{(.+?)}\]/', $re ['body'], $match );
		if (count ( $match ) != 2) {
			return "";
		}
		$match [1] = "[{" . $match [1] . "}]";
		return json_decode ( $match [1], 1 );
	}
	
	/**
	 * 返回array(cookie=>, body=>)
	 * 
	 * @param string $url
	 *        	url地址
	 * @param array $data
	 *        	提交的数据
	 * @param boolean $cookie        	
	 * @param boolean $isPost        	
	 * @return array
	 */
	private function exec($url, $data, $cookie = false, $isPost = true) {
		$dataStr = "";
		if ($data && is_array ( $data )) {
			foreach ( $data as $key => $value ) {
				$dataStr .= "$key=$value&";
			}
		}
		$curl = curl_init (); // 启动一个CURL会话
		curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); // 对认证证书来源的检查
		                                               // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
		curl_setopt ( $curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT'] ); // 模拟用户使用的浏览器
		curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 1 ); // 使用自动跳转
		                                               // curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
		$referer = "https://mp.weixin.qq.com/cgi-bin/singlemsgpage";
		$oldReferer = "https://mp.weixin.qq.com/";
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, array (
				"Referer:$referer" 
		) );
		
		if ($isPost) {
			curl_setopt ( $curl, CURLOPT_POST, 0 ); // 发送一个常规的Post请求
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $dataStr ); // Post提交的数据包
		}
		
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制 防止死循环
		curl_setopt ( $curl, CURLOPT_HEADER, 1 ); // 显示返回的Header区域内容
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
		                                               // curl_setopt($curl, CURLOPT_COOKIEFILE, 'cookie.txt');
		                                               // curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');
		                                               // curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__).'/cookie.txt');
		                                               // curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__).'/cookie.txt');
		if ($cookie) {
			curl_setopt ( $curl, CURLOPT_COOKIE, $cookie );
		}
		$tmpInfo = curl_exec ( $curl ); // 执行操作
		if (curl_errno ( $curl )) {
			echo 'Errno' . curl_error ( $curl ); // 捕抓异常
			return;
		}
		curl_close ( $curl ); // 关闭CURL会话
		                   // 解析HTTP数据流
		list ( $header, $body ) = explode ( "\r\n\r\n", $tmpInfo );
		if (! $cookie) {
			// 解析COOKIE
			$cookie = "";
			preg_match_all ( "/set\-cookie: (.*)/i", $header, $matches );
			if (count ( $matches ) == 2) {
				foreach ( $matches [1] as $each ) {
					$cookie .= trim ( $each ) . ";";
				}
			}
		}
		return array (
				"cookie" => $cookie,
				"body" => trim ( $body ) 
		);
	}
	
	// post方式调用curl
	function submit($url, $data, $cookie = false) {
		return $this->exec ( $url, $data, $cookie );
	}
	
	// get方式调用curl
	function get($url, $cookie) {
		return $this->exec ( $url, '', $cookie, false );
	}
}
// $user = "gh_2f8359070be6";
// 微信公众号登陆密码 明文
// $pass = 'qiai666';
// $weixin = new sendWeixinMsg($user, $pass);
// $fakeid_arr = $weixin->getFakeid(500); //获取fakeid
// $res = $weixin->send('otk5KtwY_bevCed7GnqxNUdOUeNk', '你好！'); //根据fakeid发送消息
/*
 * $res = $weixin->getLatestMsgs(0); //获取最新消息 $res = $weixin->getUserInfo($fakeid); //根据fakeid获取用户信息 $res = $weixin->getGroups(); //获取分组信息 $res = $weixin->getBossInfo();//获取公众号自己的信息
 */

?>