<?php
	ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	header('Content-Type: text/html; charset=utf-8');
	if (strlen(trim($_GET["Sno"])) == 0) {
		echo "<center>錯誤！</center>";
		exit;
	}
		try {
		$Sno = Cryptographic::decrypt($_GET["Sno"]);
		//echo $_GET["Sno"]."A".$Sno;
		include_once("BaseClass/Setting.php");
		include_once("BaseClass/CDbShell.php");
		
		@CDbShell::connect();
		CDbShell::query("SELECT * FROM Firm WHERE Sno = '".$Sno."'"); 
		if (CDbShell::num_rows() != 1) {
			echo "<center>錯誤：沒有廠商</center>";
			exit;
		}
		$FirmRow = CDbShell::fetch_array();
		$_VirAccount = false;
		//echo "SELECT FC.*, PF.Mode, PF.Kind FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = " . $Sno . " AND PF.Type = '1' AND PF.Kind = '虛擬帳號12碼' LIMIT 1";
		//exit;
		CDbShell::query("SELECT FC.*, PF.Mode, PF.Kind FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = '" . $Sno . "' AND PF.Type = '1' AND PF.Kind = '虛擬帳號12碼' AND Enable = 1 LIMIT 1");
		if (CDbShell::num_rows() >= 1) {
			$_VirAccount = true;
		}
		include("card.html");
	} catch(Exception $e) {
	echo "<center>錯誤：".$e->getMessage()."</center>";
	exit;
} 
class Cryptographic {
	static $iv = "M#yC!ash";
	static $key = "6BAE2CF100974F9D88AE";

	static function decrypt($encrypted){

		$encrypted = self::hex2bin($encrypted);
		$cipher = @mcrypt_module_open(MCRYPT_TRIPLEDES, '', MCRYPT_MODE_CBC, self::$iv );
	    $key = substr(self::$key, 0, @mcrypt_enc_get_key_size($cipher));
	 
		// 128-bit blowfish encryption:
		if (@mcrypt_generic_init($cipher, self::$key, self::$iv) != -1){
			// PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
			$cleartext = @mdecrypt_generic($cipher,$encrypted );
			@mcrypt_generic_deinit($cipher);
		}
	 
		// suppression du padding.
		$pad = ord(substr($cleartext,strlen($cleartext)-1));
		if($pad>0 & $pad<=8){
			$cleartext = substr($cleartext, 0, strlen($cleartext) - $pad);
		}

		return $cleartext;
	 
	}
	
	static function hex2bin($hexdata) {
		$bindata = '';

		for ($i = 0; $i < strlen($hexdata); $i += 2) {
		    $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
		}

		return $bindata;
    }
}
?>