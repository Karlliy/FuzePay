<?php
ini_set('SHORT_OPEN_TAG', "On"); // 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors', "On"); // 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
include_once("BaseClass/Setting.php");
include_once("BaseClass/CDbShell.php");

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
    
    /*
    if (!is_numeric($_POST["Amount"]) || intval($_POST["Amount"]) <= 0) {
        $ErrCode = "9180006";
        throw new exception("請傳入交易金額");
    }*/
    
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
    
    CDbShell::query("SELECT FC.*, PF.Mode, PF.Kind FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = " . $FirmRow["Sno"] . " AND PF.Type = '1' AND FC.Enable = 1 AND PF.Kind = '第一銀行固定' LIMIT 1");
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

    if (is_numeric(mb_strpos($PaymentMode, "第一銀行", "0", "UTF-8"))) {

        $passchars      = array('4','3','2','8','7','6','5','4','3','2');
        
        Again:
        srand((double)microtime()*1000000); 
        $WaterAccount =  str_pad(rand(0, 100000000), 8, '0', STR_PAD_LEFT);

        $chars     = str_split(substr(First_Code.$WaterAccount, -10));

        $x = 0;
        foreach ($chars as $char) {
            //echo $char."|".$passchars[$x] . "|". ($char * $passchars[$x])."<pre />";
            //$CheckCode1 += (($char * $passchars[$x]) % 10);
            $CheckCode1 += ($char * $passchars[$x]);
            
            $x++;
        }

        //echo $CheckCode1."<pre />";
        $_Code1 = ($CheckCode1 / 11);
        //echo $_Code1."|". intval($_Code1)."<pre />";
        $_Code2 = ((intval($_Code1) + 1) * 11) - $CheckCode1;
        //echo $_Code2."<pre />";
        $_Code3 = ($_Code2 % 10);
        //echo $_Code3."<pre />";

        $VatmAccount = First_Code.$WaterAccount. $_Code3;

        CDbShell::query("SELECT Sno FROM ledger WHERE VatmAccount = '" . $VatmAccount . "'");
        if (CDbShell::num_rows() > 0) {
            goto Again;
        }

        $field = array("VatmAccount");
		$value = array($VatmAccount);
		CDbShell::update("ledger", $field, $value, "Sno = '".$LedgerId."'" );

        $OrderNo      = $CashFlowID;
        $MerProductID = $_POST['MerProductID'];
        $MerUserID    = $_POST['MerUserID'];
        $Amount       = $_POST['Amount'];
        $VatmBankCode = "007 (第一銀行)";
        $split        = "-";
        $VatmAccount  = substr($VatmAccount, 0, 4) . $split . substr($VatmAccount, 4, 4) . $split . substr($VatmAccount, 8, 4) . $split . substr($VatmAccount, 12, 4);
        
        $Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=1&MerTradeID=".$_POST["MerTradeID"]."&MerUserID=".$_POST["MerUserID"]."");
            
        $SendPOST["RtnCode"] = "1";
        $SendPOST["RtnMessage"] = "取號成功";
        $SendPOST["MerTradeID"] = $_POST["MerTradeID"];
        $SendPOST["MerProductID"] = $_POST["MerProductID"];
        $SendPOST["MerUserID"] = $_POST["MerUserID"];

        $SendPOST["BankName"] = "第一銀行";
        $SendPOST["VatmBankCode"] = "007";
        $SendPOST["VatmAccount"] = $VatmAccount;
        $SendPOST["Amount"] = $_POST['Amount'];
        $SendPOST["ExpireDatetime"] = $ExpireDatetime ;
        $SendPOST["Validate"] = $Validate;
        
        if (strlen(trim($TakeNumberURL)) != 0 || strlen(trim($_POST['TakeNumberURL'])) != 0) {
            
            try {
                if ($TakeNumberURL != '') {
                    $strReturn = SockPost($TakeNumberURL, $SendPOST, $curlerror);                    
                
                    $fp = fopen('Log/CTBC/TakeNumber_LOG_'.date("YmdHi").'.txt', 'a');
                    fwrite($fp, " ---------------- TakeNumber開始 ---------------- ".PHP_EOL);                
                    fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                    fwrite($fp, "\$strReturn =>".$strReturn.PHP_EOL);
                    fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                    fclose($fp);
                }
                if ($_POST['TakeNumberURL'] != '') {
                    $strReturn = SockPost($_POST['TakeNumberURL'], $SendPOST, $curlerror);                    
                
                    $fp = fopen('Log/CTBC/TakeNumber_LOG_'.date("YmdHi").'.txt', 'a');
                    fwrite($fp, " ---------------- TakeNumber開始 ---------------- ".PHP_EOL);                
                    fwrite($fp, "\$_POST['TakeNumberURL'] =>".$_POST['TakeNumberURL'].PHP_EOL);
                    fwrite($fp, "\$strReturn =>".$strReturn.PHP_EOL);
                    fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                    fclose($fp);
                }
            }
            catch (Exception $e) {  
                
            }
        }else {
            $fp = fopen('Log/CTBC/TakeNumberErr_LOG_'.date("YmdHi").'.txt', 'a');
            fwrite($fp, " ---------------- TakeNumber開始 ---------------- ".PHP_EOL);                
            fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
            fclose($fp);
        }
        if ($_POST['ReturnJosn'] == "Y" || $_POST['ReturnJson'] == "Y") {
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