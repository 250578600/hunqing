<?php
class koko {
	public static $obj = array ();
	public static function getObj($key) {
		$t = C ( 'KokoTools' );
		if (isset ( $t [$key] )) {
			if (! isset ( self::$obj [$key] )) {
				self::$obj [$key] = new $t [$key] ();
			}
			return self::$obj [$key];
		} else {
			return null;
		}
	}
	public static function getKeys($array, $key = 'id') {
		$keys = array ();
		foreach ( $array as $v ) {
			$keys [] = $v [$key];
		}
		return $keys;
	}
	public static function turnIdToKey($data, $id = "id") {
		$ret = array ();
		if ($data) {
			foreach ( $data as $v )
				if (isset ( $v [$id] )) {
					$ret [$v [$id]] = $v;
				}
		}
		return $ret;
	}
	public static function checkDevice() {
		$ua = strtolower ( $_SERVER ['HTTP_USER_AGENT'] );
		if (strpos ( $ua, "micromessenger" ) !== false)
			return "wx";
		if (preg_match ( "/phone|android|mobile|nokia|pad/", $ua ))
			return "m";
		return "pc";
	}
	public static function createSn() {
		return date ( "YmdHis" ) . substr ( md5 ( get_client_ip () ), 0, 5 ) . rand ( 1000, 9999 );
	}
	public static function arraySum($array, $key) {
		$ret = 0;
		foreach ( $array as $v ) {
			$ret += $v [$key];
		}
		return $ret;
	}
	public static function jsonOut($state = 1, $msg = '', $exit = false, $url = '') {
		static $save = null;
		static $outNum = 0;
		if ($state === 'save') {
			$save = array ();
			return;
		} elseif ($state === 'get') {
			return $save;
		} elseif ($state === 'out') {
			header ( 'Content-Type:application/json; charset=utf-8' );
			echo json_encode ( $save, JSON_UNESCAPED_UNICODE );
			return;
		} elseif ($state === 'clear') {
			$save = null;
			return;
		}
		if ($state == 0 && $msg == '') {
			$msg = "操作失败";
		} else if ($state == 1 && $msg == '') {
			$msg = "操作成功";
		}
		$data = array (
				"state" => $state,
				"msg" => $msg,
				"url" => $url 
		);
		if (is_array ( $save )) {
			if (empty ( $save )) {
				$save = $data;
			} else {
				$save ['path'] [] = $data;
			}
			return $data;
		} else if ($outNum == 0) {
			header ( 'Content-Type:application/json; charset=utf-8' );
			$outNum ++;
			echo json_encode ( $data, JSON_UNESCAPED_UNICODE );
			if ($exit) {
				exit ();
			}
		}
	}
	public static function arrayTranspose($data) {
		$out = array ();
		foreach ( $data as $k => $v ) {
			foreach ( $v as $k2 => $v2 ) {
				$out [$k2] [$k] = $v2;
			}
		}
		return $out;
	}
	public static function curPageURL() {
		$pageURL = 'http';
		
		if ($_SERVER ["HTTPS"] == "on") {
			$pageURL .= "s";
		}
		$pageURL .= "://";
		
		if ($_SERVER ["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"] . $_SERVER ["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER ["SERVER_NAME"] . $_SERVER ["REQUEST_URI"];
		}
		return $pageURL;
	}
	public static function header($url = "/") {
		header ( "location:" . $url );
		exit ();
	}
	public static function binary($v) {
		$v = str_split ( $v );
		$i = 0;
		foreach ( $v as $j ) {
			$i = ($i << 1) + intval ( $j );
		}
		return $i;
	}
	public static function ya($data) {
		$data = str_split ( bin2hex ( $data ) );
		$len = count ( $data );
		if ($len % 2 == 1)
			$len --;
		for($i = 0; $i < $len; $i += 2) {
			$tmp = $data [$i];
			$data [$i] = $data [$i + 1];
			$data [$i + 1] = $tmp;
		}
		$data = join ( $data );
		return $data;
	}
	public static function jie($data) {
		$data = str_split ( $data );
		$len = count ( $data );
		if ($len % 2 == 1)
			$len --;
		for($i = 0; $i < $len; $i += 2) {
			$tmp = $data [$i];
			$data [$i] = $data [$i + 1];
			$data [$i + 1] = $tmp;
		}
		$data = join ( $data );
		return pack ( "H*", $data );
	}
	// 对称加密函数
	public static function encrypt($plainText, $secretKey = null) {
		if ($secretKey == null) {
			$secretKey = C ( 'secretKey' ) ?  : 'dajdchasjcasuid';
		}
		$secretKey = pack ( 'H*', md5 ( $secretKey ) );
		$iv_size = mcrypt_get_iv_size ( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC );
		$iv = mcrypt_create_iv ( $iv_size, MCRYPT_RAND );
		$cipherText = mcrypt_encrypt ( MCRYPT_RIJNDAEL_128, $secretKey, $plainText, MCRYPT_MODE_CBC, $iv );
		$cipherText = $iv . $cipherText;
		return base64_encode ( $cipherText );
	}
	// 对称解密函数
	public static function decrypt($cipherText, $secretKey = null) {
		if ($secretKey == null) {
			$secretKey = C ( 'secretKey' ) ?  : 'dajdchasjcasuid';
		}
		$secretKey = pack ( 'H*', md5 ( $secretKey ) );
		$iv_size = mcrypt_get_iv_size ( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC );
		$cipherText = base64_decode ( $cipherText );
		$iv = substr ( $cipherText, 0, $iv_size );
		$cipherText = substr ( $cipherText, $iv_size );
		$plainText = mcrypt_decrypt ( MCRYPT_RIJNDAEL_128, $secretKey, $cipherText, MCRYPT_MODE_CBC, $iv );
		return rtrim ( $plainText, "\0" );
	}
	public static function setting($name = null) {
		$n = explode ( "/", $name );
		$name = $n [0];
		$setting = S ( 'setting' );
		if ($setting == null) {
			$setting = D ( 'Setting' )->get ();
			S ( 'setting', $setting );
		}
		if ($name == null) {
			return $setting;
		}
		if (isset ( $setting [$name] ) && $setting [$name] !== null) {
			$ret = $setting [$name];
		} else {
			$r = C ( $name );
			if ($r !== null) {
				$ret = $r;
			} else {
				$ret = null;
			}
		}
		if ($ret !== null && isset ( $n [1] )) {
			switch (strtolower ( $n [1] )) {
				case 'a' : // 数组
					$ret = ( array ) $ret;
					break;
				case 'd' : // 数字
					$ret = ( int ) $ret;
					break;
				case 'f' : // 浮点
					$ret = ( float ) $ret;
					break;
				case 'b' : // 布尔
					$ret = ( boolean ) $ret;
					break;
				case 's' : // 字符串
				default :
					$ret = ( string ) $ret;
			}
		}
		return $ret;
	}
	public static function splitArray($array, $num) {
		$out = array ();
		$i = 0;
		$o = array ();
		foreach ( $array as $k => $v ) {
			$o [$k] = $v;
			if (++ $i == $num) {
				$out [] = $o;
				$i = 0;
				$o = array ();
			}
		}
		if (! empty ( $o )) {
			$out [] = $o;
		}
		return $out;
	}
	public static function number() {
		$str = array (
				'零',
				'一',
				'二',
				'三',
				'四',
				'五',
				'六',
				'七',
				'八',
				'九',
				'十' 
		);
	}
	public static function getVerification() {
		$config = array (
				'imageW' => 150,
				'imageH' => 30,
				'fontSize' => 16, // 验证码字体大小
				'length' => 4, // 验证码位数
				'useNoise' => false, // 关闭验证码杂点
				'useCurve' => false 
		); //
		
		ob_clean ();
		$Verify = new \Think\Verify ( $config );
		$Verify->entry ();
	}
	public static function checkVerify($code) {
		$verify = new \Think\Verify ( array (
				'reset' => false 
		) );
		return $verify->check ( $code );
	}
	public static function sendEmail($email, $msg) {
		vendor ( "koko.email.email" );
		$m = new \email ();
		return $m->send ( $email, $msg );
	}
	public static function sendSms($telephone, $msg) {
		$msg = rawurlencode ( mb_convert_encoding ( $msg, "gb2312", "utf-8" ) );
		$url = "http://yzm.mb345.com/ws/BatchSend2.aspx?CorpID=CDLK00073&Pwd=ss1103@&Mobile={$telephone}&Content=" . $msg . "&SendTime=&cell=";
		// $url = "http://120.55.197.77:1210/Services/MsgSend.asmx/SendMsg?userCode=私颜&userPass=SIYAN123zh&DesNo={$telephone}&Msg={$msg}&Channel=1";
		return file_get_contents ( $url );
	}
	public static function sendTelephoneCode($telephone, $code = "") {
		if ($code == '') {
			$code = rand ( 100000, 999999 );
		}
		session ( "telephone_code", $code );
		$msg = "您的验证码为{$code}，有效时间15分钟【蜂鸟智库】";
		self::sendSms ( $telephone, $msg );
		return $code;
	}
	public static function checkTelephoneCode($code) {
		return $code == session ( "telephone_code" );
	}
	public static function checkTelephone($tel) {
		return preg_match ( "/^1[3-9][0-9]{9}$/", $tel );
	}
	public static function checkEmail($email) {
		return preg_match ( "/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/", $email );
	}
	public static function getNames($data) {
		$out = array ();
		foreach ( $data as $v ) {
			$out [$v [0]] = $v [1];
		}
		return $out;
	}
	public static function cutPic($background, $qrcode, $qr_w, $x, $y, $text = null, $font_file = null) {
		$debug = 0;
		if (! is_dir ( 'Public' )) {
			mkdir ( 'Public' );
		}
		if (! is_dir ( "Public/QR" )) {
			mkdir ( "Public/QR" );
		}
		if (! is_dir ( "Public/tmp" )) {
			mkdir ( "Public/tmp" );
		}
		$file = "./Public/QR/" . md5 ( $background . $qrcode ) . ".jpg";
		if (! file_exists ( $file ) || $debug) {
			if (substr ( $background, 0, 5 ) == 'http:' || substr ( $background, 0, 6 ) == 'https:') {
				$hz = explode ( ".", substr ( $background, - 6 ) );
				if (isset ( $hz [1] )) {
					$hz = '.' . $hz ( count ( $hz ) - 1 );
				} else {
					$hz = '';
				}
				$f = "./Public/tmp/" . md5 ( $background ) . $hz;
				file_put_contents ( $f, file_get_contents ( $background ) );
				$background = $f;
			}
			if (substr ( $qrcode, 0, 5 ) == 'http:' || substr ( $qrcode, 0, 6 ) == 'https:') {
				$hz = explode ( ".", substr ( $qrcode, - 6 ) );
				if (isset ( $hz [1] )) {
					$hz = '.' . $hz ( count ( $hz ) - 1 );
				} else {
					$hz = '';
				}
				$f = "./Public/tmp/" . md5 ( $qrcode ) . $hz;
				file_put_contents ( $f, file_get_contents ( $qrcode ) );
				$qrcode = $f;
			}
			if (substr ( $background, - 3 ) == 'png') {
				$back = imagecreatefrompng ( $background );
			} else {
				$back = imagecreatefromjpeg ( $background );
			}
			if (substr ( $qrcode, - 3 ) == 'png') {
				$src = imagecreatefrompng ( $qrcode );
			} else {
				$src = imagecreatefromjpeg ( $qrcode );
			}
			$size_back = getimagesize ( $background );
			$size_src = getimagesize ( $qrcode );
			if (is_array ( $text )) {
				ImageTTFText ( $back, $text ['size'], $text ['angle'], $text ['x'], $text ['y'], $text ['color'], $font_file, $text ['text'] );
			}
			if ($x ===null) {
				$x = ($size_back [0] - $qr_w) / 2;
			}
			imagecopyresampled ( $back, $src, $x, $y, 0, 0, $qr_w, $qr_w, $size_src [0], $size_src [1] );
			imagejpeg ( $back, $file );
			imagedestroy ( $back );
		}
		return $file;
	}
}
function json($value) {
	return json_encode ( $value, JSON_UNESCAPED_UNICODE );
}
function dbSaved($name, $db = null) {
	static $saved = array ();
	if ($name == null) {
		return $saved;
	}
	if ($db == null) {
		return isset ( $saved [$name] ) ? $saved [$name] : null;
	}
	$saved [$name] = $db;
}