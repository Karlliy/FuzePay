<?php
	ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	header('Content-Type: text/html; charset=utf-8');
	header('Access-Control-Allow-Origin: *');
	include_once("BaseClass/Setting.php");
	include_once("BaseClass/CDbShell.php");
	include_once("BaseClass/CommonElement.php");
	
	//print_r($_POST);
	$fp = fopen('Log/Fubon/Notify_LOG_'.date("YmdHi").'.txt', 'a');
	fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
	while (list ($key, $val) = each ($_POST)) 
	{
		fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
	};
	
	fwrite($fp, "\$_POST['Data'] =>".$_POST['Data'].PHP_EOL);
	//$_VatmAccount = substr($_POST['Data'], -14);
	$_VatmAccount = substr($_POST['Data'], 45, 14);
	$_Amt = substr($_POST['Data'], 25, 13);
	$_TX = substr($_POST['Data'], 16, 8);
	$_DATE = substr($_POST['Data'], 8, 8);

	$_PaymentAmt = intval($_Amt);
	//$_PaymentDate = Date('Y-m-d', strtotime($_DATE));
	$_PaymentDate = Date('Y-m-d H:i:s');

	fwrite($fp, "VatmAccount =>".$_VatmAccount.PHP_EOL);
	fwrite($fp, "PaymentAmt =>".$_PaymentAmt.PHP_EOL);
	fwrite($fp, "TX =>".$_TX.PHP_EOL);
	fwrite($fp, "PaymentDate =>".$_PaymentDate.PHP_EOL);
	fclose($fp);
	
	//echo "CashFlowID = '".$_POST["MerchantTradeNo"];
	@CDbShell::connect();
	CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.Fee FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.VatmAccount = '".$_VatmAccount."'"); 
	$FirmRow = CDbShell::fetch_array();
	$SuccessURL = $FirmRow["SuccessURL"];
	$FailURL = $FirmRow["FailURL"];
	
	CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Kind = '虛擬帳號' AND FC.Enable = '1' LIMIT 1");  
	$FCRow = CDbShell::fetch_array();
	
	switch ($FCRow["Closing"]) {
		case "Day":
			//$ExpectedRecordedDate = date('Y-m-d', strtotime($_PaymentDate ." +".$FCRow["Day"]." day"));
			$Period = date("Y-m-d", strtotime($_PaymentDate));
			$ClosingDate = date("Y-m-d", strtotime($_PaymentDate));
			break;
		case "Week":
			$TodayWeek = Date('w');
			if ($TodayWeek == 0) $TodayWeek = 7;
			//$ExpectedRecordedDate = date("Y-m-d", strtotime($_PaymentDate ."+".(7- $TodayWeek + $FCRow["Day"]). " day"));
			$Period = date('Y-m-d', strtotime($_PaymentDate. " -".($TodayWeek - 1)." day")) . " ~ " . date('Y-m-d',strtotime($_PaymentDate. " +".(7 - $TodayWeek)." day"));
			$ClosingDate = date('Y-m-d', strtotime($_PaymentDate. " +".(7- $TodayWeek)." day"));
			break;
		case "Month":
			//$ExpectedRecordedDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month +".$FCRow["Day"]." day"));
			$Period = date("Y-m-01", strtotime($_PaymentDate)) . " ~ " . date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
			$ClosingDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
			break;
	}
	$ExpectedRecordedDate = CommonElement::CountHoliday($ClosingDate, $FCRow["Day"], true);

	if (floatval($FCRow["FeeRatio"]) > 0){
		$Fee = floatval($_PaymentAmt) * floatval($FCRow["FeeRatio"] / 100);
	}
	if ($FCRow["FixedFee"] != 0) {
		$Fee = $Fee + $FCRow["FixedFee"];
	}

	if (is_numeric($FCRow["MinFee"]) && $FCRow["MinFee"] > 0) {
            
		if ($Fee < floatval($FCRow["MinFee"]))
			$Fee = $FCRow["MinFee"];
	}
	
	if (is_numeric($FCRow["MaxFee"]) && $FCRow["MaxFee"] > 0) {
		
		if ($Fee > floatval($FCRow["MaxFee"]))
			$Fee = $FCRow["MaxFee"];
	}

	
	$field = array("OrderID", "PaymentCode", "Period", "ClosingDate", "ExpectedRecordedDate", "ClosingTotal", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State");
	$value = array($_TX, "虛擬帳號-富邦", $Period, $ClosingDate, $ExpectedRecordedDate, $_PaymentAmt, $_PaymentDate, $_PaymentDate, "0", "支付成功", "0");
	CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VatmAccount."' AND State = -1" );

	if ($Fee > $FirmRow['Fee']) {
		$field = array("Fee");
		$value = array($Fee);
		CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VatmAccount."'" );
	}
	
	echo "";
	
	if ($SuccessURL != "") {
		
		$Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=1&MerTradeID=".$FirmRow["MerTradeID"]."&MerUserID=".$FirmRow["MerUserID"]."");
		
		$SendPOST["RtnCode"] = "1";
		$SendPOST["RtnMessage"] = "支付成功";
		$SendPOST["MerTradeID"] = $FirmRow["MerTradeID"];
		$SendPOST["MerProductID"] = $FirmRow["MerProductID"];
		$SendPOST["MerUserID"] = $FirmRow["MerUserID"];

		$SendPOST["VatmAccount"] = $_VatmAccount;
		$SendPOST["Amount"] = $_PaymentAmt;
		$SendPOST["PaymentDate"] = $_PaymentDate;
		$SendPOST["Validate"] = $Validate;
		try {
			$strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

			$field = array("CallbackURL", "CallbackReturn", "CurlError");
			$value = array($SuccessURL, $strReturn, $curlerror);
			CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VatmAccount."'");

			if (strcasecmp($strReturn, 'success') == 0) {
				$field = array("SendStatus");
				$value = array("1");
				CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VatmAccount."'");
			}
		}
		catch (Exception $e) {
			$field = array("CallbackURL", "CallbackReturn", "CurlError");
			$value = array($SuccessURL, $e->getMessage(), $curlerror);
			CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VatmAccount."'");
		} 
	}else {
		$field = array("CallbackURL", "CallbackReturn", "CurlError");
		$value = array($SuccessURL, "回調網址是空的", "回調網址是空的");
		CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VatmAccount."'");
	}
	
	function SockPost($URL, $Query, &$curlerror){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $Query);
		$SSL = (substr($URL, 0, 8) == "https://" ? true : false); 
		if ($SSL) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		$strReturn = curl_exec($ch);
		
		if(curl_errno($ch)){
		    $curlerror = "Request Error(".curl_errno($ch)."):" . curl_error($ch) ;
		}else {
			$curlerror = "Request OK(".curl_errno($ch)."):" . curl_error($ch);
		}
		
		curl_close ($ch);
		
		return $strReturn;
		
	}
	function _replaceChar($value)
	{
		$search_list = array('%2d', '%5f', '%2e', '%21', '%2a', '%28', '%29');
		$replace_list = array('-', '_', '.', '!', '*', '(', ')');
		$value = str_replace($search_list, $replace_list ,$value);
		
		return $value;
	}
	//產生檢查碼
	function _getMacValue($hash_key, $hash_iv, $form_array)
	{
		$encode_str = "HashKey=" . $hash_key;
		foreach ($form_array as $key => $value)
		{
			$encode_str .= "&" . $key . "=" . $value;
		}
		$encode_str .= "&HashIV=" . $hash_iv;
		echo $encode_str;
		$encode_str = strtolower(urlencode($encode_str));
		$encode_str = _replaceChar($encode_str);
		return md5($encode_str);
	}
	
	function create_aes_decrypt($parameter = "", $key = "", $iv = "")
	{
	    return strippadding(openssl_decrypt(hex2bin($parameter), 'AES-256-CBC', 'zn2E7vzUy31MRVNzIOWOPXJWiDIDltvU', OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, 'QxzY29TBdUo4QbEz'));
	}
	function strippadding($string)
	{
	    $slast  = ord(substr($string, -1));
	    $slastc = chr($slast);
	    $pcheck = substr($string, -$slast);
	    if (preg_match("/$slastc{" . $slast . "}/", $string)) {
	        $string = substr($string, 0, strlen($string) - $slast);
	        return $string;
	    } else {
	        return false;
	    }
	}
