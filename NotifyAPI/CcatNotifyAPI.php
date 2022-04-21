<?php
    ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
    ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
    ini_set('error_reporting',E_ALL & ~E_NOTICE);
    header('Access-Control-Allow-Origin: *');
    include_once("../BaseClass/Setting.php");
    include_once("../BaseClass/CDbShell.php");
    include_once("../BaseClass/CommonElement.php");

    $fp = fopen('../Log/Ccat/Notify_LOG_'.date("YmdHis").'.txt', 'a');
    fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);

    foreach($_POST as $key => $val)
    {
        fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
    };	
    $XmlFile = file_get_contents('php://input');
    fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
    fwrite($fp, "XmlFile =>".$XmlFile.PHP_EOL);	
    fclose($fp);

    $Obj = json_decode($XmlFile);

    @CDbShell::connect();
    CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.Fee, L.NotifyURL FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.CashFlowID = '".$Obj->order_no."'"); 
    $FirmRow = CDbShell::fetch_array();
    $SuccessURL = $FirmRow["SuccessURL"];
    $NotifyURL = $FirmRow["NotifyURL"];

    //$_PaymentDate =  date('Y-m-d H:i:s', strtotime($Obj->modify_time));
    $_PaymentDate = date('Y-m-d H:i:s', strtotime($Obj->modify_time . " +8 hours")); 

    
    //$Obj2 = json_decode($Obj->payment_detail);
    //$_CardNumber = $Obj2->bank_id.$Obj2->virtual_account;
    $_CardNumber = $Obj->payment_detail->storeId;

    if ($Obj->payment_detail->virtual_account != NULL) {
        $PaymentName = "虛擬帳號-黑貓";
        $PaymentCode = "虛擬帳號-[黑貓]";
        CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '1' AND FC.Enable = '1' AND PF.Mode = '黑貓Pay' LIMIT 1");  
        if (CDbShell::num_rows() == 0) {
            CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '1' AND (FC.FeeRatio > 0 OR FC.FixedFee > 0) LIMIT 1");  
        }
    }else if ($Obj->payment_detail->ibon_code != NULL) {
        $PaymentName = "超商-黑貓7-11";
        $PaymentCode = "超商繳款-[黑貓7-11]";
        CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '3' AND FC.Enable = '1' AND PF.Mode = '黑貓7-11' LIMIT 1");  
        if (CDbShell::num_rows() == 0) {
            CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '3' AND (FC.FeeRatio > 0 OR FC.FixedFee > 0) LIMIT 1");  
        }
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
        $Fee = floatval($Obj->amount) * floatval($FCRow["FeeRatio"] / 100);
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

    if ($Obj->status == "B") {
        $field = array("OrderID", "PaymentName", "PaymentCode", "Period", "ClosingDate", "ExpectedRecordedDate", "ClosingTotal", "Fee", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber");
        $value = array($Obj->trans_id, $PaymentName, $PaymentCode, $Period, $ClosingDate, $ExpectedRecordedDate, intval($Obj->amount), $Fee, $_PaymentDate, $_PaymentDate, $Obj->status, "交易成功", "0", $_CardNumber);
        CDbShell::update("ledger", $field, $value, "CashFlowID = '".$Obj->order_no."'" );
    
        if (CDbShell::affected_rows() == 1) {
            
            echo "OK";

            if ('' != $SuccessURL || $NotifyURL != '') {
                $Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&RtnCode=1&MerTradeID='.$FirmRow['MerTradeID'].'&MerUserID='.$FirmRow['MerUserID']);

                $SendPOST['RtnCode'] = '1';
                $SendPOST['RtnMessage'] = '交易成功';
                $SendPOST['MerTradeID'] = $FirmRow['MerTradeID'];
                $SendPOST['MerProductID'] = $FirmRow['MerProductID'];
                $SendPOST['MerUserID'] = $FirmRow['MerUserID'];
                $SendPOST['PayInfo'] = $_CardNumber;
                $SendPOST['Amount'] = intval($Obj->amount);
                $SendPOST['PaymentDate'] = $_PaymentDate;
                $SendPOST['Validate'] = $Validate;
                if ($SuccessURL != '') {							
                    try {
                        $strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

                        $fp = fopen('../Log/Ccat/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
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
                        $fp = fopen('../Log/Ccat/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
                        fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
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

                        $fp = fopen('../Log/Ccat/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
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
                        $fp = fopen('../Log/Ccat/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
                        fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
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
                $fp = fopen('../Log/Ccat/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
                fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
                fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
                fwrite($fp, '$strReturn => 回傳網址是空的'.PHP_EOL);
                fclose($fp);
            }
        }else {
            
            echo "OK";
            exit;
        }
    }else {
        echo "OK";
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
