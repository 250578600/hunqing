<?php

namespace Common\Model;

use Common\Model\KokoModel;

class MemberModel extends KokoModel {
	public $logined_id;
	public $logined_key;
	public $data_;
	public $info = array (
			"sex" => array (
					"未知",
					"男",
					"女" 
			) 
	);
	public $wx_key = array (
			"subscribe",
			"nickname",
			"headimgurl",
			"sex" 
	);
	protected $_validate = array (
			array (
					"username",
					"",
					"用户名已注册",
					0,
					"unique" 
			) ,array (
					"telephone",
					"",
					"手机号已注册",
					0,
					"unique" 
			) ,array (
					"email",
					"",
					"邮箱已注册",
					0,
					"unique" 
			) ,
	);
	public function __construct($name = '', $tablePrefix = '', $connection = '', $config = null) {
		$table = array (
				"username varchar(30) unique",
				"telephone varchar(20)",
				"telephone_verified tinyint default 0",
				"email varchar(30) default ''",
				"email_verified tinyint default 0",
				"nickname varchar(30) not null",
				"password varchar(32) not null",
				"headimgurl varchar(200)",
				"name varchar(30) not null",
				"sex tinyint not null default 0 comment '0:未知,1:男,2:女'",
				"create_time int not null",
				"create_ip varchar(20) not null",
				"login_time int not null",
				"login_ip varchar(20) not null",
				"login_times int not null default 0",
				"province smallint",
				"city smallint",
				"county smallint",
				"address varchar(200)",
				"balance double not null default 0",
				"withdraw double not null default 0 comment '已提现金额'",
				"spent double not null default 0 comment '已消费金额'",
				"point int not null default 0 comment '积分'",
				"groupid int not null default 1",
				"freeze tinyint default 0  comment '冻结'",
				"state tinyint default 0  comment '用户状态'",
				"openid varchar(50) unique",
				"subscribe tinyint default 0 comment '0:未关注，1已关注'",
				"qrcode varchar(100) default ''" 
		);
		if (isset ( $config ['userInfo'] ) && $config ['userInfo']) {
			$table = array_merge ( $table, array (
					"age tinyint default 0",
					"height int default 0",
					"weight int default 0",
					"shenfen varchar(20) default ''",
					"shenfen_img varchar(100) default ''",
					"shenfen_imgs varchar(1024) default ''",
					"interest varchar(1024) default ''" 
			) );
		}
		if (isset ( $config ["distribution"] ) && is_array ( $config ["distribution"] )) { // / 分销系统
			if (isset ( $_GET ['shang'] ) && is_numeric ( $_GET ['shang'] )) {
				session ( 'shang', $_GET ['shang'] );
			}
			$arr = array ();
			$table [] = "shang int not null default 0 comment '上级id'";
			$table [] = "shangs varchar(200) not null default '' comment '关系最近的上级id'";
			$table [] = "xia int not null default 0 comment '下级个数'";
		}
		$config ["table"] ["keys"] [] = $table;
		$config ["table"] ["tables"] ['group'] = array (
				"class" => "\Common\Model\GroupModel" 
		);
		$config ["table"] ["tables"] ['message'] = array (
				"class" => "\Common\Model\CommentModel",
				"no_foreign" => true 
		);
		$config ["table"] ["tables"] ['coupon'] = array (
				"class" => "\Common\Model\CouponModel" 
		);
		$this->logined_key = $this->getModelName () . "_logined_id";
		parent::__construct ( $name, $tablePrefix, $connection, $config );
		if (! isset ( $GLOBALS ['no_database'] )) {
			$this->logined ();
		}
	}
	public function loginWeixin() {
		if ($this->checkConfig ( 'weixinLogin' ) == false || session ( "weixin_logined" )) {
			return;
		}
		if (\koko::checkDevice () == 'wx') {
			$user = session ( "wxUserData" );
			if (! $user) {
				$wx = \koko::getObj ( 'wx' );
				if (! $_GET ['code']) {
					$url = $wx->wxOauthUserinfo ( \koko::curPageURL () );
					$wx->wxHeader ( $url );
				} else {
					$data = $wx->wxOauthAccessToken ( $_GET ['code'] );
					$u = $wx->wxGetUser ( $data ['openid'] );
					if ($u ['subscribe'] == 0) {
						$u = $wx->wxOauthUser ( $data ['access_token'], $data ['openid'] );
						$u ['subscribe'] = 0;
					}
					$user = array_merge ( $data, $u );
					session ( "wxUserData", $user );
				}
			}
		} elseif ($this->checkConfig ( 'simulateWeixin' )) {
			$weixinData = '{"access_token":"-fw---Yk5_EY4w","expires_in":7200,"refresh_token":"-fw--","openid":"' . md5 ( "123456789" ) . '","scope":"snsapi_userinfo","subscribe":1,"nickname":"模拟账号","sex":1,"language":"zh_CN","city":"\u6210\u90fd","province":"\u56db\u5ddd","country":"\u4e2d\u56fd","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/dxKEsjQBP4R4les6phia8Nrwpjrb57dpTpIBloT1hzX5xcZx72IsZYg7eRribeo3VegAibjSFjgnIaIkJrqhV6zjDL2iab4VQdcS\/0","subscribe_time":1456111127,"remark":"","groupid":0}';
			$user = json_decode ( $weixinData, true );
		} else {
			return;
		}
		$id = $this->loginWeixin2 ( $user, session ( 'shang' ) );
		if ($this->checkLogined () == false) {
			session ( $this->logined_key, $id );
		}
	}
	public function loginWeixin2($wx_user, $shang = null) {
		if (! isset ( $wx_user ['openid'] )) {
			return null;
		}
		$ret = $this->where ( "openid='{$wx_user['openid']}'" )->find ();
		$data = array (
				'subscribe' => $wx_user ['subscribe'],
				'openid' => $wx_user ['openid'],
				'login_time' => NOW_TIME,
				'login_ip' => get_client_ip () 
		);
		foreach ( $this->wx_key as $k => $v ) {
			$data [$v] = $wx_user [$v];
		}
		$id = null;
		if ($ret) { // 微信有登录过
			if ($this->checkLogined ()) { // 有登录过微信,有登录网站 // 将当前登录账号绑定为当前微信
				if ($this->logined_id == $ret ['id']) {
					// do nothing
				} else { // 当前登录账号和已关联微信不一致
					$this->where ( "id=" . $ret ['id'] )->setField ( "openid", '' );
					$this->where ( "id=" . $this->logined_id )->save ( $data );
				}
				$id = $this->logined_id;
			} else { // 有登录过微信,没有登录网站,这里实现微信自动登录
				$id = $ret ['id'];
				$data ['login_times'] = array (
						"exp",
						"login_times+1" 
				);
				$this->where ( "id=" . $id )->save ( $data );
			}
		} else { // 微信没有登录过
			foreach ( $this->wx_key as $k => $v ) {
				$data [$v] = $wx_user [$v];
			}
			if ($this->checkLogined ()) { // 没有登录微信,但是登录了网站,eg 在浏览器注册,在微信第一次登录就走这条路线
				$data ['login_times'] = array (
						"exp",
						"login_times+1" 
				);
				$this->where ( "id=" . $this->logined_id )->save ( $data );
				if ($ret ['login_time'] < $this->today) {
					$this->tongjiToday ( 'login' );
				}
				$id = $ret ['id'];
			} else { // 没有登录网站
				if ($this->checkConfig ( 'weixinRegister' )) { // /这里是不需要注册直接用微信可以登录
					$data ['create_time'] = NOW_TIME;
					$data ['create_ip'] = get_client_ip ();
					if ($shang && $this->checkConfig ( 'distribution' )) {
						$this->setShangData ( $data, $shang );
					}
					$id = $this->insert ( $data ); // 自动根据微信信息注册用户
				} else {
					// do nothing; 这里是不自动生成用户
				}
			}
		}
		if ($id) {
			// session ( "weixin_logined", 1 );
		}
		return $id;
	}
	public function logined() {
		$id = session ( $this->logined_key );
		if ($id) {
			$this->logined_id = $id;
			$this->data_ = $this->show ( $id );
			if ($this->data_ == null) {
				$this->logout ();
			}
			if ($this->data_ ['city'] && ! cookie ( "user_city" )) {
				cookie ( "user_city", $this->data_ ['city'] );
				$_COOKIE ['user_city'] = $this->data_ ['city'];
			}
		}
	}
	
