<?php

namespace Admin\Controller;

use Think\Controller;

class ServerController extends Controller {
	public $wx = null;
	public function _initialize() {
		$this->wx = \koko::getObj('wx');
	}
	public function index() {
		print_r($this->wx->wxGetUser ( 'omxgPvyXJRO0ueh_Gaa_vxlpEeJ8' ));
	}
	public function call() {
		$data = $GLOBALS ["HTTP_RAW_POST_DATA"];
		file_put_contents ( 'out.txt', $data ,FILE_APPEND); 
		if (empty ( $data )) {
			echo $_GET ['echostr'];
		} else {
			$data = ( array ) simplexml_load_string ( $data, 'SimpleXMLElement', LIBXML_NOCDATA );
			$m = new \Home\Model\UserModel ( '', '', '', array (
					'weixinLoginNo' => true 
			) );
			switch ($data ['MsgType']) {
				case 'event' :
					switch ($data ['Event']) {
						case 'CLICK' :
							switch ($data ['EventKey']) {
								case '' :
							}
							break;
						case 'subscribe' :
							$user = $this->wx->wxGetUser ( $data ['FromUserName'] );
							file_put_contents ( "user.txt", var_export ( $user, true ) );
							if ($data ['EventKey']) {
								$shang = explode ( '_', $data ['EventKey'] );
								$shang = is_numeric ( $shang [1] ) ? $shang [1] : null;
							} else {
								$shang = null;
							}
							$m->loginWeixin2 ( $user, $shang );
							if ($shang) {
								$m->where ( "openid='{$data ['FromUserName']}'" )->setField ( "groupid", 2 );
							}
							file_put_contents ( "123.txt", var_export ( $data, true ) );
							echo $this->transmitText ( $data, "私颜高品质，生活更甜蜜！感谢关注私颜！欢迎走进私颜！详情咨询13980056239" );
							break;
						case 'unsubscribe' :
							$m->where ( "openid='{$data ['FromUserName']}'" )->setField('subscribe',0);
					//		$m->where ( "openid='{$data ['FromUserName']}'" )->delete();
							break;
						case 'SCAN1' :
							if(is_numeric($data ['EventKey'])){
								$u=$m->where ( "openid='{$data ['FromUserName']}'" )->find();
								$shang=$m->where("id=".$data ['EventKey'])->find();
								if(!$u['shang0']&&$shang['groupid']==3){
									file_put_contents ( "user.txt", var_export ( $data, true ) );
									$m->where("id=".$u ['id'])->save(array(
										"shang0"=>$shang['id'],
										"shang1"=>$shang['shang0'],
										"shang2"=>$shang['shang1'],
									));
									$m->where("id=".$shang['id'])->setInc('xia0');
									$m->where("id=".$shang['shang0'])->setInc('xia1');
									$m->where("id=".$shang['shang1'])->setInc('xia2');
									echo $this->transmitText ( $data, "您已经成功绑定为{$shang['nickname']}的下线" );
								}
							}
							break;
						case 'LOCATION' :
							$uid = $m->where ( "openid = '{$data['FromUserName']}'" )->getField ( 'id' );
							if ($uid) {
								$m->setLocation ( $data ['Latitude'], $data ['Longitude'], $uid );
							}
							break;
					}
					break;
				case 'text' :
					echo " <xml>
								<ToUserName><![CDATA[{$data['FromUserName']}]]></ToUserName>
								<FromUserName><![CDATA[{$data['ToUserName']}]]></FromUserName>
								<CreateTime>" . NOW_TIME . "</CreateTime>
								<MsgType><![CDATA[transfer_customer_service]]></MsgType>
							</xml>";
			}
		}
	}
	public function menu() {
		$at = $this->wx->wxAccessToken ();
		$data = array (
				"button" => array (
						array (
								"type" => 'view',
								'name' => "game",
								"url" => "http://test13.fengniaozhiku.com/" 
						) 
				) 
		);
		print_r ( $data );
		// $data = stripslashes ( json_encode ( $data, JSON_UNESCAPED_UNICODE ) );
		$data = stripslashes ( json_encode ( $data ) );
		$data = str_replace ( "game", "商城", $data );
		echo $data;
		$ret = $this->wx->wxMenuCreate ( $data );
		// $ret=$wx->wxMenuGet();
		print_r ( $ret );
	}
	public function a() {
		$a = new \Common\Model\FreightModel ();
		echo $a->get ( 67 );
	}
	private function transmitText($object, $content, $flag = 0) {
		$textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
		$resultStr = sprintf ( $textTpl, $object ['FromUserName'], $object ['ToUserName'], time (), $content, $flag );
		return $resultStr;
	}
	private function transmitImg($object, $MediaId) {
		$textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
					<MsgType><![CDATA[image]]></MsgType>
					<Image>
					<MediaId><![CDATA[%s]]></MediaId>
					</Image>
					</xml>";
		$resultStr = sprintf ( $textTpl, $object ['FromUserName'], $object ['ToUserName'], time (), $MediaId );
		return $resultStr;
	}
}