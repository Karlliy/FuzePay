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
            $PaymentType = '8';
            $PaymentName = '超商條碼 7-11';
            break;
        case 'Family':
            $PaymentType = '9';
            $PaymentName = '超商條碼 全家';
            break;
        case 'OK':
            $PaymentType = '10';
            $PaymentName = '超商條碼 OK';
            break;
        case 'HiLife':
            $PaymentType = '11';
            $PaymentName = '超商條碼 Hi-Life';
            break;
        // default:    #藍新
        //     $PaymentType = '2';
        //     break;
        default:    #藍新
            $PaymentType = '8';
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

    if ($_POST['ChoosePayment'] == "711" || $_POST['ChoosePayment'] == "") {

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

        $_DATE = DATE("Ymd");
        $_TIME = DATE("His");
        $_TIME2 = DATE("Hi");

        #測試環境
        /*$_MOBINO = $_DATE."MB0001".str_pad($LedgerId, 8, '0', STR_PAD_LEFT).$_TIME2;
        
        $SendPOST = 
        <<<EOF
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" >
            <soap:Header>
                <AuthHeader xmlns="http://www.ibon.com.tw/">
                        <UID>MB0001</UID>
                        <PWD>zecPUXG9QtSUGgzB</PWD>
                    </AuthHeader>
            </soap:Header>
            <soap:Body>
                <MobiPrint xmlns="http://www.ibon.com.tw/">
                        <HEADER>
                            <MOBINO>{$_MOBINO}</MOBINO>
                            <BUSINESS>CP0700001</BUSINESS>
                            <DATE>{$_DATE}</DATE>
                            <TIME>{$_TIME}</TIME>
                            <STATCODE>0000</STATCODE>
                            <STATDESC/>
                        </HEADER>
                        <AP>
                            <PCODE_IN>S0020</PCODE_IN>
                            <KEY_1>{$VatmAccount}</KEY_1>
                            <KEY_2/>
                            <KEY_3/>
                            <KEY_4/>
                            <KEY_5/>
                            <KEY_6/>
                            <TOTAL_COUNT>1</TOTAL_COUNT>
                            <Detail>
                                <SERIALNO>01</SERIALNO>
                            </Detail>
                        </AP>
                    </MobiPrint>
            </soap:Body>

        </soap:Envelope>
EOF;
        $strReturn = SockPostWS("http://61.57.234.95/CPMobiService/CPMOBI_PayService.asmx", $SendPOST, $curlerror);*/

        #正式環境
        $_MOBINO = $_DATE."2KED02".str_pad($LedgerId, 8, '0', STR_PAD_LEFT).$_TIME2;
        $SendPOST = 
        <<<EOF
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" >
            <soap:Header>
                <AuthHeader xmlns="http://www.ibon.com.tw/">
                        <UID>2KED02</UID>
                        <PWD>2f2PgMsPRRGgE2qGxqUC</PWD>
                    </AuthHeader>
            </soap:Header>
            <soap:Body>
                <MobiPrint xmlns="http://www.ibon.com.tw/">
                        <HEADER>
                            <MOBINO>{$_MOBINO}</MOBINO>
                            <BUSINESS>CP0700001</BUSINESS>
                            <DATE>{$_DATE}</DATE>
                            <TIME>{$_TIME}</TIME>
                            <STATCODE>0000</STATCODE>
                            <STATDESC/>
                        </HEADER>
                        <AP>
                            <PCODE_IN>S0020</PCODE_IN>
                            <KEY_1>{$VatmAccount}</KEY_1>
                            <KEY_2/>
                            <KEY_3/>
                            <KEY_4/>
                            <KEY_5/>
                            <KEY_6/>
                            <TOTAL_COUNT>1</TOTAL_COUNT>
                            <Detail>
                                <SERIALNO>01</SERIALNO>
                            </Detail>
                        </AP>
                    </MobiPrint>
            </soap:Body>

        </soap:Envelope>
EOF;
        $strReturn = SockPostWS("https://bonus.ibon.com.tw/CPMobiService/CPMOBI_PayService.asmx", $SendPOST, $curlerror);

        //echo $strReturn;
        $xml = simplexml_load_string($strReturn, NULL, NULL, "http://www.w3.org/2003/05/soap-envelope");
        $xml->registerXPathNamespace('MobiPrint', 'http://www.ibon.com.tw/');

        $nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:HEADER/MobiPrint:STATCODE');
        $STATCODE = (string) $nodes[0];
        //var_dump($STATCODE);

        $nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:AP/MobiPrint:Detail/MobiPrint:PAY_AMOUNT');
        $AMOUNT = (string) $nodes[0];
        //var_dump($AMOUNT);

        $nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:AP/MobiPrint:Detail/MobiPrint:PAY_ENDDATE');
        $_ExpireDate = (string) $nodes[0];

        $nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:AP/MobiPrint:Detail/MobiPrint:OL_CODE_1');
        $OL_CODE_1 = (string) $nodes[0];
        //var_dump($OL_CODE_1);

        //echo "<img src=Barcode.php?".$OL_CODE_1." />";

        $nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:AP/MobiPrint:Detail/MobiPrint:OL_CODE_2');
        $OL_CODE_2 = (string) $nodes[0];
        //var_dump($OL_CODE_2);
        //echo "<img src=Barcode.php?".$OL_CODE_2." />";
        $nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:AP/MobiPrint:Detail/MobiPrint:OL_CODE_3');
        $OL_CODE_3 = (string) $nodes[0];
        //var_dump($OL_CODE_3);
        //echo "<img src=Barcode.php?".$OL_CODE_3." />";

        include("BarcodePayFor711.html");
        exit;

    }else if ($_POST['ChoosePayment'] == "Family") {
        $_ExpireDate = date('Y-m-d 23:59:59', strtotime(date('Ymd') . " +1 day"));
            
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

            /*if ($_POST['ReturnJosn'] == "Y") {
                echo json_encode($SendPOST);
            }else {
                include("StorePayForFamily.html");
            }*/

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
    
        $parameter = array(
            "VD_ACCOUNT"			=> "fuzepay",
            "VD_ORDERNO"			=> $result->TX_WEB->AP->ORDER_NO,
            "VD_PINCODE"			=> $result->TX_WEB->AP->PIN_CODE,
        );
        $sHtml = "<form id='rongpaysubmit' name='rongpaysubmit' action='https://ecb.famiport.com.tw/familyec/barcode_guide2.aspx' method='POST'>";
        while (list($key, $val) = each($parameter)) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        
        //submit按钮控件请不要含有name属性
        //$sHtml = $sHtml . "<input type='submit' value='付款'></form>";
        $sHtml = $sHtml . "<input type='submit' value='付款' style='display:none'></form>";
        $sHtml = $sHtml . "<script>document.forms['rongpaysubmit'].submit();</script>";
        
        echo $sHtml;
        exit;
    }
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

function SockPostWS($URL, $Query, &$curlerror){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    /*curl_setopt($ch, CURLOPT_HTTPHEADER , array(
        "Cache-Control: no-cache",
        "Content-Type: application/xml"
    ));*/
    $headers = array("Content-type:text/xml; charset=utf-8");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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