	/*
	 * config ['distribution']=array( array( 'ratio_balance'=>81, 'ratio_jifen'=>41, ), array( 'ratio_balance'=>31, 'ratio_jifen'=>11, ) ); 这里的id是付款的人的id,获取收益的都是他的上线
	 */
	public function addIncome($money, $id, $distribution = null) {
		if (empty ( $distribution ) && $this->checkConfig ( 'distribution' )) {
			$distribution = $this->config ['distribution'];
		}
		if (! $id) {
			\koko::jsonOut ( 0, "id错误", true );
		}
		if (empty ( $distribution )) {
			\koko::jsonOut ( 0, "distribution错误", true );
		}
		$shangs = $this->where ( "id=" . $id )->getField ( 'shangs' );
		$shangs = empty ( $shangs ) ? array () : explode ( ",", $shangs );
		$sids = array ();
		foreach ( $shangs as $v ) {
			$tmp = explode ( ":", $v );
			$sids [$tmp [0]] = $tmp [1];
		}
		
		foreach ( $distribution as $k => $keys ) {
			$data = array ();
			foreach ( $keys as $k2 => $v2 ) { // 这里是提成
				$data [$k2] = array (
						"exp",
						$k2 . ' + ' . ($money * $v2 / 100) 
				);
			}
			$w = null;
			if ($k == 0) {
				$w = "id=" . $id;
			} else if (isset ( $sids [$k] )) {
				$w = "id=" . $sids [$k];
			}
			if (! empty ( $w )) {
				$this->where ( $w )->save ( $data );
			}
		}
	}
	public function getXia($id = null, $type = false) {
		if ($this->checkConfig ( 'distribution' )) {
			if ($id == null) {
				$id = $this->logined_id;
			}
			if ($id) {
				if (is_numeric ( $type )) {
					$data = $this->where ( "shang" . $type . " = " . $id )->order ( "id desc" )->select ();
					return $data;
				} else {
					$ret = array ();
					$sub = $this->config ['distribution'];
					foreach ( $sub as $k => $v ) {
						if ($type == 'count') {
							$ret [] = $this->where ( "shang" . $k . " = " . $id )->count ();
						} else {
							$ret [] = $this->where ( "shang" . $k . " = " . $id )->order ( "id desc" )->select ();
						}
					}
					return $ret;
				}
			} else {
				throw_exception ( "id 错误" );
			}
		} else {
			throw_exception ( "没有开启下线" );
		}
	}
	private function setShangData(&$data, $shang_id) {
		if ($this->checkConfig ( 'distribution' ) && is_numeric ( $shang_id )) {
			$shang = $this->show ( $shang_id );
			if ($shang) {
				$d = array (
						'1:' . $shang_id 
				);
				$shang_ids = empty ( $shang ['shangs'] ) ? array () : explode ( ",", $shang ['shangs'] );
				foreach ( $shang_ids as $k => $v ) {
					$v = explode ( ":", $v );
					$d [] = ($k + 2) . ':' . $v [1];
					if ($k == 8) {
						break;
					}
				}
				$data ['shang'] = $shang_id;
				$data ['shangs'] = join ( ",", $d );
				$this->where ( 'id=' . $shang_id )->setInc ( 'xia' );
			}
		}
	}
	public function notifyShang($shang, $ji, $nickname) {
	}
	public function insert($data) {
		if (empty ( $data ['groupid'] )) {
			$data ['groupid'] = $this->getDbEx ( 'group' )->order ( "`default` desc,id asc" )->getField ( 'id' ) ?  : 0;
		}
		return parent::insert ( $data );
	}
	public function update($where, $data) {
		if ($where == null) {
			$where = $this->logined_id;
		}
		return parent::update ( $where, $data );
	}
	public function show($id, $config = null) {
		$data = parent::show ( $id );
		if ($this->checkConfig ( "userInfo" )) {
			$data ['shenfen_imgs'] = unserialize ( $data ['shenfen_imgs'] );
		}
		if ($data) {
			$loc = \koko::getObj ( "loc" );
			$data ['province_'] = $loc->getNames ( $data ['province'] );
			$data ['city_'] = $loc->getNames ( $data ['city'] );
			$data ['county_'] = $loc->getNames ( $data ['county'] );
		}
		return $data;
	}
	public function getList($kokoWhere = null, $config = null) {
		$data = parent::getList ( $kokoWhere, $config );
		$userInfo = $this->checkConfig ( "userInfo" );
		$loc = \koko::getObj ( "loc" );
		foreach ( $data ['list'] as &$v ) {
			if ($userInfo) {
				$v ['shenfen_imgs'] = unserialize ( $v ['shenfen_imgs'] );
			}
			$v ['province_'] = $loc->getNames ( $v ['province'] );
			$v ['city_'] = $loc->getNames ( $v ['city'] );
			$v ['county_'] = $loc->getNames ( $v ['county'] );
		}
		return $data;
	}
	public function getMyQR($id = null) {
		if ($id == null) {
			if ($this->checkLogined ()) {
				$id = $this->logined_id;
			} else {
				return 'not login';
			}
		}
		if ($id != $this->logined_id) {
			$openid = $this->where ( "id=$id" )->getField ( 'openid' );
		} else {
			$openid = $this->data_ ['openid'];
		}
		$fileName = "Public/QRcode/" . $openid . '.png';
		if (file_exists ( "Public/QRcode/" ) == false) {
			mkdir ( "Public/QRcode/" );
		}
		if (file_exists ( $fileName ) && $this->data_ ['qrcode']) {
			return $fileName;
		}
		$wx = \koko::getObj ( 'wx' );
		$data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "' . $id . '"}}}';
		$ret = $wx->wxQrCodeTicket ( $data );
		file_put_contents ( 'test.txt', $ret );
		$json = json_decode ( $ret, true );
		$this->where ( 'id=' . $id )->setField ( 'qrcode', $json ['url'] );
		// 纠错级别：L、M、Q、H
		$level = 'L';
		// 点的大小：1到10,用于手机端4就可以了
		$size = 8;
		// 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
		
		// 生成的文件名
		vendor ( "koko.kokoClass.weixin.api.phpqrcode.phpqrcode" );
		\QRcode::png ( $json ['url'], $fileName, $level, $size, 1 );
		
		// 是否上传到微信服务器
		$file_info = array (
				'filename' => '/' . $fileName, // 国片相对于网站根目录的路径
				'content-type' => 'image/png', // 文件类型
				'filelength' => filesize ( $fileName ) 
		); // 图文大小
		   
		// $media_id = $wx->add_material ( $file_info );
		
		return $fileName;
	}
	public function checkLogined() {
		return is_numeric ( $this->logined_id );
	}
	public function checkPassword($pwd) {
		if ($this->checkLogined ())
			return $this->data_ ['password'] == self::pwdJiami ( $pwd );
		else
			return false;
	}
	public function setPassword($pwd, $where = null) {
		if (is_numeric ( $where )) {
			$where = "id = " . $where;
		} elseif ($where == null) {
			if ($this->checkLogined ()) {
				$where = "id=" . $this->logined_id;
			} else {
				exit ( "设置密码条件错误" );
			}
		}
		return $this->where ( $where )->save ( array (
				"password" => self::pwdJiami ( $pwd ) 
		) );
	}
	public function login($data) {
		if ($this->checkConfig ( 'checkLogin.code' )) {
			if (! \koko::checkVerify ( $data ["code"] )) {
				\koko::jsonOut ( 0, "验证码错误", true );
			}
		}
		$w = array ();
		if ($this->checkConfig ( "loginType" )) {
			$isTel = \koko::checkTelephone ( $data ['key'] );
			$isEmail = \koko::checkEmail ( $data ['key'] );
			foreach ( $this->config ['loginType'] as $v ) {
				if (($isTel && $v == 'telephone') || ($isEmail && $v == 'email') || (is_numeric ( $data ['key'] ) && $v == 'id') || (! $isTel && ! $isEmail && $v == 'username')) {
					$w [$v] = $data ['key'];
				}
			}
			$w ['_logic'] = 'or';
		} else {
			\koko::jsonOut ( 0, "系统暂时不支持登录", true );
		}
		$ret = $this->where ( $w )->find ();
		if ($ret) {
			if ($ret ['password'] == self::pwdJiami ( $data ['password'] )) {
				session ( $this->logined_key, $ret ['id'] );
				$this->logined_id = $ret ['id'];
				$today = strtotime ( date ( 'Y-m-d' ) );
				if (NOW_TIME - $ret ['login_time'] > 3600 * 24 || $ret ['login_time'] < $today) {
					$this->tongjiToday ( 'login' );
				}
				$d = array (
						'login_time' => NOW_TIME,
						'login_ip' => get_client_ip (),
						'login_times' => array (
								'exp',
								"login_times+1" 
						) 
				);
				$this->where ( "id=" . $ret ['id'] )->save ( $d );
				$this->logined ();
				return true;
			} else {
				\koko::jsonOut ( 0, "密码错误", true );
			}
		} else {
			\koko::jsonOut ( 0, "该账号不存在", true );
		}
	}
	public function logout() {
		if (\koko::checkDevice () == 'wx') {
			$this->where ( "id=" . $this->logined_id )->setField ( "openid", '' );
		}
		$this->logined_id = null;
		return session ( $this->logined_key, null );
	}
	public function register($data, $login = false) {
		if ($this->checkConfig ( 'checkReg.telephone_code' )) {
			if (! \koko::checkTelephoneCode ( $data ["telephone_code"] )) {
				\koko::jsonOut ( 0, "手机验证码错误", true );
			}
		}
		if ($this->checkConfig ( 'checkReg.code' )) {
			if (! \koko::checkVerify ( $data ["code"] )) {
				\koko::jsonOut ( 0, "验证码错误", true );
			}
		}
		if (strlen ( $data ['password'] ) < 6) {
			\koko::jsonOut ( 0, "密码太短", true );
		}
		$ip = get_client_ip ();
		$data ['password'] = $this->pwdJiami ( $data ['password'] );
		$data ['create_time'] = NOW_TIME;
		$data ['create_ip'] = $ip;
		if ($login) { // 注册的时候就登陆了
			$data ['login_time'] = $data ['create_time'];
			$data ['login_ip'] = $ip;
			$data ['login_times'] = 1;
		}
		if (! isset ( $data ['nickname'] )) {
			$data ['nickname'] = $data ['username'] ?  : $data ['telephone'];
		}
		$ret = $this->insert ( $data );
		if ($ret) {
			if ($login) {
				$this->tongjiToday ( 'login' );
				session ( $this->logined_key, $ret );
			}
			$this->tongjiToday ( 'register' );
			\koko::jsonOut ( 1, "注册成功", false, U ( 'Account/login' ) );
		} else {
			\koko::jsonOut ( 0, "注册失败" );
		}
	}
	public static function pwdJiami($str) {
		$salt = "kokokoko";
		return md5 ( $salt . md5 ( $salt . $str ) );
	}
	public function setShang($id, $shang_id) {
		if ($this->checkConfig ( 'distribution' ) && is_numeric ( $shang_id )) {
			$shang = $this->find ( $shang_id );
			$data = array ();
			$sub = $this->config ['distribution'];
			$old = $this->find ( $id );
			$len = count ( $sub );
			foreach ( $sub as $k => $v ) {
				if ($k == 0) {
					$data ['shang' . $k] = $shang_id;
					$this->where ( "id = $shang_id" )->setInc ( "xia" . $k );
				} else {
					$data ['shang' . $k] = $shang ? $shang ['shang' . ($k - 1)] : 0;
					$this->where ( "id = " . $data ['shang' . $k] )->setInc ( "xia" . $k );
				}
				if ($k < $len - 1) {
					$this->where ( "shang{$k} = " . $id )->setField ( "shang" . ($k + 1), $shang_id );
				}
				$this->where ( "id = " . $old ['shang' . $k] )->setDec ( "xia" . $k );
				$this->notifyShang ( $data ['shang' . $k], $k + 1, $data ['nickname'] );
			}
			return $this->where ( "id = $id" )->save ( $data );
		}
	}
	public function setLocation($latitude = null, $longitude = null, $user_id = null) {
		$latitude = $latitude ?  : I ( "post.latitude" );
		$longitude = $longitude ?  : I ( "post.longitude" );
		$data = file_get_contents ( "http://api.map.baidu.com/geocoder/v2/?ak=6lE76fTyh3gHvIKG8QNrdSrO&callback=renderReverse&location={$latitude},{$longitude}&output=json&pois=1" );
		$d = substr ( $data, 29 );
		$d = substr ( $d, 0, strlen ( $d ) - 1 );
		$d = json_decode ( $d, true );
		$province = $d ['result'] ['addressComponent'] ['province'];
		$city = $d ['result'] ['addressComponent'] ['city'];
		$county = $d ['result'] ['addressComponent'] ['district'];
		$loc = new data\location ();
		$data = array ();
		$data ['city'] = $loc->getIds ( $city );
		$data ['county'] = $loc->getIds ( $county );
		$data ['province'] = $loc->getParents ( $data ['city'] );
		return $this->update ( $user_id, $data );
	}
	public function getGroupName() {
		return $this->getDbEx ( 'group' )->getField ( "id,name" );
	}
	public function getGroupPower($groupid = null) {
		if ($groupid == null) {
			$groupid = $this->data_ ['groupid'];
		}
		$power = $this->getDbEx ( 'group' )->show ( $groupid, array (
				"power" => true 
		) );
		return $power;
	}
}