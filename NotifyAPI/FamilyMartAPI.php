<?php
    ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	header('Access-Control-Allow-Origin: *');
	include_once("../BaseClass/Setting.php");
	include_once("../BaseClass/CDbShell.php");
	include_once("../BaseClass/CommonElement.php");
	
	//print_r($_POST);
	$fp = fopen('../Log/Family/Notify_LOG_'.date("YmdHis").'.txt', 'a');
	fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
	foreach($_POST as $key => $val)
	//while (list ($key, $val) = each ($_POST)) 
	{
		fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
    };	
    $XmlFile = file_get_contents('php://input');
    fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
	fwrite($fp, "XmlFile =>".$XmlFile.PHP_EOL);	
    fclose($fp);

    $xml = simplexml_load_string($_POST['d']);

    @CDbShell::connect();
	CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.NotifyURL FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.CashFlowID = '".$xml->AP->ORDER_NO."'"); 
	$FirmRow = CDbShell::fetch_array();
	$SuccessURL = $FirmRow["SuccessURL"];
    $NotifyURL = $FirmRow["NotifyURL"];
    $FailURL = $FirmRow["FailURL"];
    
    CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '4' AND PF.Mode = '全家' LIMIT 1");  
    if (CDbShell::num_rows() == 0) {
        CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '4' AND (FC.FeeRatio > 0 OR FC.FixedFee > 0) LIMIT 1");  
    }
    $FCRow = CDbShell::fetch_array();
    
    $_PaymentDate = date("Y-m-d H:i:s", strtotime($xml->HEADER->DATE.$xml->HEADER->TIME));
	  	
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

    if ($xml->HEADER->STATCODE == "0000") {

        $field = array("OrderID", "PaymentCode", "Period", "ClosingDate", "ExpectedRecordedDate", "ClosingTotal", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber", "Parameter1", "Parameter2", "Parameter3");
	  	$value = array($xml->HEADER->TERMINO, "超商繳款-[全家]", $Period, $ClosingDate, $ExpectedRecordedDate, intval($xml->AP->ACCOUNT), $_PaymentDate, $_PaymentDate, $xml->HEADER->STATCODE, "交易成功", "0", $xml->AP->STORE_DESC, $xml->AP->OL_Code_1, $xml->AP->OL_Code_2, $xml->AP->OL_Code_3);
	  	CDbShell::update("ledger", $field, $value, "CashFlowID = '".$xml->AP->ORDER_NO."'" );
        
        if (CDbShell::affected_rows() == 1) {
            
            echo '0000';

            if ('' != $SuccessURL || $NotifyURL != '') {
                $Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&RtnCode=1&MerTradeID='.$FirmRow['MerTradeID'].'&MerUserID='.$FirmRow['MerUserID']);

                $SendPOST['RtnCode'] = '1';
                $SendPOST['RtnMessage'] = '交易成功';
                $SendPOST['MerTradeID'] = $FirmRow['MerTradeID'];
                $SendPOST['MerProductID'] = $FirmRow['MerProductID'];
                $SendPOST['MerUserID'] = $FirmRow['MerUserID'];
                $SendPOST['PayInfo'] = $xml->AP->STORE_DESC;
                $SendPOST['Amount'] = intval($xml->AP->ACCOUNT);
                $SendPOST['PaymentDate'] = $_PaymentDate;
                $SendPOST['Validate'] = $Validate;
                if ($SuccessURL != '') {
                    try {
                        $strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

                        $fp = fopen('../Log/Family/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
                        fwrite($fp, ' ---------------- Send_Notify開始 ---------------- '.PHP_EOL);
                        fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
                        foreach($SendPOST as $key => $val)
                        {
                        	//while (list($key, $val) = each($SendPOST)) {
                            fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
                        }
                        fwrite($fp, '$strReturn =>'.$strReturn.PHP_EOL);
                        fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
                        fclose($fp);
                    } catch (Exception $e) {
                        $fp = fopen('../Log/Family/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
                        fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
                        fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
                        foreach($SendPOST as $key => $val)
                        {
                        	//while (list($key, $val) = each($SendPOST)) {
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

                        $fp = fopen('../Log/Family/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
                        fwrite($fp, ' ---------------- Send_Notify開始 ---------------- '.PHP_EOL);
                        fwrite($fp, 'NotifyURL =>'.$NotifyURL.PHP_EOL);
                        foreach($SendPOST as $key => $val)
                        {
                        	//while (list($key, $val) = each($SendPOST)) {
                            fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
                        }
                        fwrite($fp, '$strReturn =>'.$strReturn.PHP_EOL);
                        fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
                        fclose($fp);
                    } catch (Exception $e) {
                        $fp = fopen('../Log/Family/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
                        fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
                        fwrite($fp, 'NotifyURL =>'.$NotifyURL.PHP_EOL);
                        foreach($SendPOST as $key => $val)
                        {
                        	//while (list($key, $val) = each($SendPOST)) {
                            fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
                        }
                        fwrite($fp, '$strReturn =>'.$e->getMessage().PHP_EOL);
                        fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
                        fclose($fp);
                    }
                }
            } else {
                $fp = fopen('../Log/Family/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
                fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
                fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
                fwrite($fp, '$strReturn => 回傳網址是空的'.PHP_EOL);
                fclose($fp);
            }


        }else {
            echo "4001";
        }
    }else {

        $field = array("OrderID", "PaymentCode", "Period", "ClosingDate", "ExpectedRecordedDate", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State");
	  	$value = array($xml->HEADER->TERMINO, "超商繳款-[全家]", $Period, $ClosingDate, $ExpectedRecordedDate, $_PaymentDate, $_PaymentDate, $xml->HEADER->STATCODE, $xml->HEADER->STATDESC, "0");
          CDbShell::update("ledger", $field, $value, "CashFlowID = '".$xml->AP->ORDER_NO."' AND State = -1" );
          
        echo '0000';
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
?>