<?php
    ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	header('Access-Control-Allow-Origin: *');
	include_once("../BaseClass/Setting.php");
	include_once("../BaseClass/CDbShell.php");
	include_once("../BaseClass/CommonElement.php");

    $fp = fopen('../Log/First/Notify_2_LOG_'.date("YmdHis").'.txt', 'a');
    fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);

    foreach($_POST as $key => $val)
    {
        fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
    };	

    fwrite($fp, " ---------------- 開始GET ---------------- ".PHP_EOL);
    foreach($_GET as $key => $val) 
	{
		fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
    };	
    $XmlFile = file_get_contents('php://input');
    fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
    fwrite($fp, "XmlFile =>".$XmlFile.PHP_EOL);	

    // $string = explode('&',$XmlFile);

    // foreach($string as $key => $val)
    // {
    //     list($key2, $val2) = explode('=',$val);
    //     $_POST[$key2] = urldecode($val2);
    // };

    fwrite($fp, " ---------------- Log ----------------".PHP_EOL);
    fwrite($fp, "\$_OrderId =>".$_POST["OrderId"].PHP_EOL);	
    fwrite($fp, "\$_Amount =>".$_POST["Amount"].PHP_EOL);
    fwrite($fp, "\$_PaymentDate =>".$_POST["TransTime"].PHP_EOL);
    fwrite($fp, "\$_PaymentDate =>".date("Y-m-d H:i:s", strtotime(trim($_POST["TransTime"]))).PHP_EOL);
    fwrite($fp, "\$_ToolStatus =>".$_POST["ToolStatus"].PHP_EOL);
    fwrite($fp, "\$_ReCheckId =>".$_POST["ReCheckId"].PHP_EOL);
    fclose($fp);

    if ($_POST["TransStatus"] == "2") {
        $_PaymentDate = date("Y-m-d H:i:s", strtotime($_POST["TransTime"]));

        @CDbShell::connect();
        CDbShell::query("SELECT L.Sno FROM Ledger AS L WHERE L.CashFlowID = '".$_POST["OrderId"]."' AND L.State = '-1'"); 
        if (CDbShell::num_rows() == 1) {
            CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.Fee, L.NotifyURL FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.CashFlowID = '".$_POST["OrderId"]."'"); 
        
            $FirmRow = CDbShell::fetch_array();
            $_NotSendSuccess = 0;
            $SuccessURL = $FirmRow["SuccessURL"];
            $NotifyURL = $FirmRow["NotifyURL"];
            $FailURL = $FirmRow["FailURL"];
            $_NotSendSuccess = $FirmRow["NotSendSuccess"];

            CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '1' AND FC.Enable = '1' AND PF.Kind = '虛擬帳號' AND Mode = '第一銀行' LIMIT 1");  
            if (CDbShell::num_rows() == 0) {
                CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '1' AND PF.Kind = '虛擬帳號' AND (FC.FeeRatio > 0 OR FC.FixedFee > 0) LIMIT 1");  
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
                case "TenDays":
                    $GetDay = Date('d', strtotime($_PaymentDate));
                    if($GetDay <= 10) {
                        $Period = date('Y-m-01', strtotime($_PaymentDate)) . " ~ " . date('Y-m-10',strtotime($_PaymentDate));
                        $ClosingDate = date('Y-m-10', strtotime($_PaymentDate));
                    
                    }elseif($GetDay > 10 && $GetDay <= 20) {
                        $Period = date('Y-m-11', strtotime($_PaymentDate)) . " ~ " . date('Y-m-20',strtotime($_PaymentDate));
                        $ClosingDate = date('Y-m-20', strtotime($_PaymentDate));
                    
                    }elseif($GetDay > 20) {
                        $Period = date('Y-m-21', strtotime($_PaymentDate)) . " ~ " . date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
                        $ClosingDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
                    
                    }
                    break;
                case "Month":
                    //$ExpectedRecordedDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month +".$FCRow["Day"]." day"));
                    $Period = date("Y-m-01", strtotime($_PaymentDate)) . " ~ " . date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
                    $ClosingDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
                    break;
            }

            if ($FCRow["Closing"] == "TenDays") { #旬結撥款T日含假日
                $ExpectedRecordedDate = date('Y-m-d', strtotime($ClosingDate ." +".($FCRow["Day"]+1)." day"));
            }else {
                $ExpectedRecordedDate = CommonElement::CountHoliday($ClosingDate, $FCRow["Day"], true);
            }

            if (floatval($FCRow["FeeRatio"]) > 0){
                $Fee = floatval($_POST["Amount"]) * floatval($FCRow["FeeRatio"] / 100);
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

            $Obj = json_decode(urldecode($_POST["PayInfo"]));
            $CardNumber = $Obj->PayAccountNo;

            $field = array("OrderID", "PaymentCode", "Period", "ClosingDate", "ExpectedRecordedDate", "ClosingTotal", "Fee", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber");
            $value = array($_POST["ReCheckId"], "虛擬帳號-[第一銀行]", $Period, $ClosingDate, $ExpectedRecordedDate, intval($_POST["Amount"]), $Fee, $_PaymentDate, $_PaymentDate, "0", "交易成功", "0", $CardNumber);
            CDbShell::update("ledger", $field, $value, "CashFlowID = '".$_POST["OrderId"]."'");
            if (CDbShell::affected_rows() == 1) {
                if (('' != $SuccessURL || $NotifyURL != '') && $_NotSendSuccess == 0) {
                    $Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&RtnCode=1&MerTradeID='.$FirmRow['MerTradeID'].'&MerUserID='.$FirmRow['MerUserID']);

                    $SendPOST['RtnCode'] = '1';
                    $SendPOST['RtnMessage'] = '交易成功';
                    $SendPOST['MerTradeID'] = $FirmRow['MerTradeID'];
                    $SendPOST['MerProductID'] = $FirmRow['MerProductID'];
                    $SendPOST['MerUserID'] = $FirmRow['MerUserID'];
                    $SendPOST['PayInfo'] = $CardNumber;
                    $SendPOST['Amount'] = intval($_POST["Amount"]);
                    $SendPOST['PaymentDate'] = $_PaymentDate;
                    $SendPOST['Validate'] = $Validate;
                    if ($SuccessURL != '') {
                        try {
                            $strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

                            $fp = fopen('../Log/First/Send_Notify_2_LOG_'.date('YmdHi').'.txt', 'a');
                            fwrite($fp, ' ---------------- Send_Notify開始 ---------------- '.PHP_EOL);
                            fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
                            //while (list($key, $val) = each($SendPOST)) {
                            foreach($SendPOST as $key => $val)
                            {
                                fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
                            }
                            fwrite($fp, '$strReturn =>'.$strReturn.PHP_EOL);
                            fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
                            fclose($fp);
                        } catch (Exception $e) {
                            $fp = fopen('../Log/First/Send_Notify_2_ErrLOG_'.date('YmdHi').'.txt', 'a');
                            fwrite($fp, ' ---------------- Send_Notify_2_Err開始 ---------------- '.PHP_EOL);
                            fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
                            //while (list($key, $val) = each($SendPOST)) {
                            foreach($SendPOST as $key => $val)
                            {
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

                            $fp = fopen('../Log/First/Send_Notify_2_LOG_'.date('YmdHi').'.txt', 'a');
                            fwrite($fp, ' ---------------- Send_Notify開始 ---------------- '.PHP_EOL);
                            fwrite($fp, 'NotifyURL =>'.$NotifyURL.PHP_EOL);
                            //while (list($key, $val) = each($SendPOST)) {
                            foreach($SendPOST as $key => $val)
                            {
                                fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
                            }
                            fwrite($fp, '$strReturn =>'.$strReturn.PHP_EOL);
                            fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
                            fclose($fp);
                        } catch (Exception $e) {
                            $fp = fopen('../Log/First/Send_Notify_2_ErrLOG_'.date('YmdHi').'.txt', 'a');
                            fwrite($fp, ' ---------------- Send_Notify_2_Err開始 ---------------- '.PHP_EOL);
                            fwrite($fp, 'NotifyURL =>'.$NotifyURL.PHP_EOL);
                            //while (list($key, $val) = each($SendPOST)) {
                            foreach($SendPOST as $key => $val)
                            {
                                fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
                            }
                            fwrite($fp, '$strReturn =>'.$e->getMessage().PHP_EOL);
                            fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
                            fclose($fp);
                        }
                    }
                } else {
                    $fp = fopen('../Log/First/Send_Notify_2_ErrLOG_'.date('YmdHi').'.txt', 'a');
                    fwrite($fp, ' ---------------- Send_Notify_2_Err開始 ---------------- '.PHP_EOL);
                    fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
                    fwrite($fp, '$strReturn => 回傳網址是空的'.PHP_EOL);
                    fclose($fp);
                }
                //$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                //header($protocol . ' 200 '); 

                echo "OK";
                exit;
            }else {
                $fp = fopen('../Log/First/Fail_LOG_'.date('YmdHi').'.txt', 'a');
                fwrite($fp, ' ---------------- Fail_LOG開始 ---------------- '.PHP_EOL);
                fwrite($fp, 'CashFlowID =>'.$_POST["OrderId"].PHP_EOL);
                fclose($fp);
                //header("HTTP/1.0 500 Internal Server Error");
                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                header($protocol . ' 200 ');
                exit;
            }
        }
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