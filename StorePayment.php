<?php
ini_set('SHORT_OPEN_TAG', 'On');                // 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors', 'On');                // 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
include_once 'BaseClass/Setting.php';
include_once 'BaseClass/CDbShell.php';

//print_r($_POST);

//echo "<center>錯誤：此支付方式還沒開始</center>";
//exit;
//var_dump($_POST);
if (strlen(trim($_POST['HashKey'])) < 10 || strlen(trim($_POST['HashIV'])) < 10) {
    echo '<center>錯誤：9180001</center>';
    exit;
}
@CDbShell::connect();
CDbShell::query("SELECT * FROM Firm WHERE BINARY HashKey = '".$_POST['HashKey']."' AND BINARY HashIV = '".$_POST['HashIV']."'");
if (1 != CDbShell::num_rows()) {
    echo '<center>錯誤：9180002</center>';
    exit;
} else {
    $FirmRow = CDbShell::fetch_array();
}

if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $myip = $_SERVER['REMOTE_ADDR'];
} else {
    $myip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $myip = $myip[2];
}

if ('' != $FirmRow['RefusalIP']) {
    $RefusalIP = mb_split('##', $FirmRow['RefusalIP']);
    if (is_numeric(array_search($myip, $RefusalIP))) {
        echo '<center>錯誤：9180013</center>';
        exit;
    }
}

$SuccessURL     = $FirmRow["SuccessURL"];
$FailURL        = $FirmRow["FailURL"];
$TakeNumberURL  = $FirmRow["TakeNumberURL"];

