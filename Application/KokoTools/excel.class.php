<?php

namespace KokoTools;

class excel {
	public $koko = null;
	public function __construct() {
	}
	public function export1($filename, $title, $data) {
		$arr = array ();
		$line = 0;
		$lines = array ();
		foreach ( $data as $l => $lv ) {
			$c = 0;
			$lines [] = $line;
			foreach ( $lv as $lcol => $col ) {
				if (! is_array ( $col )) {
					$arr [$line] [$c ++] = $col;
				} else {
					foreach ( $col as $kr => $r ) {
						foreach ( $r as $z => $zv ) {
							$arr [$line] [$c + $z] = $zv;
						}
						$line ++;
					}
					$line --;
				}
			}
			
			
			$line ++;
		}
		print_r ( $lines );
		print_r ( $arr );
	}
	public function export($filename, $title, $data) {
		if (! $filename) {
			$filename = date ( 'YmdHis' );
		}
		set_time_limit ( 0 );
		ini_set ( "memory_limit", "-1" );
		vendor ( "PHPExcel.PHPExcel" );
		$x = new \PHPExcel ();
		header ( "Pragma: public" );
		header ( "Expires: 0" );
		header ( "Cache-Control:must-revalidate, post-check=0, pre-check=0" );
		header ( "Content-Type:application/force-download" );
		header ( "Content-Type:application/vnd.ms-execl" );
		header ( "Content-Type:application/octet-stream" );
		header ( "Content-Type:application/download" );
		header ( "Content-Disposition:attachment;filename=$filename.xls" );
		header ( "Content-Transfer-Encoding:binary" );
		$x->getActiveSheet ()->setCellValue ( 'A' . 1, 'hahhas' );
		$cellName = explode ( ",", 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,AA,AB,AC,AD,AE,AF,AG,AH,AI,AJ,AK,AL,AM,AN,AO,AP,AQ,AR,AS,AT,AU,AV,AW,AX,AY,AZ' );
		foreach ( $title as $k => $v ) {
			$x->getActiveSheet ()->setCellValueExplicit ( $cellName [$k] . "1", $v, \PHPExcel_Cell_DataType::TYPE_STRING );
			$x->getActiveSheet ()->getColumnDimension ( $cellName [$k] )->setWidth ( 30 );
			$x->getActiveSheet ()->getStyle ( $cellName [$k] . "1" )->getFont ()->setSize ( 15 );
			$x->getActiveSheet ()->getStyle ( $cellName [$k] . "1" )->getFont ()->setBold ( true );
			$x->getActiveSheet ()->getStyle ( $cellName [$k] . "1" )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		}
		foreach ( $data as $i => $v ) {
			$j = 0;
			foreach ( $v as $jj => $d ) {
				if (substr ( $d, 0, 1 ) == '@') {
					$d = json_decode ( substr ( $d, 1 ), true );
				}
				if (is_array ( $d )) {
					if (isset ( $d ['src'] )) {
						$src = $this->getImg ( $d ['src'] );
						if ($src) {
							$objDrawing = new \PHPExcel_Worksheet_Drawing ();
							$objDrawing->setName ( 'Photo' );
							$objDrawing->setDescription ( 'Photo' );
							$objDrawing->setPath ( $src );
							$objDrawing->setWidth ( isset ( $d ['width'] ) ? $d ['width'] : 150 );
							$objDrawing->setHeight ( isset ( $d ['height'] ) ? $d ['height'] : 120 );
							$objDrawing->setCoordinates ( $cellName [$j] . ($i + 2) );
							$objDrawing->setWorksheet ( $x->getActiveSheet () );
						}
					}
					if (isset ( $d ['merge'] ) && is_array ( $d ['merge'] )) {
						$x->getActiveSheet ()->mergeCells ( $cellName [$j] . ($i + 2) . ':' . $cellName [$j + $d ['merge'] [0]] . ($i + 2 + $d ['merge'] [1]) );
					}
					if (isset ( $d ['value'] )) {
						$x->getActiveSheet ()->setCellValueExplicit ( $cellName [$j] . ($i + 2), $d ['value'], is_int ( $d ['value'] ) ? TYPE_NUMERIC\PHPExcel_Cell_DataType::TYPE_NUMERIC : TYPE_NUMERIC\PHPExcel_Cell_DataType::TYPE_STRING );
					}
				} else {
					$x->getActiveSheet ()->setCellValueExplicit ( $cellName [$j] . ($i + 2), $d, \PHPExcel_Cell_DataType::TYPE_STRING );
				}
				$j ++;
			}
		}
		$objWriter = \PHPExcel_IOFactory::createWriter ( $x, 'Excel5' );
		$objWriter->save ( 'php://output' );
	}
	public function export2($filename, $title, $data) {
		if (! $filename) {
			$filename = date ( 'YmdHis' );
		}
		set_time_limit ( 0 );
		ini_set ( "memory_limit", "-1" );
		vendor ( "PHPExcel.PHPExcel" );
		$x = new \PHPExcel ();
		header ( "Pragma: public" );
		header ( "Expires: 0" );
		header ( "Cache-Control:must-revalidate, post-check=0, pre-check=0" );
		header ( "Content-Type:application/force-download" );
		header ( "Content-Type:application/vnd.ms-execl" );
		header ( "Content-Type:application/octet-stream" );
		header ( "Content-Type:application/download" );
		header ( "Content-Disposition:attachment;filename=$filename.xls" );
		header ( "Content-Transfer-Encoding:binary" );
		$x->getActiveSheet ()->setCellValue ( 'A' . 1, 'hahhas' );
		$cellName = explode ( ",", 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,AA,AB,AC,AD,AE,AF,AG,AH,AI,AJ,AK,AL,AM,AN,AO,AP,AQ,AR,AS,AT,AU,AV,AW,AX,AY,AZ' );
		foreach ( $title as $k => $v ) {
			$x->getActiveSheet ()->setCellValueExplicit ( $cellName [$k] . "1", $v, \PHPExcel_Cell_DataType::TYPE_STRING );
			$x->getActiveSheet ()->getColumnDimension ( $cellName [$k] )->setWidth ( 30 );
			$x->getActiveSheet ()->getStyle ( $cellName [$k] . "1" )->getFont ()->setSize ( 15 );
			$x->getActiveSheet ()->getStyle ( $cellName [$k] . "1" )->getFont ()->setBold ( true );
			$x->getActiveSheet ()->getStyle ( $cellName [$k] . "1" )->getAlignment ()->setHorizontal ( \PHPExcel_Style_Alignment::HORIZONTAL_CENTER );
		}
		foreach ( $data as $i => $v ) {
			$j = 0;
			foreach ( $v as $jj => $d ) {
				if (is_array ( $d )) {
					if (isset ( $d ['src'] )) {
						$src = $this->getImg ( $d ['src'] );
						if ($src) {
							$objDrawing = new \PHPExcel_Worksheet_Drawing ();
							$objDrawing->setName ( 'Photo' );
							$objDrawing->setDescription ( 'Photo' );
							$objDrawing->setPath ( $src );
							$objDrawing->setWidth ( isset ( $d ['width'] ) ? $d ['width'] : 150 );
							$objDrawing->setHeight ( isset ( $d ['height'] ) ? $d ['height'] : 120 );
							$objDrawing->setCoordinates ( $cellName [$j] . ($i + 2) );
							$objDrawing->setWorksheet ( $x->getActiveSheet () );
						}
					}
					if (isset ( $d ['merge'] ) && is_array ( $d ['merge'] )) {
						$x->getActiveSheet ()->mergeCells ( $cellName [$j + $d ['merge'] [0]] . ($i + 2 + $d ['merge'] [1]) );
					}
					if (isset ( $d ['value'] )) {
						$x->getActiveSheet ()->setCellValueExplicit ( $cellName [$j] . ($i + 2), $d ['value'], is_int ( $d ['value'] ) ? TYPE_NUMERIC\PHPExcel_Cell_DataType::TYPE_NUMERIC : TYPE_NUMERIC\PHPExcel_Cell_DataType::TYPE_STRING );
					}
				} else {
					$x->getActiveSheet ()->setCellValueExplicit ( $cellName [$j] . ($i + 2), $d, \PHPExcel_Cell_DataType::TYPE_STRING );
				}
				$j ++;
			}
		}
		$objWriter = \PHPExcel_IOFactory::createWriter ( $x, 'Excel5' );
		$objWriter->save ( 'php://output' );
	}
	public function getImg($a) {
		if (substr ( $a, 0, 1 ) == '/') {
			$path = substr ( $a, 1 );
			if (file_exists ( $path )) {
				return $path;
			}
			return '';
		} elseif (substr ( $a, 0, 5 ) == 'http:' || substr ( $a, 0, 6 ) == 'https:') {
			return '';
			$name = md5 ( $a );
			$b = explode ( ".", $a );
			$hz = $b [count ( $b ) - 1];
			$name = 'Public/images/out.' . $name . '.' . $hz;
			if (! file_exists ( $name )) {
				file_put_contents ( $name, file_get_contents ( $a ) );
			}
			return $name;
		} else {
			return '';
			return $a;
		}
	}
	public function import($filename) {
		// $filename="F:\\1.xls";
		vendor ( "PHPExcel.PHPExcel" );
		$objReader = \PHPExcel_IOFactory::createReader ( 'Excel5' );
		$objPHPExcel = $objReader->load ( $filename );
		$objWorksheet = $objPHPExcel->getActiveSheet ();
		$highestRow = $objWorksheet->getHighestRow ();
		$highestColumn = $objWorksheet->getHighestColumn ();
		$highestColumnIndex = \PHPExcel_Cell::columnIndexFromString ( $highestColumn );
		$excelData = array ();
		for($row = 1; $row <= $highestRow; $row ++) {
			for($col = 0; $col < $highestColumnIndex; $col ++) {
				$excelData [$row] [] = ( string ) $objWorksheet->getCellByColumnAndRow ( $col, $row )->getValue ();
			}
		}
		return $excelData;
	}
	public function createExportData($table, $keys, $dataList) {
		$ids = array ();
		foreach ( $dataList as $v ) {
			$ids [] = $v ['id'];
		}
		$g = $_GET;
		unset ( $g ['p'] );
		$data = array (
				"table" => $table,
				"keys" => $keys,
				'ids' => join ( ',', $ids ),
				'get' => join ( " and ", $g ) 
		);
		return \koko::ya ( serialize ( $data ) );
	}
}

?>