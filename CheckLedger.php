<?php
header('Content-Type: text/html; charset=utf-8');
include_once("BaseClass/Setting.php");
include_once("BaseClass/CDbShell.php");
//print_r($_POST);
if (strlen(trim($_POST["HashKey"])) < 10 || strlen(trim($_POST["HashIV"])) < 10) {
	echo "<center>錯誤：7030001</center>";
	exit;
}
@CDbShell::connect();
CDbShell::query("SELECT * FROM Firm WHERE BINARY HashKey = '".$_POST["HashKey"]."' AND BINARY HashIV = '".$_POST["HashIV"]."'"); 
if (CDbShell::num_rows() != 1) {
	echo "<center>錯誤：7030002</center>";
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

if (strlen(trim($_POST["MerTradeID"])) == 0) {
	echo "<center>錯誤：7030003</center>";
	exit;
}

try {
	
	CDbShell::query("SELECT L.* FROM Ledger AS L INNER JOIN Firm AS F ON F.Sno = L.FirmSno WHERE HashKey = '".$_POST["HashKey"]."' AND BINARY HashIV = '".$_POST["HashIV"]."' AND L.MerTradeID = '".$_POST["MerTradeID"]."'"); 	
	if (CDbShell::num_rows() == 0) {
		$ErrCode = "7030004";
		throw new exception("查詢不到店家交易編號資料");
	}
	$LedgerRow = CDbShell::fetch_array();
	
	if (intval($LedgerRow["ClosingTotal"]) > 0) {
		$RtnCode = 1;
	}else {
		$RtnCode = $LedgerRow["ResultCode"];
	}
	
	switch ($LedgerRow["State"]) {
		case "-3":
			$TradeState = "-3";
			$RtnMessage = "已退款";
			break;
		case "-2":
			$TradeState = "-2";
			$RtnMessage = "退款處理中";
			break;
		case "-1":
			$TradeState = "-1";
			$RtnMessage = "未完成交易";
			break;
		case "0":
		case "1":
			$TradeState = "1";
			$RtnMessage = $LedgerRow["ResultMesg"];
			break;
		case "2":
			$TradeState = "-1";
			$RtnMessage = "未入款[".$LedgerRow["ResultMesg"]."]";
			break;
	}
	$parameter = array(
		"RtnCode"				=> $RtnCode,
		"RtnMessage"			=> $RtnMessage,
		"TradeState"			=> $TradeState,
		"MerTradeID"			=> $LedgerRow["MerTradeID"],
		"MerProductID"			=> $LedgerRow["MerProductID"],
		"MerUserID"				=> $LedgerRow["MerUserID"],
		"Amount"				=> ((intval($LedgerRow["ClosingTotal"]) == 0) ? intval($LedgerRow["Total"]) : intval($LedgerRow["ClosingTotal"])),
		"TransactionDate"		=> (($LedgerRow["TransactionDate"] > '1911-01-01') ? $LedgerRow["TransactionDate"] : $LedgerRow["CreationDate"] ),
		"PaymentDate"			=> $LedgerRow["PaymentDate"]
	);	
	
	echo json_encode($parameter, JSON_UNESCAPED_UNICODE );
	exit;
} catch(Exception $e) {
	echo "<center>錯誤：".$ErrCode."(".$e->getMessage().")</center>";
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

?>