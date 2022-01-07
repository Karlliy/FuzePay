<?php
ini_set('SHORT_OPEN_TAG', 'On');                // 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors', 'On');                // 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
include_once("BaseClass/Setting.php");
include_once("BaseClass/CDbShell.php");

/*$fp = fopen('LOG_CREDIT.txt', 'a');
fwrite($fp, " ---------------- START ---------------- \n\r");

foreach($_POST as $key => $val)
{
	fwrite($fp, $key." => ".$val."  \n\r");
}

$_POST["Installment"] = 0;
    
fwrite($fp, " ---------------- END ---------------- \n\r");
fclose($fp);*/

//print_r($_POST);

//echo "<center>錯誤：此支付方式還沒開始</center>";
//exit;
@CDbShell::connect();

if (strlen(trim($_POST["HashKey"])) < 10 || strlen(trim($_POST["HashIV"])) < 10) {
	echo "<center>錯誤：9180001</center>";
	exit;
}

CDbShell::query("SELECT * FROM Firm WHERE BINARY HashKey = '".$_POST["HashKey"]."' AND BINARY HashIV = '".$_POST["HashIV"]."'"); 
if (CDbShell::num_rows() != 1) {
	echo "<center>錯誤：9180002</center>";
	exit;
}else {
	$FirmRow = CDbShell::fetch_array();
}

if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   
	$myip = $_SERVER['REMOTE_ADDR'];   
} else {   
	$myip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);   
	$myip = $myip[2];   
}

if ($FirmRow["RefusalIP"] != "") {
	$RefusalIP = mb_split("##", $FirmRow["RefusalIP"]);
	if (is_numeric(array_search($myip, $RefusalIP))) {
		echo "<center>錯誤：9180013</center>";
		exit;
	}
}

$SuccessURL     = $FirmRow["SuccessURL"];
$FailURL        = $FirmRow["FailURL"];
$TakeNumberURL  = $FirmRow["TakeNumberURL"];

