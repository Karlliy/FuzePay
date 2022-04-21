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
//CDbShell::query("SELECT * FROM Firm WHERE BINARY HashKey = '" . $_POST["HashKey"] . "' AND BINARY HashIV = '" . $_POST["HashIV"] . "'");
CDbShell::query("SELECT * FROM Firm WHERE HashKey = '" . $_POST["HashKey"] . "' AND HashIV = '" . $_POST["HashIV"] . "'");
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
    
    
    if (!is_numeric($_POST["Amount"]) || intval($_POST["Amount"]) <= 0) {
        $ErrCode = "9180006";
        throw new exception("請傳入交易金額");
    }
    
    if ($FirmRow["VirtualATMDisburse"] != "-1") {
        if (floatval($_POST["Amount"]) > floatval($FirmRow["VirtualATMDisburse"])) {
            $ErrCode = "9180015";
            throw new exception("交易金額己大於單筆交易額度");
        }
    }

    CDbShell::query("SELECT Sno FROM Ledger WHERE FirmSno = " . $FirmRow["Sno"] . " AND MerTradeID = '" . trim($_POST["MerTradeID"]) . "'");
    if (CDbShell::num_rows() >= 1) {
        $ErrCode = "9180009";
        throw new exception("店家交易編號重複");
    }

    
    
    CDbShell::query("SELECT FC.*, PF.Mode, PF.Kind FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = " . $FirmRow["Sno"] . " AND PF.Type = '12' AND FC.Enable = 1 AND PF.Kind = '小額電信' LIMIT 1");
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
            $FormHtml .= '<input type="hidden" name="filename" id="filename" value="TelecomPayment.php">';
            $FormHtml .= '</form>';
            
            include("VerifyCode.html");
            echo $FormHtml;
            exit;
        }
    }
    Again99:
    //echo $PaymentMode;

    $CashFlowID = Date("ymdHis") . str_pad(floor(microtime() * 10000), 4, '0', STR_PAD_LEFT) . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    if ($_Verified == true) {        
        $sql = "UPDATE smscheck SET CashFlowID = '".$CashFlowID."', Status = 1 WHERE Token = '".$_POST["Token"]."'";
        CDbShell::query($sql);
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
        "State", 
        'NotifyURL', 
        'TakeNumberURL'
    );
    $value = array(
        $FirmRow["Sno"],
        $CashFlowID,
        $_POST['MerTradeID'],
        $_POST['MerProductID'],
        $_POST['MerUserID'],
        "12",
        $PaymentName,
        $_POST['Amount'],
        $Fee,
        $ValidDate,
        $myip,
        $PayModeRow["FeeRatio"],
        "-1",
        $_POST['NotifyURL'],
        $_POST['TakeNumberURL']
    );
    CDbShell::insert("ledger", $field, $value);
    $LedgerId = CDbShell::insert_id();

    if (is_numeric(mb_strpos($PaymentMode, "Sonet", "0", "UTF-8"))) {

    }
    else {
        throw new exception("電信小額未啟用，請接洽".Simplify_Company."，".Simplify_Company."客服專線：".Base_TEL);
    }
}
catch (Exception $e) {
    echo "<center>錯誤！ErrCode：" . $ErrCode . " ErrMessage：" . $e->getMessage() . "</center>";
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