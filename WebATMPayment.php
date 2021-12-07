<?php
ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting',E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
include_once("BaseClass/Setting.php");
include_once("BaseClass/CDbShell.php");
//print_r($_POST);
if (strlen(trim($_POST["HashKey"])) < 10 || strlen(trim($_POST["HashIV"])) < 10) {
	echo "<center>錯誤：9180001</center>";
	exit;
}
@CDbShell::connect();
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

$DeviantURL = $FirmRow["DeviantURL"];

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
	
	if (!is_numeric($_POST["Amount"]) || intval($_POST["Amount"]) <= 35) {
		$ErrCode = "9180006";
		throw new exception("請傳入交易金額或金額小於35元");
	}
	
	if (strlen(trim($_POST["TradeDesc"])) < 1) {
		$ErrCode = "9180007";
		throw new exception("請傳入交易描述 ");
	}
	
	if (strlen(trim($_POST["ItemName"])) < 1) {
		$ErrCode = "9180008";
		throw new exception("請傳入商品名稱");
	}
	
	if ($_POST["ChoosePayment"] != "WebATM") {
		$ErrCode = "9180011";
		throw new exception("請傳入正確WebATM代號");
	}
	
	CDbShell::query("SELECT * FROM Ledger WHERE FirmSno = ".$FirmRow["Sno"]." AND MerTradeID = '".trim($_POST["MerTradeID"])."'"); 	
	if (CDbShell::num_rows() >= 1) {
		$ErrCode = "9180009";
		throw new exception("店家交易編號重複");
	}
	
	switch($_POST["ChoosePayment"]) {
		case "WebATM":
			$ChoosePayment = "WebATM";
			$PaymentName = "WebATM";
			break;
	}

		if ($ChoosePayment == "WebATM") {
			$sql = "SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Kind = 'WebATM' AND FC.Enable = 1 AND (FC.FeeRatio > 0 OR FC.FixedFee) LIMIT 1";
			CDbShell::query($sql);
		}
		$Row = CDbShell::fetch_array();
		if (CDbShell::num_rows() >= 1) {
			if ($Row["FixedFee"] != 0) {
				$Fee = $Row["FixedFee"];
			}else {
				$Fee = floatval($_POST['Amount']) * floatval($Row["FeeRatio"] / 100);
				
				if (is_numeric($Row["MinFee"]) && $Row["MinFee"] > 0) {
					
					if ($Fee < floatval($Row["MinFee"])) $Fee = $Row["MinFee"];
				}
				
				if (is_numeric($Row["MaxFee"]) && $Row["MaxFee"] > 0) {
					
					if ($Fee > floatval($Row["MaxFee"])) $Fee = $Row["MaxFee"];
				}
			}
		}else {
			$ErrCode = "9180010";
			throw new exception("未設定系統介接，請接洽".Simplify_Company."，".Simplify_Company."客服專線：".Base_TEL);
		}
		
		$ValidDate = date("Y-m-d H:i:s",mktime (date("H"),date("i"),date("s"),date("m") ,date("d") + 2 ,date("Y")));

		$CashFlowID = Date("ymdHis").str_pad(floor(microtime() * 10000),4,'0',STR_PAD_LEFT).str_pad(rand(0,9999),4,'0',STR_PAD_LEFT);
		
		$field = array("FirmSno", "CashFlowID", "MerTradeID", "MerProductID", "MerUserID", "PaymentType", "PaymentName", "Total", "Fee", "ValidDate", "IP", "FeeRatio", "State");
		$value = array($FirmRow["Sno"], $CashFlowID, $_POST['MerTradeID'], $_POST['MerProductID'], $_POST['MerUserID'], "5", $PaymentName, $_POST['Amount'], $Fee, $ValidDate, $myip, $FirmRow["FeeRatio"], "-1");
		CDbShell::insert("ledger", $field, $value);
		
		$PaymentMode = $Row["Mode"];
		if ($ChoosePayment == "WebATM") {
			if (is_numeric(mb_strpos($PaymentMode, "綠界", "0", "UTF-8"))) {
				
				$parameter = array(
					"MerchantID"			=> ecpay_Merchant_ID,
					"MerchantTradeNo"		=> $CashFlowID,
					"MerchantTradeDate"		=> date("Y/m/d H:i:s"),
					"PaymentType"			=> "aio",
					"TotalAmount"			=> intval($_POST["Amount"]),
					"TradeDesc"				=> $_POST["TradeDesc"],
					"ItemName"				=> $_POST["ItemName"],
					"ReturnURL"				=> Receive_URL."Ecpay_WebATM_Success.php",
					"ChoosePayment"			=> "WebATM",
					"NeedExtraPaidInfo"		=> "Y",
				);
				
				$sort_array  = "";
				$mysign  = "";
				$sort_array   = arg_sort($parameter); 
				$mysign = build_mysign($sort_array, ecpay_HashKey, ecpay_HashIV, "MD5");
				
				$sHtml = "<form id='rongpaysubmit' name='rongpaysubmit' action='".ecpay_Credit_URL."' method='POST'>";
				while (list ($key, $val) = each ($parameter)) 
				{
				    $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
				}
				$sHtml = $sHtml."<input type='hidden' name='CheckMacValue' value='".strtoupper($mysign)."'/>";
				$sHtml = $sHtml."<input type='submit' value='付款' style='display:none'></form>";
			    $sHtml = $sHtml."<script>document.forms['rongpaysubmit'].submit();</script>";
				
				echo $sHtml;
				exit;
			}
		}
	

} catch(Exception $e) {
	echo "<center>錯誤！ErrCode：".$ErrCode." ErrMessage：".$e->getMessage()."</center>";
	/*if ($DeviantURL != "") {
		$sHtml = "<form id='Deviant' name='Deviant' action='".$DeviantURL."' method='POST'>";
		$sHtml.= "<input type='hidden' name='ErrCode' value='".$ErrCode."'/>";
		$sHtml.= "<input type='hidden' name='ErrMessage' value='".$e->getMessage()."'/>";
		$sHtml.= "<input type='submit' value='送出'></form>";

		$sHtml.= "<script>document.forms['Deviant'].submit();</script>";
		echo $sHtml;
	}*/
	exit;
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
    ksort($array);
    reset($array);
    return $array;
}

?>