try {
	if (strlen(trim($_POST["MerTradeID"])) < 6) {
		$ErrCode = "9180003";
		throw new exception("請傳入店家交易編號");
	}
	
	if (strlen(trim($_POST["MerProductID"])) < 2) {
		$ErrCode = "9180004";
		throw new exception("請傳入店家商品代號");
	}
	
	if (preg_match("/^[A-Za-z0-9]{2,}$/",$_POST["MerUserID"]) == false) {
		$ErrCode = "9180005";
		throw new exception("請傳入消費者ID，且消費者ID只能英文或數字組合");
	}
	
	if (!is_numeric($_POST["Amount"]) || intval($_POST["Amount"]) < 5) {
		$ErrCode = "9180006";
		throw new exception("請傳入交易金額；交易金額請大於5元");
	}
	
	if ($_POST["Installment"] == 1) {
		if (!is_numeric($_POST["Amount"]) || intval($_POST["Amount"]) < 100) {
			$ErrCode = "9180006";
			throw new exception("信用卡分期交易金額請大於100元");
		}
	}
	
	
	/*if (strlen(trim($_POST["TradeDesc"])) < 1) {
		$ErrCode = "9180007";
		throw new exception("請傳入交易描述 ");
	}
	
	if (strlen(trim($_POST["ItemName"])) < 1) {
		$ErrCode = "9180008";
		throw new exception("請傳入商品名稱");
	}
	
	if ($_POST["UnionPay"] != "0" && $_POST["UnionPay"] != "1") {
		$ErrCode = "9180011";
		throw new exception("請傳入正確信用卡類別");
	}*/
	
	CDbShell::query("SELECT * FROM Ledger WHERE FirmSno = ".$FirmRow["Sno"]." AND MerTradeID = '".trim($_POST["MerTradeID"])."'"); 	
	if (CDbShell::num_rows() >= 1) {
		$ErrCode = "9180009";
		throw new exception("店家交易編號重複");
	}

	if ($FirmRow["SMSCheck"] == 1) {
        $_Verified = false;

        if (trim($_POST["Token"]) == "") {
            $_Verified = false;
        }else {
            $sql = "SELECT * FROM smscheck WHERE Token = '".$_POST["Token"]."' AND Status = 0";
            $result = CDbShell::query($sql);
            if (CDbShell::num_rows($result) == 1) {
                $_Verified = true;
            }
        }
        if ($_Verified == false) {
            $FormHtml = '<form name="data" method="post">';
            foreach($_POST as $key => $val)
            //while (list ($key, $val) = each ($_POST)) 
            {
                $FormHtml .= '<input type="hidden" name="'.$key.'" id="'.$key.'" value="'.$val.'">';
            };
            $FormHtml .= '<input type="hidden" name="filename" id="filename" value="CreditPayment.php">';
            $FormHtml .= '</form>';
            
            include("VerifyCode.html");
            echo $FormHtml;
            exit;
        }
    }

	$TradeDesc = $_POST["TradeDesc"];
	$ItemName = $_POST["ItemName"];
	/*if ($_POST["Installment"] == 1) {
		CDbShell::query("SELECT PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '1' AND (PF.Kind = '信用卡3期' OR PF.Kind = '信用卡12期') AND FC.Enable = 1 LIMIT 1");
	}else {
		CDbShell::query("SELECT PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '1' AND PF.Kind = '信用卡' AND FC.Enable = 1 LIMIT 1"); 
	}*/
	CDbShell::query("SELECT PF.Kind, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '7' AND FC.Enable = 1 LIMIT 1"); 
	$PayModeRow = CDbShell::fetch_array();
	$PaymentMode = $PayModeRow["Mode"];

	CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '7' AND FC.Enable = 1 AND (FC.FeeRatio > 0 OR FC.FixedFee) LIMIT 1"); 
		$Row = CDbShell::fetch_array();
		if (CDbShell::num_rows() >= 1) {
			$PaymentMode = $PayModeRow["Mode"];

			$PaymentName = $PayModeRow["Kind"] . "-". $PayModeRow["Mode"];
			$Fee = 0;
			if (floatval($PayModeRow["FeeRatio"]) > 0){
				$Fee = floatval($_POST['Amount']) * floatval($PayModeRow["FeeRatio"] / 100);
			}
			if ($PayModeRow["FixedFee"] != 0) {
				$Fee = $Fee + $PayModeRow["FixedFee"];
			} 
				
			if (is_numeric($PayModeRow["MinFee"]) && $PayModeRow["MinFee"] > 0) {
				
				if ($Fee < floatval($PayModeRow["MinFee"]))
					$Fee = $PayModeRow["MinFee"];
			}
			
			if (is_numeric($PayModeRow["MaxFee"]) && $PayModeRow["MaxFee"] > 0) {
				
				if ($Fee > floatval($PayModeRow["MaxFee"]))
					$Fee = $PayModeRow["MaxFee"];
			}
		}else {
			$ErrCode = "9180010";
			throw new exception("未設定系統介接或此金流已被關閉，請接洽".Simplify_Company."，".Simplify_Company."客服專線：".Base_TEL);
		}
	    
	    $ValidDate = date('Y-m-d', strtotime(date('Y-m-d') . " +20 day"));
		/*switch ($Row["Closing"]) {
			case "Day":
				$ExpectedRecordedDate = date("Y-m-d",mktime (date("H"),date("i"),date("s"),date("m") ,date("d") + ($Row["Day"]) ,date("Y")));
				$Period = date("Y-m-d",mktime (date("H"),date("i"),date("s"),date("m") ,date("d") ,date("Y")));
				$ClosingDate = date('Y-m-d');
				break;
			case "Week":
				$TodayWeek = Date('w');
				if ($TodayWeek == 0) $TodayWeek = 7;
				$ExpectedRecordedDate = date("Y-m-d",mktime (date("H"),date("i"),date("s"),date("m") ,date("d") + (7- $TodayWeek) + $Row["Day"] ,date("Y")));
				$Period = date('Y-m-d',strtotime(date('Y-m-d'). " -".($TodayWeek - 1)." day")) . " ~ " . date('Y-m-d',strtotime(date('Y-m-d'). " +".(7 - $TodayWeek)." day"));
				$ClosingDate = date('Y-m-d',strtotime(date('Y-m-d'). " +".(7- $TodayWeek)." day"));
				break;
			case "Month":
				$ExpectedRecordedDate = date('Y-m-d',strtotime(date('Y-m-01'). " +1 month +".$Row["Day"]." day")) ;
				$Period = date("Y-m-01",mktime (date("H"),date("i"),date("s"),date("m") ,date("d") ,date("Y"))) . " ~ " . date('Y-m-d',strtotime(date('Y-m-01'). " +1 month -1 day"));
				$ClosingDate = date('Y-m-d',strtotime(date('Y-m-01'). " +1 month -1 day"));
				break;
		}*/
		//srand(mktime());
		$CashFlowID = Date("ymdHis").str_pad(floor(microtime() * 10000),4,'0',STR_PAD_LEFT).str_pad(rand(0,9999),4,'0',STR_PAD_LEFT);
		if ($_Verified == true) {        
			$sql = "UPDATE smscheck SET CashFlowID = '".$CashFlowID."', Status = 1 WHERE Token = '".$_POST["Token"]."'";
			CDbShell::query($sql);
		}
	
		//$CashFlowID = "21060318371780499593";
		$field = array("FirmSno", "CashFlowID", "MerTradeID", "MerProductID", "MerUserID", "PaymentType", "PaymentName", "Total", "Fee", "ValidDate", "IP", "FeeRatio", "State");
		$value = array($FirmRow["Sno"], $CashFlowID, $_POST['MerTradeID'], $_POST['MerProductID'], $_POST['MerUserID'], "7", $PaymentName, $_POST['Amount'], $Fee, $ValidDate, $myip, $FirmRow["FeeRatio"], "-1");
		CDbShell::insert("ledger", $field, $value);
	
	if ($PaymentMode == "GASH") {  
    
		/*if ($_POST["Installment"] == 1) {
			CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Kind = '信用卡3期' AND FC.Enable = 1 AND (FC.FeeRatio > 0 OR FC.FixedFee) LIMIT 1"); 
		}else {
			CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Kind = '信用卡' AND FC.Enable = 1 AND (FC.FeeRatio > 0 OR FC.FixedFee) LIMIT 1"); 
		}*/
		
		unset($parameter);
		$parameter = array(
			"MSG_TYPE"				=> "0100",
			"PCODE"					=> "300000",
			"CID"					=> GASH_CID,
			"COID"					=> $CashFlowID,
			"CUID"					=> "TWD",
			"PAID"					=> "BNK80702",			//國際信用卡(永豐信用卡)
			//"PAID"					=> "COPGAM05",
			"AMOUNT"				=> $_POST["Amount"],
			"RETURN_URL"			=> Receive_URL."PaySuccess",			
			"ORDER_TYPE"			=> "M",
			"MEMO "					=> trim($_POST["MerTradeID"]),
			"PRODUCT_NAME "			=> trim($_POST["MerProductID"]),
			"USER_ACCTID"			=> $_POST["MerUserID"]
		);
		$ERQC = _GetERQC(GASH_CID, $CashFlowID, "TWD", $_POST["Amount"], GASH_Key1, GASH_Key2, GASH_TKey);
		$parameter["ERQC"] = $ERQC;
		
		$xml = new SimpleXMLElement('<TRANS/>');
		//array_walk_recursive($parameter, array ($xml, 'addChild'));
		array_walk_recursive($parameter, function($value, $key)use($xml){
			$xml->addChild($key, $value);
		});
		$data = base64_encode($xml->asXML());

		$sHtml = "<form id='rongpaysubmit' name='rongpaysubmit' action='".GASH_URL."' method='POST'>";
		/*while (list ($key, $val) = each ($parameter)) 
		{
		    $shtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
		}*/
		$sHtml = $sHtml."<input type='hidden' name='data' value='".$data."'/>";
		// $sHtml = $sHtml."<input type='submit' value='付款'></form>";
		$sHtml = $sHtml."<input type='submit' value='付款' style='display:none'></form>";
		$sHtml = $sHtml."<script>document.forms['rongpaysubmit'].submit();</script>";
		
		/*$fp = fopen('Log/AP_CreditPay_'.date("Ymd His").'.txt', 'a');
		fwrite($fp, $sHtml."\n\r");
		fclose($fp);*/
	
		echo $sHtml;
		exit;
	}elseif ($PaymentMode == "TapPay") {

		$MerTradeID	  = $_POST["MerTradeID"];
		$MerProductID = $_POST['MerProductID'];
		$MerUserID    = $_POST['MerUserID'];
		$Amount       = $_POST['Amount'];
		
		include("CreditPay.html");
		/*$_cardholder= array(
			"phone_number"		=> $_POST["Mobile"],
			"name"				=> $_POST["Name"],			
			"email"				=> $_POST["Email"],
			"zip_code"			=> "",
			"address"			=> "",
			"national_id"		=> "",
			"member_id"			=> ""
		  );

		$parameters = array(
			"prime"				=> "test_3a2fb2b7e892b914a03c95dd4dd5dc7970c908df67a49527c0a648b2bc9",
			"partner_key"		=> TapPay_PartnerKey,	
			"merchant_id"		=> "REGENT_CTBC",
			"amount"			=> (intval($_POST["Amount"])*100),
			"details"           => "測試商品",
			"cardholder"		=> json_decode(json_encode($_cardholder, true))
		);

		//$parameters["cardholder"] = json_decode(json_encode($_cardholder, true));

		$strReturn = SockPost2(TapPay, json_encode($parameters, JSON_UNESCAPED_UNICODE), $curlerror);

		var_dump($strReturn);*/
	}else if (is_numeric(mb_strpos($PaymentMode, "百適匯", "0", "UTF-8"))) {

		$parameters = array(
            "HashKey"		=> "TNVSAR46RTHY7GB4GCFTFPYR",
            "HashIV"		=> "F98PWKBVKAMDQSF8LALC4EVJS",	
            "MerTradeID"	=> $CashFlowID,
            "MerProductID"	=> $_POST['MerProductID'],
            "MerUserID"     => $_POST['MerUserID'],
            "Amount"        => $_POST["Amount"],
        );

		$sHtml = "<form id='rongpaysubmit' name='rongpaysubmit' action='https://bes-pay.com/Credit' method='POST'>";
		foreach($parameters as $key => $val) {
			//while (list($key, $val) = each($parameter)) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='付款' style='display:none'></form>";
        $sHtml = $sHtml . "<script>document.forms['rongpaysubmit'].submit();</script>";
        
        echo $sHtml;
        exit;

	}else {
		throw new exception("線上刷卡未啟用，請接洽".Simplify_Company."，".Simplify_Company."客服專線：".Base_TEL);
	}
} catch(Exception $e) {
	echo "<center>錯誤：".$ErrCode."(".$e->getMessage().")</center>";
	if ($FailURL != "") {
		$sHtml = "<form id='Deviant' name='Deviant' action='".$FailURL."' method='POST'>";
		$sHtml.= "<input type='hidden' name='ErrCode' value='".$ErrCode."'/>";
		$sHtml.= "<input type='hidden' name='ErrMessage' value='".$e->getMessage()."'/>";
		$sHtml.= "<input type='submit' value='送出'></form>";

		$sHtml.= "<script>document.forms['Deviant'].submit();</script>";
		echo $sHtml;
	}
	exit;
} 

function _GetERQC($cid, $coid, $cuid, $amt, $key, $iv, $pwd)
{
	$erqc = "";
	$encrypt_data = "%s%s%s%s%s";
	
	// 驗證用的 AMOUNT 需整理成 14 碼
	if (strpos($amt, ".") !== false)
	{
		$amt = substr($amt, 0, strpos($amt, ".")) . ((strlen($amt) - strpos($amt, ".")) > 3 ? substr($amt, strpos($amt, ".") + 1, 2) : str_pad(substr($amt, (strpos($amt, ".") + 1)), 2, "0"));
		$amt = str_pad($amt, 14, "0", STR_PAD_LEFT);
	}
	else
	{
		$amt = str_pad($amt, 12, "0", STR_PAD_LEFT) . "00"; //.PadLeft(14, '0');
	}

	//$amt = "00000000005000";
	$encrypt_data = sprintf($encrypt_data, $cid, $coid, $cuid, $amt, $pwd);

	//$des = new Crypt3Des($key,$iv);
	$base64_encrypt_data = encrypt( $encrypt_data, $key,$iv );
	$erqc = base64_encode( sha1( $base64_encrypt_data, true ) );
	
	return $erqc;
}

function encrypt ($value, $_key, $_iv)
{
	$iv = base64_decode($_iv);
	$key = base64_decode($_key);
	$value = PaddingPKCS7($value);
	$ret = openssl_encrypt($value, "DES-EDE3-CBC", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
	$ret = base64_encode( $ret );
	return $ret;
}
function PaddingPKCS7 ($data)
{
	$block_size = 8;
	$padding_char = $block_size - (strlen($data) % $block_size);
	$data .= str_repeat(chr($padding_char), $padding_char);
	return $data;
}

function build_mysign($sort_array, $HashKey, $HashIV, $sign_type = "MD5") 
{
    $prestr = create_linkstring($sort_array);     	
    $prestr = "HashKey=".$HashKey."&".$prestr."&HashIV=".$HashIV;
    //echo $prestr;
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
    foreach($array as $key => $val)
    //while (list ($key, $val) = each ($array)) 
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
    ksort($array);
    reset($array);
    return $array;
}
function SockPost2($URL, $Query, &$curlerror){
    $headers = array(
        'Content-type: application/json',
        'x-api-key: '.TapPay_PartnerKey,
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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

class Cryptographic {
	static $iv = "M#yC!ash";
	static $key = "6BAE2CF100974F9D88AE";

	static function encrypt($str) {

		$td = @mcrypt_module_open(MCRYPT_TRIPLEDES, '', MCRYPT_MODE_CBC, self::$iv);
		
		@mcrypt_generic_init($td, self::$key, self::$iv);
		$encrypted = @mcrypt_generic($td, $str);

		@mcrypt_generic_deinit($td);
		@mcrypt_module_close($td);

		return bin2hex($encrypted);
	}
	static function hex2bin($hexdata) {
		$bindata = '';

		for ($i = 0; $i < strlen($hexdata); $i += 2) {
		    $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
		}

		return $bindata;
    }
	static function decrypt($encrypted){

		$encrypted = self::hex2bin($encrypted);
		$cipher = @mcrypt_module_open(MCRYPT_TRIPLEDES, '', MCRYPT_MODE_CBC, self::$iv );
	    $key = substr(self::$key, 0, @mcrypt_enc_get_key_size($cipher));
	 
		// 128-bit blowfish encryption:
		if (@mcrypt_generic_init($cipher, $key, self::$iv) != -1){
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
}
?>