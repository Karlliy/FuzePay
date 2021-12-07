<?php
    ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
    ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
    ini_set('error_reporting',E_ALL & ~E_NOTICE);
    header('Access-Control-Allow-Origin: *');
    include_once("../BaseClass/Setting.php");
    include_once("../BaseClass/CDbShell.php");
    include_once("../BaseClass/CommonElement.php");

    $fp = fopen('../Log/TCB/Notify_LOG_'.date("YmdHis").'.txt', 'a');
    fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
    while (list ($key, $val) = each ($_POST)) 
    {
        fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
    };	
    $XmlFile = file_get_contents('php://input');
    fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
    fwrite($fp, "XmlFile =>".$XmlFile.PHP_EOL);	
    fclose($fp);
    
    $xml = simplexml_load_string($XmlFile, NULL, NULL, "http://schemas.xmlsoap.org/soap/envelope/");

    // register your used namespace prefixes
    $xml->registerXPathNamespace('header', 'http://www.tibco.com/namespaces/bc/2002/04/partyinfo.xsd');
    $xml->registerXPathNamespace('services', 'http://ns.tcb.com.tw/XSD/TCB/BC/Message/BankCollStatusAdviseRq/01'); // ? ns not in use

    $nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Header/header:PartyInfo/transactionID');
    $_OrderID = (string) $nodes[0];
    #var_dump($_OrderID);

    // then use xpath to adress the item you want (using this NS)
    $nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:BillId');
    $_VirAccount = (string) $nodes[0];
    #var_dump($_VirAccount);

    $nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:CollInfo/services:CurAmt/services:Amt');
    $_Amount = (string) $nodes[0];
    #var_dump($_Amount);

    $nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:CollInfo/services:OrigDt');
    $OrigDt = (string) $nodes[0];
    #var_dump($OrigDt);

    $nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:CollInfo/services:OrigTm');
    $OrigTm = (string) $nodes[0];
    #var_dump($OrigTm);

    $nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:SettlementInfo/services:CustAcctId');
    $CustAcctId = (string) $nodes[0];
    #var_dump($CustAcctId);

    $_PaymentDate = Date('Y-m-d H:i:s', strtotime($OrigDt. " ".$OrigTm));
    #var_dump($_PaymentDate);

    // if (trim($_VirAccount) == "0889090505911203") {   #蝦皮用帳號
    //     $_FirmSno = 66;
    // }else if (trim($_VirAccount) == "0889090519242258" || trim($_VirAccount) == "0889090519703420" || trim($_VirAccount) == "0889090519011801") {   #富達用帳號
    //     $_FirmSno = 71;
    // }else if (trim($_VirAccount) == "0889090531237360") {   #bbbb用帳號
    //     $_FirmSno = 72;
    // }else if (trim($_VirAccount) == "0889090617147785") {   #威博用帳號
    //     $_FirmSno = 62;
    // }else if (trim($_VirAccount) == "0889090617899907") {   #1111用帳號
    //     $_FirmSno = 77;
    // }else if (trim($_VirAccount) == "0889090623505347" || trim($_VirAccount) == "0889090623203440" || trim($_VirAccount) == "0889090623356000" || trim($_VirAccount) == "0889090623338507"
    // || trim($_VirAccount) == "0889090623709085" || trim($_VirAccount) == "0889090623576028" || trim($_VirAccount) == "0889090623929139" || trim($_VirAccount) == "0889090623528105"
    // || trim($_VirAccount) == "0889090623656692" || trim($_VirAccount) == "0889090623831442" || trim($_VirAccount) == "0889090623238138" || trim($_VirAccount) == "0889090623152156"
    // || trim($_VirAccount) == "0889090623733192" || trim($_VirAccount) == "0889090623720441" || trim($_VirAccount) == "0889090623030884" || trim($_VirAccount) == "0889090623410579"
    // || trim($_VirAccount) == "0889090623237306" || trim($_VirAccount) == "0889090623685974" || trim($_VirAccount) == "0889090623842711" || trim($_VirAccount) == "0889090623516401") {   #紫紫紫用帳號
    //     $_FirmSno = 81;
    // }else if (trim($_VirAccount) == "0889090422943810" || trim($_VirAccount) == "0889090422783383" || trim($_VirAccount) == "0889090422167991" || trim($_VirAccount) == "0889090517619173" || trim($_VirAccount) == "0889090517851620" || trim($_VirAccount) == "0889090517325616") {
    //     $_FirmSno = 60;
    // }else {
    //     $_FirmSno = 1;
    // }
    $_IsFixe = false;

    @CDbShell::connect();

    CDbShell::query("SELECT L.Sno FROM Ledger AS L WHERE L.OrderID = '".$_OrderID."' AND State = 0"); 
    if (CDbShell::num_rows() == 0) {        #沒有存在再新增
        $_NotSendSuccess = 0;
        $sql = "SELECT * FROM fixedvirt WHERE FixedVirtAccount = '" . trim($_VirAccount) . "'";
        CDbShell::query($sql);
        if (CDbShell::num_rows() != 0) {
            $_IsFixe = true;
            $FixedRow = CDbShell::fetch_array();
            $_FirmSno = $FixedRow['FirmSno'];
            CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.Fee, L.NotifyURL FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.FirmSno = '".$_FirmSno."'"); 
            $FirmRow = CDbShell::fetch_array();
            $SuccessURL = $FirmRow["SuccessURL"];
            $NotifyURL = $FirmRow["NotifyURL"];
            $FailURL = $FirmRow["FailURL"];
            $_NotSendSuccess = $FirmRow["NotSendSuccess"];
        }else {     #非固定
            $_IsFixe = false;
            CDbShell::query("SELECT F.*, L.FirmSno, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.Fee, L.NotifyURL FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.VatmAccount = '".$_VirAccount."'"); 
            $FirmRow = CDbShell::fetch_array();
            $SuccessURL = $FirmRow["SuccessURL"];
            $NotifyURL = $FirmRow["NotifyURL"];
            $FailURL = $FirmRow["FailURL"];
            $_FirmSno = $FirmRow["FirmSno"];
            $_NotSendSuccess = $FirmRow["NotSendSuccess"];
        }

        $fp = fopen('../Log/TCB/FirmSno_LOG_'.date("YmdHis").'.txt', 'a');
        fwrite($fp, " ---------------- ----------------".PHP_EOL);
        fwrite($fp, "_FirmSno => ".$_FirmSno.PHP_EOL);	
        fclose($fp);
        
        CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$_FirmSno." AND PF.Type = '1' AND FC.Enable = 1 AND PF.Mode = '合庫' LIMIT 1");  
        if (CDbShell::num_rows() == 0) {
            CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$_FirmSno." AND PF.Type = '1' AND PF.Kind = '虛擬帳號' AND (FC.FeeRatio > 0 OR FC.FixedFee > 0) LIMIT 1");  
        }
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
            $Fee = floatval($_Amount) * floatval($FCRow["FeeRatio"] / 100);
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

        if ($_IsFixe == true) {     #固定
            $field = array("FirmSno","OrderID", "PaymentType", "PaymentName", "PaymentCode", "Period", "ClosingDate", "ExpectedRecordedDate", "Total", "ClosingTotal", "Fee", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber", "VatmAccount");
            $value = array($_FirmSno, $_OrderID, "1", "虛擬帳號-合庫", "虛擬帳號-[合庫]", $Period, $ClosingDate, $ExpectedRecordedDate, $_Amount, $_Amount, $Fee, $_PaymentDate, $_PaymentDate, "0", "交易成功", "0", $CustAcctId, $_VirAccount);
            //CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VirAccount."'");
            CDbShell::insert("ledger", $field, $value);
        }else {
            $field = array("OrderID", "PaymentType", "PaymentName", "PaymentCode", "Period", "ClosingDate", "ExpectedRecordedDate", "Total", "ClosingTotal", "Fee", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber");
            $value = array($_OrderID, "1", "虛擬帳號-合庫", "虛擬帳號-[合庫]", $Period, $ClosingDate, $ExpectedRecordedDate, $_Amount, $_Amount, $Fee, $_PaymentDate, $_PaymentDate, "0", "交易成功", "0", $CustAcctId);
            CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VirAccount."'");
        }
        $affected_rows = CDbShell::affected_rows();
        //if ($affected_rows == 1) {

            /*if ($Fee > $FirmRow['Fee']) {
                $field = array("Fee");
                $value = array($Fee);
                CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VirAccount."'");
            }*/

            if (('' != $SuccessURL || $NotifyURL != '') && $_NotSendSuccess == 0) {
                $Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&RtnCode=1&MerTradeID='.$FirmRow['MerTradeID'].'&MerUserID='.$FirmRow['MerUserID']);

                $SendPOST['RtnCode'] = '1';
                $SendPOST['RtnMessage'] = '交易成功';
                $SendPOST['MerTradeID'] = $FirmRow['MerTradeID'];
                $SendPOST['MerProductID'] = $FirmRow['MerProductID'];
                $SendPOST['MerUserID'] = $FirmRow['MerUserID'];
                $SendPOST["PayInfo"] = $CustAcctId;
                $SendPOST['Amount'] =  $_Amount;
                $SendPOST['PaymentDate'] = $_PaymentDate;
                $SendPOST['Validate'] = $Validate;
                if ($SuccessURL != '') {
                    try {
                        $strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

                        $fp = fopen('../Log/TCB/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
                        fwrite($fp, ' ---------------- Send_Notify開始 ---------------- '.PHP_EOL);
                        fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
                        while (list($key, $val) = each($SendPOST)) {
                            fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
                        }
                        fwrite($fp, '$strReturn =>'.$strReturn.PHP_EOL);
                        fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
                        fclose($fp);
                    } catch (Exception $e) {
                        $fp = fopen('../Log/TCB/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
                        fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
                        fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
                        while (list($key, $val) = each($SendPOST)) {
                            fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
                        }
                        fwrite($fp, '$strReturn =>'.$e->getMessage().PHP_EOL);
                        fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
                        fclose($fp);
                    }
                }

                if ($NotifyURL != '') {
                    try {
                        $strReturn = SockPost($NotifyURL, $SendPOST, $curlerror);

                        $fp = fopen('../Log/TCB/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
                        fwrite($fp, ' ---------------- Send_Notify開始 ---------------- '.PHP_EOL);
                        fwrite($fp, 'NotifyURL =>'.$NotifyURL.PHP_EOL);
                        while (list($key, $val) = each($SendPOST)) {
                            fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
                        }
                        fwrite($fp, '$strReturn =>'.$strReturn.PHP_EOL);
                        fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
                        fclose($fp);
                    } catch (Exception $e) {
                        $fp = fopen('../Log/TCB/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
                        fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
                        fwrite($fp, 'NotifyURL =>'.$NotifyURL.PHP_EOL);
                        while (list($key, $val) = each($SendPOST)) {
                            fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
                        }
                        fwrite($fp, '$strReturn =>'.$e->getMessage().PHP_EOL);
                        fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
                        fclose($fp);
                    }
                }
            } else {
                $fp = fopen('../Log/TCB/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
                fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
                fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
                fwrite($fp, '$strReturn => 回傳網址是空的'.PHP_EOL);
                fclose($fp);
            }

            $SerialNumber = intval(microtime(true)*10000);
            $Response .=
<<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<outputMessage>
    <ns4:BankCollStatusAdviseRs xmlns:S="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns2="http://ns.tcb.com.tw/XSD/TCB/BC/Message/BankCollStatusAdviseRq/01" xmlns:ns3="http://www.tibco.com/namespaces/bc/2002/04/partyinfo.xsd" xmlns:ns4="http://ns.tcb.com.tw/XSD/TCB/BC/Message/BankCollStatusAdviseRs/01">
        <ns4:Status_Res>
            <ns4:StatusCode>0000</ns4:StatusCode>
            <ns4:StatusDesc>SUCCESS</ns4:StatusDesc>
        </ns4:Status_Res>
        <ns4:RqUID_Res>{$SerialNumber}</ns4:RqUID_Res>
    </ns4:BankCollStatusAdviseRs>
</outputMessage>
EOF;
            header("Content-type: text/xml; charset=utf-8");
            echo $Response;
    /*}else {

        $fp = fopen('../Log/TCB/ErrLOG_'.date('YmdHi').'.txt', 'a');
        fwrite($fp, ' ---------------- Err開始 ---------------- '.PHP_EOL);
        fwrite($fp, '\$affected_rows =>'.$affected_rows.PHP_EOL);
        fclose($fp);

        $SerialNumber = intval(microtime(true)*10000);
        $Response .=
        <<<EOF
        <?xml version = "1.0" encoding = "UTF-8"?>
        <outputMessage>
            <ns4:BankCollStatusAdviseRs xmlns:S = "http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENV = "http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns2 = "http://ns.tcb.com.tw/XSD/TCB/BC/Message/BankCollStatusAdviseRq/01" xmlns:ns3 = "http://www.tibco.com/namespaces/bc/2002/04/partyinfo.xsd" xmlns:ns4 = "http://ns.tcb.com.tw/XSD/TCB/BC/Message/BankCollStatusAdviseRs/01">
                <ns4:Status_Res>
                    <ns4:StatusCode>E906</ns4:StatusCode>
                    <ns4:StatusDesc>ServiceRequestNotAuthorized</ns4:StatusDesc>
                </ns4:Status_Res>
                <ns4:RqUID_Res>{$SerialNumber}</ns4:RqUID_Res>
            </ns4:BankCollStatusAdviseRs>
        </outputMessage>
EOF;

        echo $Response;
    }*/

        CDbShell::DB_close();
        exit;
    }

    function microtime_float()
    {
    list($usec, $sec) = explode(" ", microtime());
    return ($usec.$sec);
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

	function get_client_ip($type = 0,$adv=false) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if($adv){
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos    =   array_search('unknown',$arr);
                if(false !== $pos) unset($arr[$pos]);
                $ip     =   trim($arr[0]);
            }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip     =   $_SERVER['HTTP_CLIENT_IP'];
            }elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip     =   $_SERVER['REMOTE_ADDR'];
            }
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法驗證
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }


