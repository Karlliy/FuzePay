<?php
ini_set('SHORT_OPEN_TAG', "On"); // 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors', "On"); // 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
include_once("BaseClass/Setting.php");
include_once("BaseClass/CDbShell.php");
/*$fp = fopen('Log/_LOG_'.date("YmdHis").'.txt', 'a');
fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
while (list ($key, $val) = each ($_POST)) 
{
    fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
};	
fclose($fp);*/
//print_r($_POST);
if (strlen(trim($_POST["HashKey"])) < 10 || strlen(trim($_POST["HashIV"])) < 10) {
    echo "<center>錯誤：9180001</center>";
    exit;
}
@CDbShell::connect();
CDbShell::query("SELECT * FROM Firm WHERE BINARY HashKey = '" . $_POST["HashKey"] . "' AND BINARY HashIV = '" . $_POST["HashIV"] . "'");
if (CDbShell::num_rows() != 1) {
    echo "<center>錯誤：9180002</center>";
    exit;
} else {
    $FirmRow = CDbShell::fetch_array();
    
    $SuccessURL     = $FirmRow["SuccessURL"];
    $FailURL        = $FirmRow["FailURL"];
    $TakeNumberURL  = $FirmRow["TakeNumberURL"];
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

if ($FirmRow["FirmCode"] == "601200") {
    define("AllPay_Merchant_ID", "3040635");
    define("AllPay_HashKey", "B4FNY33IwOYAhai9");
    define("AllPay_HashIV", "kdTgiN8Hfa8oWEYx");
}
if ($FirmRow["FirmCode"] == "601400") {
    define("AllPay_Merchant_ID", "3039193");
    define("AllPay_HashKey", "I161bjgG8c0MK3ko");
    define("AllPay_HashIV", "9G1CXxaXleHZ26Ad");
}

try {
    if (strlen(trim($_POST["MerTradeID"])) < 6) {
        $ErrCode = "9180003";
        throw new exception("請傳入店家交易編號");
    }
    
    if (strlen(trim($_POST["MerProductID"])) < 2) {
        $ErrCode = "9180004";
        throw new exception("請傳入店家商品代號");
    }
    if (false == preg_match('/^[A-Za-z0-9\x7f-\xff]{2,}$/', $_POST['MerUserID'])) {
        $ErrCode = "9180005";
        throw new exception("請傳入消費者ID，且消費者ID只能英文或數字組合");
    }
    
    
    // if (!is_numeric($_POST["Amount"]) || intval($_POST["Amount"]) <= 0) {
    //     $ErrCode = "9180006";
    //     throw new exception("請傳入交易金額");
    // }
    
    // if ($FirmRow["VirtualATMDisburse"] != "-1") {
    //     if (floatval($_POST["Amount"]) > floatval($FirmRow["VirtualATMDisburse"])) {
    //         $ErrCode = "9180015";
    //         throw new exception("交易金額己大於單筆交易額度");
    //     }
    // }
    
    /*if (strlen(trim($_POST["TradeDesc"])) < 1) {
        $ErrCode = "9180007";
        throw new exception("請傳入交易描述 ");
    }
    
    if (strlen(trim($_POST["ItemName"])) < 1) {
        $ErrCode = "9180008";
        throw new exception("請傳入商品名稱");
    }*/
    
    CDbShell::query("SELECT * FROM Ledger WHERE FirmSno = " . $FirmRow["Sno"] . " AND MerTradeID = '" . trim($_POST["MerTradeID"]) . "'");
    if (CDbShell::num_rows() >= 1) {
        $ErrCode = "9180009";
        throw new exception("店家交易編號重複");
    }
    
    CDbShell::query("SELECT FC.*, PF.Mode, PF.Kind FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = " . $FirmRow["Sno"] . " AND PF.Type = '1' AND PF.Kind = '虛擬帳號12碼' AND Enable = 1 LIMIT 1");
    $PayModeRow = CDbShell::fetch_array();
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
    } else {
        $ErrCode = "9180010";
        throw new exception("未設定系統介接，請接洽".Simplify_Company."，".Simplify_Company."客服專線：".Base_TEL);
    }
    Again99:
    //echo $PaymentMode;

    $CashFlowID = Date("ymdHis") . str_pad(floor(microtime() * 10000), 4, '0', STR_PAD_LEFT) . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
    if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $myip = $_SERVER['REMOTE_ADDR'];
    } else {
        $myip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $myip = $myip[0];
    }
    
    $ValidDate = date('Y-m-d', strtotime(date('Y-m-d') . " +1 day"));
    $field = array(
        "FirmSno",
        "CashFlowID",
        "MerTradeID",
        "MerProductID",
        "MerUserID",
        "PaymentType",
        "PaymentName",
        "Total",
        "Fee",
        "ValidDate",
        "IP",
        "FeeRatio",
        "State"
    );
    $value = array(
        $FirmRow["Sno"],
        $CashFlowID,
        $_POST['MerTradeID'],
        $_POST['MerProductID'],
        $_POST['MerUserID'],
        "1",
        $PaymentName,
        $_POST['Amount'],
        $Fee,
        $ValidDate,
        $myip,
        $PayModeRow["FeeRatio"],
        "-1"
    );
    CDbShell::insert("ledger", $field, $value);
    $LedgerId = CDbShell::insert_id();
    if (is_numeric(mb_strpos($PaymentMode, "凱基", "0", "UTF-8"))) {
        $passchars  = array('3','7','1','3','7','1','3','7','1','3','7','1','3');
        Again6:
        $WaterAccount =  str_pad(rand(0, 10000000), 7, '0', STR_PAD_LEFT);
        
        if ($FirmRow["Sno"] == 65) {
            $ExpireDatetime = date('Y-m-d', strtotime(date('Y-m-d') . " +1 day"));
        }else {
            $ExpireDatetime = date('Y-m-d', strtotime(date('Y-m-d') . " +3650 day"));
        }

        #$GetYear = mb_substr((date("Y", strtotime($ExpireDatetime))-1911), -1);
        // $GetYear = mb_substr(date("Y"), -1);
        // $_ExpireDay = date("z", strtotime($ExpireDatetime)) + 1;
        // $_ExpireDay = str_pad($_ExpireDay, 3, '0', STR_PAD_LEFT);
        // $chars     = str_split(KGI12_Code.$WaterAccount);

        // $x = 0;
        // foreach ($chars as $char) {
        //     #echo $char."|".$passchars[$x] . "|". (($char * $passchars[$x]) % 10)."<pre />";
        //     $CheckCode1 += (($char * $passchars[$x]) % 10);
            
        //     $x++;
        // }

        // $_CheckCode1 = ($CheckCode1 % 10);

        // $passchars  = array('8', '7', '6', '5', '4', '3', '2', '1');
        // $chars      = str_split(str_pad($_POST['Amount'], 8, '0', STR_PAD_LEFT));
                
        // $x = 0;
        // foreach ($chars as $char) {
        //     #echo $char."|".$passchars[$x] . "|". (($char * $passchars[$x]) % 10)."<pre />";
        //     $CheckCode2 += @($char * $passchars[$x]);  
        //     //fwrite($fp, "\$CheckCode2 =>".$char." * ".$passchars[$x].PHP_EOL);
        //     $x++;
        // }

        // $_CheckCode2 = ($CheckCode2 % 10);

        // $CheckCode = $_CheckCode1 + $_CheckCode2;

        // $_Weights = ($CheckCode % 10);        

        // if ($_Weights == 0) {
        //     $_CheckCode = 0;
        // }else {
        //     $_CheckCode = 10 - $_Weights;
        // }
        
        //var_dump($_CheckCode ."|".$CheckCode ."|". $_CheckCode1 ."|". $_CheckCode2);

        //$VatmAccount = KGI_Code.$GetYear.$_ExpireDay.$WaterAccount.$_CheckCode;       
        $VatmAccount = KGI12_Code.$WaterAccount;   
        $VatmAccount2 = $VatmAccount;
        $sql = "SELECT Sno FROM ledger WHERE VatmAccount = '" . $VatmAccount . "'";
        CDbShell::query($sql);
        if (CDbShell::num_rows() > 0) {
            goto Again6;
        }

        $field = array("VatmAccount");
		$value = array($VatmAccount);
		CDbShell::update("ledger", $field, $value, "Sno = '".$LedgerId."'" );

        $OrderNo      = $CashFlowID;
        $MerProductID = $_POST['MerProductID'];
        $MerUserID    = $_POST['MerUserID'];
        $Amount       = $_POST['Amount'];
        $VatmBankCode = "809 (凱基銀行)";
        $split        = "-";
        //$VatmAccount  = substr($VatmAccount, 0, 4) . $split . substr($VatmAccount, 4, 4) . $split . substr($VatmAccount, 8, 4) . $split . substr($VatmAccount, 12, 4);
        $VatmAccount  = substr($VatmAccount, 0, 4) . $split . substr($VatmAccount, 4, 4) . $split . substr($VatmAccount, 8, 4);
        
        $Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=1&MerTradeID=".$_POST["MerTradeID"]."&MerUserID=".$_POST["MerUserID"]."");
            
        $SendPOST["RtnCode"] = "1";
        $SendPOST["RtnMessage"] = "取號成功";
        $SendPOST["MerTradeID"] = $_POST["MerTradeID"];
        $SendPOST["MerProductID"] = $_POST["MerProductID"];
        $SendPOST["MerUserID"] = $_POST["MerUserID"];

        $SendPOST["BankName"] = "凱基銀行";
        $SendPOST["VatmBankCode"] = "809";
        $SendPOST["VatmAccount"] = $VatmAccount;
        $SendPOST["Amount"] = $_POST['Amount'];
        $SendPOST["ExpireDatetime"] = $ExpireDatetime ;
        $SendPOST["Validate"] = $Validate;
        
        if (strlen(trim($TakeNumberURL)) != 0) {
            
            try {
                $strReturn = SockPost($TakeNumberURL, $SendPOST, $curlerror);         
                
                $fp = fopen('Log/KGI/TakeNumber_LOG_'.date("YmdHi").'.txt', 'a');
                fwrite($fp, " ---------------- TakeNumber開始 ---------------- ".PHP_EOL);                
                fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                fwrite($fp, "\$strReturn =>".$strReturn.PHP_EOL);
                fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                fclose($fp);
            }
            catch (Exception $e) {  
                
            }
        }else {
            $fp = fopen('Log/KGI/TakeNumberErr_LOG_'.date("YmdHi").'.txt', 'a');
            fwrite($fp, " ---------------- TakeNumber開始 ---------------- ".PHP_EOL);                
            fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
            fclose($fp);
        }
        if ($_POST['ReturnJosn'] == "Y") {
            echo json_encode($SendPOST);
        }else {
            include("ATMPay.html");
        }
        exit;    
    }else {
        throw new exception("虛擬帳戶未啟用，請接洽".Simplify_Company."，".Simplify_Company."客服專線：".Base_TEL);
    }
}
catch (Exception $e) {
    echo "<center>錯誤！ErrCode：" . $ErrCode . " ErrMessage：" . $e->getMessage() . "</center>";
    /*if ($DeviantURL != "") {
    $sHtml = "<form id= 'Deviant' name='Deviant' action='".$DeviantURL."' method='POST'>";
    $sHtml.= "<input type='hidden' name='ErrCode' value='".$ErrCode."'/>";
    $sHtml.= "<input type='hidden' name='ErrMessage' value='".$e->getMessage()."'/>";
    $sHtml.= "<input type='submit' value='送出'></form>";
    
    $sHtml.= "<script>document.forms['Deviant'].submit();</script>";
    echo $sHtml;
    }*/
    exit;
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
function build_mysign($sort_array, $HashKey, $HashIV, $sign_type = "MD5")
{
    $prestr = create_linkstring($sort_array);
    $prestr = "HashKey=" . $HashKey . "&" . $prestr . "&HashIV=" . $HashIV;
    //echo $prestr;
    //exit;
    $prestr = strtolower(urlencode($prestr));
    //echo $prestr;
    //exit;
    $mysgin = sign($prestr, $sign_type);
    return $mysgin;
}

function create_linkstring($array)
{
    $arg = "";
    while (list($key, $val) = each($array)) {
        $arg .= $key . "=" . $val . "&";
    }
    $arg = substr($arg, 0, count($arg) - 2); //去掉最后一个&字符
    return $arg;
}

function sign($prestr, $sign_type)
{
    $sign = '';
    if ($sign_type == 'MD5') {
        $sign = md5($prestr);
    } else {
        die("暂不支持" . $sign_type . "类型的签名方式");
    }
    return $sign;
}

function arg_sort($array)
{
    ksort($array);
    reset($array);
    return $array;
}

function create_mpg_aes_encrypt($parameter = "", $key = "", $iv = "")
{
    $md5str = "";
    
    ksort($parameter);
    
    foreach ($parameter as $key => $val) {
        $md5str = $md5str . $key . "=" . $val . "&";
    }
    
    $fp = fopen('Log/Cathay/Spgate_ATMNumber_LOG_' . date("YmdHis") . '.txt', 'a');
    fwrite($fp, " ---------------- 開始POST ---------------- \n\r");
    fwrite($fp, $md5str . "\n\r");
    fwrite($fp, " ---------------- 結束POST ---------------- \n\r");
    fclose($fp);
    /**
    $return_str = '';
    if (!empty($parameter)) {
    //將參數經過 URL ENCODED QUERY STRING 
    $return_str = http_build_query($parameter);
    //echo $iv;
    //echo "<br />";
    }
    return trim(bin2hex(openssl_encrypt(addpadding($return_str), 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv)));
    **/
}

function create_mpg_aes_encrypt2($parameter = "", $key = "", $iv = "")
{
    $return_str = '';
    if (!empty($parameter)) {
        //將參數經過 URL ENCODED QUERY STRING 
        $return_str = http_build_query($parameter);
        //echo $iv;
        //echo "<br />";
    }
    return trim(bin2hex(openssl_encrypt(addpadding($return_str), 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv)));
}

function addpadding($string, $blocksize = 32)
{
    $len = strlen($string);
    $pad = $blocksize - ($len % $blocksize);
    $string .= str_repeat(chr($pad), $pad);
    //echo $string;
    return $string;
}

?>