try {
    if (strlen(trim($_POST['MerTradeID'])) < 6) {
        $ErrCode = '9180003';
        throw new exception('請傳入店家交易編號');
    }

    if (strlen(trim($_POST['MerProductID'])) < 2) {
        $ErrCode = '9180004';
        throw new exception('請傳入店家商品代號');
    }
    if (false == preg_match('/^[A-Za-z0-9\x7f-\xff]{2,}$/', $_POST['MerUserID'])) {
        $ErrCode = '9180005';
        throw new exception('請傳入消費者ID，且消費者ID只能中文英文或數字組合');
    }

    if (!is_numeric($_POST['Amount']) || intval($_POST['Amount']) < 35) {
        $ErrCode = '9180006';
        throw new exception('請傳入交易金額或金額小於35元');
    }

    if (intval($_POST['Amount']) > 20000) {
        $ErrCode = '9180007';
        throw new exception('超商繳費金額不可超過20,000元');
    }

    /*if (strlen(trim($_POST['TradeDesc'])) < 1) {
        $ErrCode = '9180007';
        throw new exception('請傳入交易描述 ');
    }

    if (strlen(trim($_POST['ItemName'])) < 1) {
        $ErrCode = '9180008';
        throw new exception('請傳入商品名稱');
    }*/

    /*if ('Family' != $_POST['ChoosePayment'] && 'OK' != $_POST['ChoosePayment'] && "711" != $_POST['ChoosePayment'] && "HiLife" != $_POST['ChoosePayment']) {
        $ErrCode = '9180011';
        throw new exception('請傳入正確繳費超商代碼');
    }*/

    CDbShell::query('SELECT * FROM Ledger WHERE FirmSno = '.$FirmRow['Sno']." AND MerTradeID = '".trim($_POST['MerTradeID'])."'");
    if (CDbShell::num_rows() >= 1) {
        $ErrCode = '9180009';
        throw new exception('店家交易編號重複');
    }

    switch ($_POST['ChoosePayment']) {
        case '711':
            $PaymentType = '3';
            $PaymentName = '超商繳費 7-11';
            break;
        case 'Family':
            $PaymentType = '4';
            $PaymentName = '超商繳費 全家';
            break;
        case 'OK':
            $PaymentType = '5';
            $PaymentName = '超商繳費 OK';
            break;
        case 'HiLife':
            $PaymentType = '6';
            $PaymentName = '超商繳費 Hi-Life';
            break;
        // default:    #藍新
        //     $PaymentType = '2';
        //     break;
        default:    #藍新
            $PaymentType = '3';
            $PaymentName = '超商繳費 7-11';
            break;
    }
        

    CDbShell::query('SELECT FC.*, PF.Kind, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = '.$FirmRow['Sno']." AND PF.Type = '".$PaymentType."' AND FC.Enable = 1 AND (FC.FeeRatio > 0 OR FC.FixedFee) LIMIT 1");
    $PayModeRow = CDbShell::fetch_array();
    if (CDbShell::num_rows() >= 1) {
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
        $ErrCode = '9180010';
        throw new exception('未設定系統介接，請接洽'.Simplify_Company.'，'.Simplify_Company.'客服專線：'.Base_TEL);
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
            while (list ($key, $val) = each ($_POST)) 
            {
                $FormHtml .= '<input type="hidden" name="'.$key.'" id="'.$key.'" value="'.$val.'">';
            };	
            $FormHtml .= '<input type="hidden" name="filename" id="filename" value="StorePayment.php">';
            $FormHtml .= '</form>';
            
            include("VerifyCode.html");
            echo $FormHtml;
            exit;
        }
    }

    $ValidDate = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s'), date('m'), date('d') + 2, date('Y')));
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
    $CashFlowID = date('ymdHis').str_pad(floor(microtime() * 10000), 4, '0', STR_PAD_LEFT).str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

    if ($_Verified == true) {        
        $sql = "UPDATE smscheck SET CashFlowID = '".$CashFlowID."', Status = 1 WHERE Token = '".$_POST["Token"]."'";
        CDbShell::query($sql);
    }
    $PaymentMode = $PayModeRow['Mode'];
    $PaymentName = $PayModeRow["Kind"] . "-". $PayModeRow["Mode"];    

    $field = array('FirmSno', 'CashFlowID', 'MerTradeID', 'MerProductID', 'MerUserID', 'PaymentType', 'PaymentName', 'Total', 'Fee', 'ValidDate', 'IP', 'FeeRatio', 'State', 'NotifyURL', 'TakeNumberURL');
    $value = array($FirmRow['Sno'], $CashFlowID, $_POST['MerTradeID'], $_POST['MerProductID'], $_POST['MerUserID'], $PaymentType, $PaymentName, $_POST['Amount'], $Fee, $ValidDate, $myip, $FirmRow['FeeRatio'], '-1' , $_POST['NotifyURL'], $_POST['TakeNumberURL']);
    CDbShell::insert('ledger', $field, $value);
    $LedgerId = CDbShell::insert_id();
    
    if ($_POST['ChoosePayment'] == "711" || (trim($_POST['ChoosePayment']) != "OK" && trim($_POST['ChoosePayment']) != "Family")) {
        $_ExpireDate = date('Y-m-d 23:59:59', strtotime(date('Ymd') . " +1 day"));
        //GetRandom(9, $Water);

        if ($PaymentMode == "711") {
            Again:
            $Water = str_pad(rand(0, 1000000000), 9, '0', STR_PAD_LEFT);
            $VatmAccount = "YAN".date("md").$Water;
            $sql = "SELECT Sno FROM ledger WHERE VatmAccount = '" . $VatmAccount . "'";
            CDbShell::query($sql);
            if (CDbShell::num_rows() > 0) {
                goto Again;
            }

            $field = array('VatmAccount', 'ExpireDatetime');
            $value = array($VatmAccount, $_ExpireDate);
            CDbShell::update('ledger', $field, $value, "Sno = '".$LedgerId."'");

            $Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=1&MerTradeID=".$_POST["MerTradeID"]."&MerUserID=".$_POST["MerUserID"]."");
                
            $SendPOST['RtnCode'] = '1';
            $SendPOST['RtnMessage'] = '取號成功';
            $SendPOST['MerTradeID'] = $_POST['MerTradeID'];
            $SendPOST['MerProductID'] = $_POST['MerProductID'];
            $SendPOST['MerUserID'] = $_POST['MerUserID'];

            $SendPOST['Amount'] = intval($_POST['Amount']);
            $SendPOST['ExpireDatetime'] = $_ExpireDate;
            $SendPOST['Store'] = "711";
            $SendPOST["CodeNo"] = $VatmAccount;
            $SendPOST['Validate'] = $Validate;

            if ($_POST['ReturnJosn'] == "Y") {
                echo json_encode($SendPOST);
            }else {
                include("StorePayFor711.html");
            }

            if ($TakeNumberURL != '' || $_POST['TakeNumberURL'] != '') {
                    
                try {
                    $fp = fopen('Log/711/Send_TakeNumber_LOG_'.date("YmdHi").'.txt', 'a');
                    fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                    fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                    fwrite($fp, "\$_POST['TakeNumberURL'] =>".$_POST['TakeNumberURL'].PHP_EOL);
                    while (list ($key, $val) = each ($SendPOST)) 
                    {
                        fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                    };
                    
                    if ($TakeNumberURL != '') {
                        $strReturn = SockPost($TakeNumberURL, $SendPOST, $curlerror);
                    }
                    if ($_POST['TakeNumberURL'] != '') {
                        $strReturn = SockPost($_POST['TakeNumberURL'], $SendPOST, $curlerror);
                    }
                    
                    fwrite($fp, "\$strReturn =>".$strReturn.PHP_EOL);
                    fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                    fclose($fp);
                } catch (Exception $e) {
        
                    $fp = fopen('Log/711/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                    fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                    fwrite($fp, "\$SuccessURL =>".$SuccessURL.PHP_EOL);
                    while (list ($key, $val) = each ($SendPOST)) 
                    {
                        fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                    };
                    fwrite($fp, "\$strReturn =>".$e->getMessage().PHP_EOL);
                    fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                    fclose($fp);
                }
            }else {
                $fp = fopen('Log/711/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                fwrite($fp, "\$strReturn => 回傳網址是空的".PHP_EOL);
                fclose($fp);
            }
        }else {
            $parameters = array(
                "merchantNo"		=> Lihuo_Id,
                "tradeType"			=> 3,	
                "orderNo"		    => $CashFlowID,
                "amount"			=> floatval($_POST["Amount"]),
                "payname"           => ((strlen(trim($_POST["MerUserID"]))) <= 1 ? "消費者ID":$_POST["MerUserID"] )
            );

            $sign = md5(base64_encode("amount".floatval($_POST["Amount"])."merchantNo".Lihuo_Id."orderNo".$CashFlowID."tradeType3").Lihuo_Key);
            $parameters['sign'] = $sign;
            $strReturn = SockPost(Lihuo_URL, $parameters, $curlerror);
            $obj = json_decode($strReturn);
            if ($obj->code == 1) {
                $field = array('VatmAccount');
                $value = array($obj->payCode);
                CDbShell::update('ledger', $field, $value, "Sno = '".$LedgerId."'");
                $VatmAccount = $obj->payCode;
                $Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=1&MerTradeID=".$_POST["MerTradeID"]."&MerUserID=".$_POST["MerUserID"]."");
                    
                $SendPOST['RtnCode'] = '1';
                $SendPOST['RtnMessage'] = '取號成功';
                $SendPOST['MerTradeID'] = $_POST['MerTradeID'];
                $SendPOST['MerProductID'] = $_POST['MerProductID'];
                $SendPOST['MerUserID'] = $_POST['MerUserID'];
        
                $SendPOST['Amount'] = intval($_POST['Amount']);
                $SendPOST['ExpireDatetime'] = $_ExpireDate;
                $SendPOST['Store'] = "711";
                $SendPOST["CodeNo"] = $obj->payCode;
                $SendPOST['Validate'] = $Validate;
        
                if ($_POST['ReturnJosn'] == "Y") {
                    echo json_encode($SendPOST);
                }else {
                    include("StorePayFor711.html");
                }
        
                if ($TakeNumberURL != '' || $_POST['TakeNumberURL'] != '') {
                        
                    try {
                        $fp = fopen('Log/Lihuo/Send_TakeNumber_LOG_'.date("YmdHi").'.txt', 'a');
                        fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                        fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                        fwrite($fp, "\$_POST['TakeNumberURL'] =>".$_POST['TakeNumberURL'].PHP_EOL);
                        while (list ($key, $val) = each ($SendPOST)) 
                        {
                            fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                        };
            
                        if ($TakeNumberURL != '') {
                            $strReturn = SockPost($TakeNumberURL, $SendPOST, $curlerror);
                        }
                        if ($_POST['TakeNumberURL'] != '') {
                            $strReturn = SockPost($_POST['TakeNumberURL'], $SendPOST, $curlerror);
                        }                        
                        
                        fwrite($fp, "\$strReturn =>".$strReturn.PHP_EOL);
                        fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                        fclose($fp);
                    } catch (Exception $e) {
            
                        $fp = fopen('Log/Lihuo/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                        fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                        fwrite($fp, "\$SuccessURL =>".$SuccessURL.PHP_EOL);
                        while (list ($key, $val) = each ($SendPOST)) 
                        {
                            fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                        };
                        fwrite($fp, "\$strReturn =>".$e->getMessage().PHP_EOL);
                        fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                        fclose($fp);
                    }
                }else {
                    $fp = fopen('Log/Lihuo/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                    fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                    fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                    fwrite($fp, "\$strReturn => 回傳網址是空的".PHP_EOL);
                    fclose($fp);
                }
            }else {
                echo $obj->msg;
                exit;
            }
        }
    }else if ($_POST['ChoosePayment'] == "Family") {
        $_ExpireDate = date('Y-m-d 23:59:59', strtotime(date('Ymd') . " +1 day"));
            
        if ($PaymentMode == "全家") {
            //測試URL
            //$client = new SoapClient("https://ect.familynet.com.tw/pin/webec.asmx?wsdl");
            //正式URL
            $client = new SoapClient("https://ec.famiport.com.tw/pin/webec.asmx?wsdl");

            $parameter = array(
                "TX_WEB" 	    => array(
                    'HEADER' => array(
                        "XML_VER"   => "05.01",
                        "XML_FROM"  => "83438598",
                        "TERMINO"   => "QKKUKKU",
                        "XML_TO"    => "99027",
                        "BUSINESS"  => "B000001",
                        "XML_DATE"  => DATE("Ymd"),
                        "XML_TIME"  => DATE("His"),
                        "STATCODE"  => "0000"
                    ),
                    'AP' => array(
                        "ORDER_NO"  => $CashFlowID,
                        "ACCOUNT"   =>  str_pad(intval($_POST["Amount"]),5,'0',STR_PAD_LEFT),
                        "END_DATE"  => DATE("Ymd" , strtotime($_ExpireDate)),
                        "END_TIME"  => DATE("His" , strtotime($_ExpireDate)),
                        "PAY_TYPE"  => "FamiPay",
                        "PRD_DESC"  => "Pay",
                        "PAY_COMP"  => "揚盛",
                        "TRADE_TYPE"  => "1"
                        ),
                ),
                "ACCOUNT_NO"	=> "fuzepay",
                "PASSWORD"      => "f7wp1vq469ul"
            );
            $result = $client->NewOrder($parameter);

            if ($result->NewOrderResult == "0000") {

                $field = array('VatmAccount', 'ExpireDatetime');
                $value = array($result->TX_WEB->AP->PIN_CODE, $_ExpireDate);
                CDbShell::update('ledger', $field, $value, "Sno = '".$LedgerId."'");
                
                $VatmAccount = $result->TX_WEB->AP->PIN_CODE;
    
                $Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=1&MerTradeID=".$_POST["MerTradeID"]."&MerUserID=".$_POST["MerUserID"]."");
                
                $SendPOST['RtnCode'] = '1';
                $SendPOST['RtnMessage'] = '取號成功';
                $SendPOST['MerTradeID'] = $_POST['MerTradeID'];
                $SendPOST['MerProductID'] = $_POST['MerProductID'];
                $SendPOST['MerUserID'] = $_POST['MerUserID'];
        
                $SendPOST['Amount'] = intval($result->TX_WEB->AP->ACCOUNT);
                $SendPOST['ExpireDatetime'] = $_ExpireDate;
                $SendPOST['Store'] = "Family";
                $SendPOST["CodeNo"] = $result->TX_WEB->AP->PIN_CODE;
                $SendPOST['Validate'] = $Validate;
    
                if ($_POST['ReturnJosn'] == "Y") {
                    echo json_encode($SendPOST);
                }else {
                    include("StorePayForFamily.html");
                }
    
                if ($TakeNumberURL != '' || $_POST['TakeNumberURL'] != '') {
                    
                    try {
                        $fp = fopen('Log/Family/Send_TakeNumber_LOG_'.date("YmdHi").'.txt', 'a');
                        fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                        fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                        fwrite($fp, "\$_POST['TakeNumberURL'] =>".$_POST['TakeNumberURL'].PHP_EOL);
                        while (list ($key, $val) = each ($SendPOST)) 
                        {
                            fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                        };
            
                        if ($TakeNumberURL != '') {
                            $strReturn = SockPost($TakeNumberURL, $SendPOST, $curlerror);
                        }
                        if ($_POST['TakeNumberURL'] != '') {
                            $strReturn = SockPost($_POST['TakeNumberURL'], $SendPOST, $curlerror);
                        }
                        
                        fwrite($fp, "\$strReturn =>".$strReturn.PHP_EOL);
                        fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                        fclose($fp);
                    } catch (Exception $e) {
            
                        $fp = fopen('Log/Family/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                        fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                        fwrite($fp, "\$SuccessURL =>".$SuccessURL.PHP_EOL);
                        while (list ($key, $val) = each ($SendPOST)) 
                        {
                            fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                        };
                        fwrite($fp, "\$strReturn =>".$e->getMessage().PHP_EOL);
                        fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                        fclose($fp);
                    }
                }else {
                    $fp = fopen('Log/Family/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                    fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                    fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                    fwrite($fp, "\$strReturn => 回傳網址是空的".PHP_EOL);
                    fclose($fp);
                }
            }else {
                echo $result->NewOrderResult . $result->TX_WEB->HEADER->STATDESC;
            }
        }else {
            $parameters = array(
                "merchantNo"		=> Lihuo_Id,
                "tradeType"			=> 3,	
                "orderNo"		    => $CashFlowID,
                "amount"			=> floatval($_POST["Amount"]),
                "payname"           => ((strlen(trim($_POST["MerUserID"]))) <= 1 ? "消費者ID":$_POST["MerUserID"] )
            );

            $sign = md5(base64_encode("amount".floatval($_POST["Amount"])."merchantNo".Lihuo_Id."orderNo".$CashFlowID."tradeType3").Lihuo_Key);
            $parameters['sign'] = $sign;
            $strReturn = SockPost(Lihuo_URL, $parameters, $curlerror);
            $obj = json_decode($strReturn);
            if ($obj->code == 1) {
                $field = array('VatmAccount');
                $value = array($obj->payCode);
                CDbShell::update('ledger', $field, $value, "Sno = '".$LedgerId."'");
                $VatmAccount = $obj->payCode;
                $Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=1&MerTradeID=".$_POST["MerTradeID"]."&MerUserID=".$_POST["MerUserID"]."");
                    
                $SendPOST['RtnCode'] = '1';
                $SendPOST['RtnMessage'] = '取號成功';
                $SendPOST['MerTradeID'] = $_POST['MerTradeID'];
                $SendPOST['MerProductID'] = $_POST['MerProductID'];
                $SendPOST['MerUserID'] = $_POST['MerUserID'];
        
                $SendPOST['Amount'] = intval($_POST['Amount']);
                $SendPOST['ExpireDatetime'] = $_ExpireDate;
                $SendPOST['Store'] = "Family";
                $SendPOST["CodeNo"] = $obj->payCode;
                $SendPOST['Validate'] = $Validate;
        
                if ($_POST['ReturnJosn'] == "Y") {
                    echo json_encode($SendPOST);
                }else {
                    include("StorePayForFamily.html");
                }
        
                if ($TakeNumberURL != '' || $_POST['TakeNumberURL'] != '') {
                        
                    try {
                        $fp = fopen('Log/Lihuo/Send_TakeNumber_LOG_'.date("YmdHi").'.txt', 'a');
                        fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                        fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                        fwrite($fp, "\$_POST['TakeNumberURL'] =>".$_POST['TakeNumberURL'].PHP_EOL);
                        while (list ($key, $val) = each ($SendPOST)) 
                        {
                            fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                        };
            
                        if ($TakeNumberURL != '') {
                            $strReturn = SockPost($TakeNumberURL, $SendPOST, $curlerror);
                        }
                        if ($_POST['TakeNumberURL'] != '') {
                            $strReturn = SockPost($_POST['TakeNumberURL'], $SendPOST, $curlerror);
                        }
                        
                        fwrite($fp, "\$strReturn =>".$strReturn.PHP_EOL);
                        fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                        fclose($fp);
                    } catch (Exception $e) {
            
                        $fp = fopen('Log/Lihuo/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                        fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                        fwrite($fp, "\$SuccessURL =>".$SuccessURL.PHP_EOL);
                        while (list ($key, $val) = each ($SendPOST)) 
                        {
                            fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                        };
                        fwrite($fp, "\$strReturn =>".$e->getMessage().PHP_EOL);
                        fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                        fclose($fp);
                    }
                }else {
                    $fp = fopen('Log/Lihuo/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                    fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                    fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                    fwrite($fp, "\$strReturn => 回傳網址是空的".PHP_EOL);
                    fclose($fp);
                }
            }else {
                echo $obj->msg;
                exit;
            }
        }
    }elseif ($_POST['ChoosePayment'] == "OK") {
        $_ExpireDate   = date('Y-m-d 23:59:59', strtotime(date('Ymd') . " +7 day"));
        Again3:
        $VatmAccount = "FE".substr(date("Y"), -1).date("md") . str_pad(rand(0, 10000000), 7, '0', STR_PAD_LEFT);

        $sql = "SELECT Sno FROM ledger WHERE VatmAccount = '" . $VatmAccount . "'";
        CDbShell::query($sql);
        if (CDbShell::num_rows() > 0) {
            goto Again3;
        }
        
        $field = array("VatmAccount");
		$value = array($VatmAccount);
		CDbShell::update("ledger", $field, $value, "Sno = '".$LedgerId."'" );

        $OrderNo = $CashFlowID;
		$MerProductID = $_POST['MerProductID'];
		$MerUserID = $_POST['MerUserID'];
        $Amount = $_POST['Amount'];
        
        $Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=1&MerTradeID=".$_POST["MerTradeID"]."&MerUserID=".$_POST["MerUserID"]."");
            
        $SendPOST['RtnCode'] = '1';
        $SendPOST['RtnMessage'] = '取號成功';
        $SendPOST['MerTradeID'] = $_POST['MerTradeID'];
        $SendPOST['MerProductID'] = $_POST['MerProductID'];
        $SendPOST['MerUserID'] = $_POST['MerUserID'];

        $SendPOST['Amount'] = intval($_POST['Amount']);
        $SendPOST['ExpireDatetime'] = $_ExpireDate;
        $SendPOST['Store'] = "OK";
        $SendPOST["CodeNo"] = $VatmAccount;
        $SendPOST['Validate'] = $Validate;
    
        if ($_POST['ReturnJosn'] == "Y") {
            echo json_encode($SendPOST);
        }else {
            include("StorePay3.html");
        }

        if ($TakeNumberURL != '' || $_POST['TakeNumberURL'] != '') {
            
            try {
                $fp = fopen('Log/OKMart/Send_TakeNumber_LOG_'.date("YmdHi").'.txt', 'a');
                fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                fwrite($fp, "\$_POST['TakeNumberURL'] =>".$_POST['TakeNumberURL'].PHP_EOL);
                while (list ($key, $val) = each ($SendPOST)) 
                {
                    fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                };
    
                if ($TakeNumberURL != '') {
                    $strReturn = SockPost($TakeNumberURL, $SendPOST, $curlerror);
                }
                if ($_POST['TakeNumberURL'] != '') {
                    $strReturn = SockPost($_POST['TakeNumberURL'], $SendPOST, $curlerror);
                }
                
                fwrite($fp, "\$strReturn =>".$strReturn.PHP_EOL);
                fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                fclose($fp);
            } catch (Exception $e) {
    
                $fp = fopen('Log/OKMart/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                fwrite($fp, "\$SuccessURL =>".$SuccessURL.PHP_EOL);
                while (list ($key, $val) = each ($SendPOST)) 
                {
                    fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                };
                fwrite($fp, "\$strReturn =>".$e->getMessage().PHP_EOL);
                fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                fclose($fp);
            }
        }else {
            $fp = fopen('Log/OKMart/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
            fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
            fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
            fwrite($fp, "\$strReturn => 回傳網址是空的".PHP_EOL);
            fclose($fp);
        }
    }
    /*else {     #藍新超商
        $_ExpireDate   = date('Ymd', strtotime(date('Ymd') . " +14 day"));
        $DataParameter = array(
            "RespondType"			=> "JSON",
            "TimeStamp"				=> date("YmdHis"),						
            "Version" 				=> "1.5",
            "MerchantOrderNo"		=> $CashFlowID,
            "Amt"					=> intval($_POST["Amount"]),
            "ItemDesc"              => ((strlen(trim($_POST["TradeDesc"]))) <= 1 ? "交易描述":$_POST["TradeDesc"] ),
            "ExpireDate"			=> $_ExpireDate,
            "NotifyURL"				=> Receive_URL."Spgate_CVS_Success.php",
            "CustomerURL"           => Receive_URL."Spgate_CVS_Number.php",
            "Email"					=> $_POST["Email"],
            "LoginType"             => 0,
            "CVS"                   => 1
        );

        $DataParameter["MerchantID"] = Spgate_ID;

        $PostData = create_mpg_aes_encrypt2($DataParameter, Spgate_Key, Spgate_IV);

        $_TradeSha = strtoupper(hash("sha256", "HashKey=".Spgate_Key."&".$PostData."&HashIV=".Spgate_IV));
        $parameter = array(
            "MerchantID" 	=> Spgate_ID,
            "TradeInfo"		=> $PostData,
            "TradeSha"      => $_TradeSha,
            "Version"       => "1.5"
        );

        $sHtml = "<form id='rongpaysubmit' name='rongpaysubmit' action='" . Spgate_URL . "' method='POST'>";
        while (list($key, $val) = each($parameter)) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='付款' style='display:none'></form>";
        $sHtml = $sHtml . "<script>document.forms['rongpaysubmit'].submit();</script>";
        
        echo $sHtml;
        exit;
    }*/

    /*if ($_POST['ChoosePayment'] == "711") {
        $_ExpireDate   = date('Ymd', strtotime(date('Ymd') . " +1 day"));
        $DataParameter = array(
            "RespondType"			=> "JSON",
            "TimeStamp"				=> date("YmdHis"),						
            "Version" 				=> "1.5",
            "MerchantOrderNo"		=> $CashFlowID,
            "Amt"					=> intval($_POST["Amount"]),
            "ItemDesc"              => $_POST["TradeDesc"],
            "ExpireDate"			=> $_ExpireDate,
            "NotifyURL"				=> Receive_URL."Spgate_CVS_Success.php",
            "CustomerURL"           => Receive_URL."Spgate_CVS_Number.php",
            "Email"					=> "abc@abc.com",
            "LoginType"             => 0,
            "CVS"                   => 1
        );

        $DataParameter["MerchantID"] = Spgate_ID;

        $PostData = create_mpg_aes_encrypt2($DataParameter, Spgate_Key, Spgate_IV);

        $_TradeSha = strtoupper(hash("sha256", "HashKey=".Spgate_Key."&".$PostData."&HashIV=".Spgate_IV));
        $parameter = array(
            "MerchantID" 	=> Spgate_ID,
            "TradeInfo"		=> $PostData,
            "TradeSha"      => $_TradeSha,
            "Version"       => "1.5"
        );

        $sHtml = "<form id='rongpaysubmit' name='rongpaysubmit' action='" . Spgate_URL . "' method='POST'>";
        while (list($key, $val) = each($parameter)) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='付款' style='display:none'></form>";
        $sHtml = $sHtml . "<script>document.forms['rongpaysubmit'].submit();</script>";
        
        echo $sHtml;
        exit;
    }else if ($_POST['ChoosePayment'] == "Family") {
        $_ExpireDate   = date('Y-m-d 23:59:59', strtotime(date('Ymd') . " +1 day"));
        //測試URL
        //$client = new SoapClient("https://ect.familynet.com.tw/pin/webec.asmx?wsdl");
        //正式URL
        $client = new SoapClient("https://ec.famiport.com.tw/pin/webec.asmx?wsdl");
        
        /*$Rxml = new SimpleXMLElement('<TX_WEB/>');
        $HEADERXML = $Rxml->addChild("HEADER");
        $HEADERXML->addChild("XML_VER", "05.01");
        $HEADERXML->addChild("XML_FROM", "82960012");
        $_ReturnXML = $Rxml->asXML();

        $_ReturnXML = str_replace("<?xml version=\"1.0\"?>\n", '', $_ReturnXML);
        //echo $_ReturnXML;
        //exit;

        $_ReturnXML = "<TX_WEB>
        <HEADER>
          <XML_VER>05.01</XML_VER>
          <XML_FROM>82960012</XML_FROM>
          <TERMINO>KK3KK35</TERMINO>
          <XML_TO>99027</XML_TO>
          <BUSINESS>B000001</BUSINESS>
          <XML_DATE>20191108</XML_DATE>
          <XML_TIME>174500</XML_TIME>
          <STATCODE>0000</STATCODE>
          <STATDESC>string</STATDESC>
        </HEADER>
        <AP>
          <ORDER_NO>".$CashFlowID."</ORDER_NO>
          <ACCOUNT>00050</ACCOUNT>
          <END_DATE>20191118</END_DATE>
          <END_TIME>000000</END_TIME>
          <PAY_TYPE>FamiPay</PAY_TYPE>
          <PRD_DESC>TEST</PRD_DESC>
          <PAY_COMP>全網</PAY_COMP>
          <TRADE_TYPE>1</TRADE_TYPE>
          <DESC1>TEST1</DESC1>
          <DESC2>TEST1</DESC2>
          <DESC3>TEST1</DESC3>
          <DESC4>TEST1</DESC4>
        </AP>
      </TX_WEB>";

        $xmlr = new SimpleXMLElement($_ReturnXML);
        $params = new stdClass();
        $params->xml = $xmlr->asXML();*/

        /*$parameter = array(
            "TX_WEB" 	    => array(
                'HEADER' => array(
                    "XML_VER"   => "05.01",
                    "XML_FROM"  => "82960012",
                    "TERMINO"   => "KK35KK3",
                    "XML_TO"    => "99027",
                    "BUSINESS"  => "B000001",
                    "XML_DATE"  => DATE("Ymd"),
                    "XML_TIME"  => DATE("His"),
                    "STATCODE"  => "0000"
                ),
                'AP' => array(
                    "ORDER_NO"  => $CashFlowID,
                    "ACCOUNT"   =>  str_pad(intval($_POST["Amount"]),5,'0',STR_PAD_LEFT),
                    "END_DATE"  => DATE("Ymd" , strtotime($_ExpireDate)),
                    "END_TIME"  => DATE("His" , strtotime($_ExpireDate)),
                    "PAY_TYPE"  => "FamiPay",
                    "PRD_DESC"  => "Pay",
                    "PAY_COMP"  => "智慧付",
                    "TRADE_TYPE"  => "1"
                    ),
            ),
            "ACCOUNT_NO"	=> "ｗisdompay",
            "PASSWORD"      => "8jAw0FvqVT"
        );

        //var_dump($parameter);
        //exit;
        //$result = $client->__soapCall('NewOrder',array('parameters' => $parameter));
        $result = $client->NewOrder($parameter);
        //$result = $client->__soapCall("NewOrder", $parameter);
        
        //var_dump($result);
        if ($result->NewOrderResult == "0000") {

            $field = array('VatmAccount', 'ExpireDatetime');
            $value = array($result->TX_WEB->AP->PIN_CODE, $_ExpireDate);
            CDbShell::update('ledger', $field, $value, "Sno = '".$LedgerId."'");
            
            $result->TX_WEB->AP->PIN_CODE;
            include 'StorePay2.html';

            if ($TakeNumberURL != '') {
                $Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&HashKey='.$FirmRow['HashKey'].'&RtnCode=1&TradeID='.$_POST['MerTradeID'].'&UserID='.$_POST['MerUserID'].'&Money='.intval($result->TX_WEB->AP->ACCOUNT));
        
                $SendPOST['RtnCode'] = '1';
                $SendPOST['RtnMessage'] = '取號成功';
                $SendPOST['MerTradeID'] = $_POST['MerTradeID'];
                $SendPOST['MerProductID'] = $_POST['MerProductID'];
                $SendPOST['MerUserID'] = $_POST['MerUserID'];
        
                $SendPOST['Amount'] = intval($$result->TX_WEB->AP->ACCOUNT);
                $SendPOST['ExpireTime'] = $_ExpireDate;
                $SendPOST['Store'] = "Family";
                $SendPOST["CodeNo"] = $result->TX_WEB->AP->PIN_CODE;
                $SendPOST['Validate'] = $Validate;
                try {
                    $fp = fopen('Log/Family/Send_TakeNumber_LOG_'.date("YmdHi").'.txt', 'a');
                    fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                    fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                    while (list ($key, $val) = each ($SendPOST)) 
                    {
                        fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                    };
        
                    $strReturn = SockPost($TakeNumberURL, $SendPOST, $curlerror);
                    
                    
                    fwrite($fp, "\$strReturn =>".$strReturn.PHP_EOL);
                    fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                    fclose($fp);
                } catch (Exception $e) {
        
                    $fp = fopen('Log/Family/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                    fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                    fwrite($fp, "\$SuccessURL =>".$SuccessURL.PHP_EOL);
                    while (list ($key, $val) = each ($SendPOST)) 
                    {
                        fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
                    };
                    fwrite($fp, "\$strReturn =>".$e->getMessage().PHP_EOL);
                    fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
                    fclose($fp);
                }
            }else {
                $fp = fopen('Log/Family/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
                fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
                fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
                fwrite($fp, "\$strReturn => 回傳網址是空的".PHP_EOL);
                fclose($fp);
            }
        }else {
            echo $result->NewOrderResult . $result->TX_WEB->HEADER->STATDESC;
        }
    }else if ($_POST['ChoosePayment'] == "OK") {
        $_ExpireDate   = date('Y-m-d 23:59:59', strtotime(date('Ymd') . " +1 day"));
        Again:
        $VatmAccount = "WP".substr(date("Y"), -1).date("md") . str_pad(rand(0, 10000000), 7, '0', STR_PAD_LEFT);

        $sql = "SELECT Sno FROM ledger WHERE VatmAccount = '" . $VatmAccount . "'";
        CDbShell::query($sql);
        if (CDbShell::num_rows() > 0) {
            goto Again;
        }
        
        $field = array("VatmAccount");
		$value = array($VatmAccount);
		CDbShell::update("ledger", $field, $value, "Sno = '".$LedgerId."'" );

        $OrderNo = $CashFlowID;
		$MerProductID = $_POST['MerProductID'];
		$MerUserID = $_POST['MerUserID'];
		$Amount = $_POST['Amount'];
    
        include("StorePay3.html");
    }else if ($_POST['ChoosePayment'] == "HiLife") {
        $_ExpireDate   = date('Y-m-d 23:59:59', strtotime(date('Ymd') . " +1 day"));
        Again2:
        $VatmAccount = "wdp".substr(date("Y"), -1).date("md") . str_pad(rand(0, 100000000), 8, '0', STR_PAD_LEFT);

        $sql = "SELECT Sno FROM ledger WHERE VatmAccount = '" . $VatmAccount . "'";
        CDbShell::query($sql);
        if (CDbShell::num_rows() > 0) {
            goto Again2;
        }
        
        $field = array("VatmAccount");
		$value = array($VatmAccount);
		CDbShell::update("ledger", $field, $value, "Sno = '".$LedgerId."'" );

        $OrderNo = $CashFlowID;
		$MerProductID = $_POST['MerProductID'];
		$MerUserID = $_POST['MerUserID'];
		$Amount = $_POST['Amount'];
    
        include("StorePay4.html");
    }*/
} catch (Exception $e) {
    echo '<center>錯誤！ErrCode：'.$ErrCode.' ErrMessage：'.$e->getMessage().'</center>';
    /*if ($FailURL != "") {
        $sHtml = "<form id='Deviant' name='Deviant' action='".$FailURL."' method='POST'>";
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
function create_mpg_aes_encrypt($parameter = '', $key = '', $iv = '')
{
    $return_str = '';
    if (!empty($parameter)) {
        //將參數經過 URL ENCODED QUERY STRING
        $return_str = http_build_query($parameter);
        //echo $iv;
        //echo "<br />";
    }

    return trim(bin2hex(@openssl_encrypt(addpadding($return_str), 'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv)));
}

function build_mysign($sort_array, $HashKey, $HashIV, $sign_type = 'MD5')
{
    $prestr = create_linkstring($sort_array);
    $prestr = 'HashKey='.$HashKey.'&'.$prestr.'&HashIV='.$HashIV;
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
    $arg = '';
    while (list($key, $val) = each($array)) {
        $arg .= $key.'='.$val.'&';
    }
    $arg = substr($arg, 0, count($arg) - 2);             //去掉最后一个&字符
    return $arg;
}

function sign($prestr, $sign_type)
{
    $sign = '';
    if ('MD5' == $sign_type) {
        $sign = md5($prestr);
    } else {
        die('暂不支持'.$sign_type.'类型的签名方式');
    }

    return $sign;
}

function arg_sort($array)
{
    ksort($array);
    reset($array);

    return $array;
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