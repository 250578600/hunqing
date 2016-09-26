<?php

namespace Home\Controller;

class UploadController {
	private function run($v) {
		$data = array (
				"name" => $v ['name'],
				"size" => $v ['size'] 
		);
		$fn = $v ['name'];
		$fn_ = explode ( ".", $fn );
		for($i = count ( $fn_ ) - 1; $i >= 0; $i --)
			if ($fn_ [$i]) {
				$hz = $fn_ [$i];
				break;
			}
		if (! file_exists ( "Public/images" )) {
			mkdir ( "Public/images" );
		}
		if ($hz != 'png' && $hz != 'jpg' && $hz != 'jpeg' && $hz != 'bmp') {
			$fn = time () . rand ( 1, 100000 ) . ".$hz";
			$src = "Public/images/" . $fn;
			$ret = move_uploaded_file ( $v ['tmp_name'], $src );
		} else {
			$fn = time () . rand ( 1, 100000 ) . ".jpg";
			$src = "Public/images/" . $fn;
			$ret = $this->save ( $v ['tmp_name'], $src, $hz );
		}
		return array (
				"state" => $ret ? "SUCCESS" : "FAILD",
				"url" => "/" . $src,
				"title" => $fn,
				"original" => $data ['name'],
				"type" => $hz,
				"size" => $data ['size'] 
		);
	}
	public function index() {
		foreach ( $_FILES as $k => $v ) {
			if (is_string ( $v ['name'] )) {
				exit ( json_encode ( $this->run ( $v ) ) );
			} else {
				$out = array ();
				$v = \koko::arrayTranspose ( $v );
				foreach ( $v as $k2 => $v2 ) {
					$out [] = $this->run ( $v2 );
				}
				exit ( json_encode ( $out ) );
			}
		}
	}
	public function save($from, $to, $type) {
		switch (strtolower ( $type )) {
			case 'png' :
				$img = imagecreatefrompng ( $from );
				break;
			case 'jpg' :
			case 'jpeg' :
				$img = imagecreatefromjpeg ( $from );
				break;
			case 'bmp' :
				$img = imagecreatefromwbmp ( $from );
				break;
		}
		return imagejpeg ( $img, $to );
	}
	public function getWx() {
		$wx = \koko::getObj('wx');
		switch ($_POST ['action']) {
			case 'init' :
				echo json_encode ( $wx->wxJsapiPackage ( $_POST ['url'] ) );
				break;
			case 'get' :
				$ret = $wx->wxDownload ( $_POST ['serverId'] );
				if ($ret == false) {
					echo json_encode ( array (
							'state' => 'ERROR' 
					) );
				} else {
					echo json_encode ( array (
							'state' => 'SUCCESS',
							'url' => $ret 
					) );
				}
				break;
		}
	}
}