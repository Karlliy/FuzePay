<?php
class CString {
	
	function cuttingstr($str,$ct,$addstr="") {
		$len=strlen($str);
		if(strlen($str) > $ct) {
			for($i=0;$i<$ct;$i++) {
				$ch=substr($str,$i,1);
				if(ord($ch)>127) $i++;
			}
		  $str= substr($str,0,$i);
		} 
		if(strlen($str) < $len) $str.=$addstr;
		return $str;
	}
	
	function special_www($pbody) {
		$pbody =preg_replace( "/\[www *\]([\\x0-\\xff]*?)\[\/www *\]/", '<a href="\\1"  target="_blank">\\1</a>', $pbody ); 
		$pbody =preg_replace( "/\[www +([a-zA-Z0-9\.:\/_\-]+)\]([\\x0-\\xff]*?)\[\/www *\]/", '<a href="\\1"  target="_blank">\\2</a>', $pbody ); 	
		return $pbody;
	}


}

?>