//------------------------------------------交易輸入參數------------

	function build_mysign($sort_array, $HashKey, $HashIV, $sign_type = "MD5") 
	{
	    $prestr = create_linkstring($sort_array);     	
	    $prestr = "HashKey=".$HashKey."&".$prestr."&HashIV=".$HashIV;
	    echo $prestr;
	    //exit;
	    $prestr = strtolower(urlencode($prestr));    
	    //echo $prestr;
	    //exit;
	    $mysgin = sign($prestr,$sign_type);			    
	    return $mysgin;
	}	

	function create_linkstring($array) 
	{
	    $arg  = "";
	    while (list ($key, $val) = each ($array)) 
		{
	        $arg.=$key."=".$val."&";
	    }
	    $arg = substr($arg,0,count($arg)-2);		     //去掉最后一个&字符
	    return $arg;
	}

	function sign($prestr,$sign_type) 
	{
	    $sign='';
	    if($sign_type == 'MD5') 
		{
	        $sign = md5($prestr);
	    }
		else 
		{
	        die("暂不支持".$sign_type."类型的签名方式");
	    }
	    return $sign;
	}

	function arg_sort($array) 
	{
	    ksort($array, SORT_NATURAL | SORT_FLAG_CASE);
	    reset($array);
	    return $array;
	}
?>