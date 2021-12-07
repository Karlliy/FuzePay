<?php 
class JSModule {
	static function Message($value, $url=""){ //
		header("Content-type: text/html; charset=utf-8");
		
		$js  = "<script type=\"text/javascript\" language=\"javascript\">";
		$js .= "alert(\"".$value."\");";
		if($url) $js .= "window.location.href='".$url."';";
		else $js .= "window.history.go(-1);";
		$js .= "</script>\r\n";
	
		echo $js;
		exit;
	}
	
	static function BoxCloseMessage($value, $url = "") { //
		header("Content-type: text/html; charset=utf-8");
		
		$js  = "<script type=\"text/javascript\" language=\"javascript\">";
		$js .= "alert(\"".$value."\");";
		if($url) $js .= "parent.location='".$url."';";
		else $js .= "parent.location='".$_SERVER['PHP_SELF']."';";
        $js .= "parent.$.jBox.close('user');";
		$js .= "</script>\r\n";
	
		echo $js;
		exit;
	}
	
	static function ErrorMessage($value, $url=""){ //
		header("Content-type: text/html; charset=utf-8");
		
		$js .= "<script type='text/javascript' src='../jquery/jquery-1.11.0.js'></script>";	
		$js .= "<script type='text/javascript' src='../box/jBox.min.js'></script>";	
		$js .= "<script type='text/javascript' src='../box/jBox.js'></script>";	
		$js .= "<link type='text/css' rel='stylesheet' href='../box/jBox.css'/>";	

		$js .= "<script type=\"text/javascript\" language=\"javascript\">";	
		$js .= "jQuery(document).ready(function() {	";	
		$js .= "new jBox('Notice', {";
		$js .= "autoClose: 3000,";
		$js .= "position: {";
		$js .= "x: 'center',";
		$js .= "y: 'center'";
		$js .= "},";
		$js .= "stack: true,";
		$js .= "animation: {";
		$js .= "open: 'tada',";
		$js .= "close: 'zoomIn'";
		$js .= "},";
		$js .= "title: 'Error!',";
		$js .= "content: '".$value."',";
		$js .= "color: 'red',";
		$js .= "});";
		$js .= "});";
		$js .= "</script>\r\n";
		
		echo $js;
		//exit;
	}
	static function JSMessage($value, $url = "") { //
		
		if ($value != "") $js .= "alert(\"".$value."\");";
		if($url) $js .= "parent.location='".$url."';";
		else $js .= "parent.location='".$_SERVER['PHP_SELF']."';";
		
		echo $js;
		exit;
	}
	
	static function BoxCloseJSMessage($value, $url = "") { //
		
		$js .= "alert(\"".$value."\");";
		if($url) $js .= "parent.location='".$url."';";
		else $js .= "parent.location='".$_SERVER['PHP_SELF']."';";
        $js .= "parent.$.jBox.close('user');";
		
		echo $js;
		exit;
	}
	static function ErrorJSMessage($value, $url=""){ //
		
		$js .= "jQuery(document).ready(function() {	";	
		$js .= "new jBox('Notice', {";
		$js .= "autoClose: 3000,";
		$js .= "position: {";
		$js .= "x: 'center',";
		$js .= "y: 'center'";
		$js .= "},";
		$js .= "stack: true,";
		$js .= "animation: {";
		$js .= "open: 'tada',";
		$js .= "close: 'zoomIn'";
		$js .= "},";
		$js .= "title: '錯誤!',";
		$js .= "content: '".$value."',";
		$js .= "color: 'red',";
		$js .= "});";
		$js .= "});";
		
		echo $js;
		//exit;
	}
	
	static function jBoxMessage($value, $url=""){ //
		
		$js .= "jQuery(document).ready(function() {	";	
		$js .= "new jBox('Notice', {";
		$js .= "autoClose: 3000,";
		$js .= "position: {";
		$js .= "x: 'center',";
		$js .= "y: 'center'";
		$js .= "},";
		$js .= "stack: true,";
		$js .= "animation: {";
		$js .= "open: 'tada',";
		$js .= "close: 'zoomIn'";
		$js .= "},";
		//$js .= "title: '錯誤!',";
		$js .= "content: '".$value."',";
		$js .= "color: 'blue',";
		$js .= "});";
		$js .= "});";
		
		echo $js;
		//exit;
	}
}


?>