<?php
    ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	header('Access-Control-Allow-Origin: *');
	include_once("../BaseClass/Setting.php");
	include_once("../BaseClass/CDbShell.php");
	include_once("../BaseClass/CommonElement.php");

    @CDbShell::connect();
    CDbShell::query("SELECT * FROM Firm WHERE BINARY HashKey = '" . $_POST["HashKey"] . "' AND BINARY HashIV = '" . $_POST["HashIV"] . "'");
    $FirmRow = CDbShell::fetch_array();
    $SuccessURL = $FirmRow["SuccessURL"];
	$FailURL = $FirmRow["FailURL"];

    CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '7' AND FC.Enable = '1' LIMIT 1");  
    if (CDbShell::num_rows() == 0) {
        CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '7' AND (FC.FeeRatio > 0 OR FC.FixedFee > 0) LIMIT 1");  

    }
    $FCRow = CDbShell::fetch_array();

    //var_dump($_POST);
    $_cardholder= array(
        "phone_number"		=> $_POST["Mobile"],
        "name"				=> $_POST["Name"],			
        "email"				=> $_POST["Email"],
        "zip_code"			=> "",
        "address"			=> "",
        "national_id"		=> "",
        "member_id"			=> ""
    );

    $parameters = array(
        "prime"				=> $_POST["Prime"],
        "partner_key"		=> TapPay_PartnerKey,	
        "merchant_id"		=> "REGENT_CTBC",
        "amount"			=> (intval($_POST["Amount"])),
        "details"           => "商品",
        "cardholder"		=> json_decode(json_encode($_cardholder, true))
    );

    //$parameters["cardholder"] = json_decode(json_encode($_cardholder, true));

    $strReturn = SockPost2(TapPay, json_encode($parameters, JSON_UNESCAPED_UNICODE), $curlerror);

    $fp = fopen('../Log/TapPay/Notify_LOG'.date('YmdHi').'.txt', 'a');
	fwrite($fp, ' ---------------- Send_Notify開始 ---------------- '.PHP_EOL);
    fwrite($fp, "strReturn => ".$strReturn.PHP_EOL);
    fclose($fp);
    $Obj = json_decode($strReturn);
    
    //var_dump($Obj->transaction_time_millis);

    $MerTradeID	  = $_POST["MerTradeID"];
    $MerProductID = $_POST['MerProductID'];
    $MerUserID    = $_POST['MerUserID'];

    $Amount         = $Obj->amount;
    $Auth_Code      = $Obj->auth_code; 
    $_PaymentDate   = Date('Y-m-d H:i:s', ceil($Obj->transaction_time_millis / 1000));

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

    if ($Obj->status == 0 ) {
        $CashFlowID = Date("ymdHis").str_pad(floor(microtime() * 10000),4,'0',STR_PAD_LEFT).str_pad(rand(0,9999),4,'0',STR_PAD_LEFT);
        $OrderNo = $CashFlowID;
        $_CardNumber = $Obj->card_info->bin_code . "******". $Obj->card_info->last_four;
        $field = array("FirmSno", "OrderID", "CashFlowID", "MerTradeID", "MerProductID", "MerUserID", "PaymentName", "PaymentCode", "PaymentType", "Period", "ClosingDate", "ExpectedRecordedDate", "Total", "ClosingTotal", "Fee", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber", "AuthCode");
		$value = array($FirmRow["Sno"], $Obj->rec_trade_id, $CashFlowID, $_POST['MerTradeID'], $_POST['MerProductID'], $_POST['MerUserID'], "信用卡-TapPay", "信用卡-[TapPay]", 7, $Period, $ClosingDate, $ExpectedRecordedDate, intval($Obj->amount), intval($Obj->amount), $Fee, $_PaymentDate, $_PaymentDate, "0", "交易成功", "0", $_CardNumber, $Auth_Code );
		CDbShell::insert("ledger", $field, $value);
        if (CDbShell::affected_rows() == 1) {

			/*if ($Fee > $FirmRow['Fee']) {
				$field = array("Fee");
				$value = array($Fee);
				CDbShell::update("ledger", $field, $value, "VatmAccount = '".$obj->row->TRNACTNO."'");
			}*/

			if ('' != $SuccessURL && $_NotSendSuccess == 0) {
				$Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&RtnCode=1&MerTradeID='.$_POST["MerTradeID"].'&MerUserID='.$_POST['MerUserID']);

				$SendPOST['RtnCode'] = '1';
				$SendPOST['RtnMessage'] = '交易成功';
				$SendPOST['MerTradeID'] = $_POST["MerTradeID"];
				$SendPOST['MerProductID'] = $_POST['MerProductID'];
				$SendPOST['MerUserID'] = $_POST['MerUserID'];
				$SendPOST['PayInfo'] = $Obj->auth_code;
				$SendPOST['Amount'] = intval($Obj->amount);
				$SendPOST['PaymentDate'] = $_PaymentDate;
				$SendPOST['Validate'] = $Validate;
				try {
					$strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

					$fp = fopen('../Log/TapPay/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
					fwrite($fp, ' ---------------- Send_Notify開始 ---------------- '.PHP_EOL);
					fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
					while (list($key, $val) = each($SendPOST)) {
						fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
					}
					fwrite($fp, '$strReturn =>'.$strReturn.PHP_EOL);
					fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
					fclose($fp);
				} catch (Exception $e) {
					$fp = fopen('../Log/TapPay/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
					fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
					fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
					while (list($key, $val) = each($SendPOST)) {
						fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
					}
					fwrite($fp, '$strReturn =>'.$e->getMessage().PHP_EOL);
					fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
					fclose($fp);
				}
			} else {
				$fp = fopen('../Log/TapPay/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
				fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
				fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
				fwrite($fp, '$strReturn => 回傳網址是空的'.PHP_EOL);
				fclose($fp);
			}

			//$ResParameter["row"]["TransactionNo"] = $obj->row->TransactionNo;
			/*$ResParameter["Result"] = 1;
			$ResParameter["Message"] = "";
			$ResParameter["StatusCode"] = 200;

			echo json_encode($ResParameter);*/
			/*$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header($protocol . ' 200 ');*/
			
            include("../CreditSuccess.html");
			exit;
		}else {
			$fp = fopen('../Log/TapPay/Fail_LOG_'.date('YmdHi').'.txt', 'a');
			fwrite($fp, ' ---------------- Fail_LOG開始 ---------------- '.PHP_EOL);
			fwrite($fp, 'rec_trade_id =>'.$Obj->rec_trade_id.PHP_EOL);
			fclose($fp);

			/*//$ResParameter["row"]["TransactionNo"] = $obj->row->TransactionNo;
			$ResParameter["Result"] = 0;
			$ResParameter["Message"] = "Fail";
			$ResParameter["StatusCode"] = 543;

			echo json_encode($ResParameter);*/
			//header("HTTP/1.0 500 Internal Server Error");
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header($protocol . ' 200 ');
			exit;
		}
    }else {
        echo $strReturn;
        include("../CreditFail.html");
    }
    exit;

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

    function SockPost2($URL, $Query, &$curlerror){
        $headers = array(
            'Content-type: application/json',
            'x-api-key: '.TapPay_PartnerKey,
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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