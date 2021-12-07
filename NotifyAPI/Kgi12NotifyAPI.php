<?php
    ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	header('Access-Control-Allow-Origin: *');
	include_once("../BaseClass/Setting.php");
	include_once("../BaseClass/CDbShell.php");
    include_once("../BaseClass/CommonElement.php");    

    $fp = fopen('../Log/KGI12/Notify_LOG_'.date("YmdHis").'.txt', 'a');
	fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
	while (list ($key, $val) = each ($_POST)) 
	{
		fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
    };	
    fwrite($fp, " ---------------- 開始GET ---------------- ".PHP_EOL);
	while (list ($key, $val) = each ($_GET)) 
	{
		fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
    };	
    $XmlFile = file_get_contents('php://input');
    fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
	fwrite($fp, "XmlFile =>".$XmlFile.PHP_EOL);	

    fclose($fp);

    $obj = json_decode($XmlFile);

    $_PaymentDate = date("Y-m-d H:i:s", strtotime($obj->TDATE.$obj->TTIME));
    $_VatmAccount = substr($obj->ACCNO, -12); ;

    @CDbShell::connect();
	CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.Fee, L.NotifyURL FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.VatmAccount = '".$_VatmAccount."' ORDER BY Sno LIMIT 1"); 
	$FirmRow = CDbShell::fetch_array();
	$_NotSendSuccess = 0;
	$SuccessURL = $FirmRow["SuccessURL"];
	$NotifyURL = $FirmRow["NotifyURL"];
	$FailURL = $FirmRow["FailURL"];
	$_NotSendSuccess = $FirmRow["NotSendSuccess"];
	
	CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '1' AND FC.Enable = '1' AND PF.Kind = '虛擬帳號12碼' LIMIT 1");  
	if (CDbShell::num_rows() == 0) {
		CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '1' AND PF.Kind = '虛擬帳號12碼' AND (FC.FeeRatio > 0 OR FC.FixedFee > 0) LIMIT 1");  
	
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
		$Fee = floatval($obj->AMT) * floatval($FCRow["FeeRatio"] / 100);
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
	CDbShell::query("SELECT Sno FROM ledger WHERE OrderID = '".$obj->SEQNO."' AND VatmAccount = '".$_VatmAccount."' AND PaymentDate > '".date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s") ." -1 month"))."'");
	if (CDbShell::num_rows() == 0) {
		$CashFlowID = date('ymdHis').str_pad(floor(microtime() * 10000), 4, '0', STR_PAD_LEFT).str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

		$field = array("FirmSno", "OrderID", "CashFlowID", "MerTradeID", "MerProductID", "MerUserID", "PaymentName", "PaymentCode", "PaymentType", "Period", "ClosingDate", "ExpectedRecordedDate", "ClosingTotal", "Fee", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber", "VatmAccount");
		$value = array($FirmRow["Sno"], $obj->SEQNO, $CashFlowID, $FirmRow['MerTradeID'], $FirmRow['MerProductID'], $FirmRow['MerUserID'], "虛擬帳號12碼-凱基", "虛擬帳號-[凱基12碼]", 1, $Period, $ClosingDate, $ExpectedRecordedDate, intval($obj->AMT), $Fee, $_PaymentDate, $_PaymentDate, "0", "交易成功", "0", $obj->RACCNO, $_VatmAccount);
		//CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VatmAccount."'");
		CDbShell::insert("ledger", $field, $value);
		if (CDbShell::affected_rows() == 1) {

			/*if ($Fee > $FirmRow['Fee']) {
				$field = array("Fee");
				$value = array($Fee);
				CDbShell::update("ledger", $field, $value, "VatmAccount = '".$obj->row->TRNACTNO."'");
			}*/

			if (('' != $SuccessURL || $NotifyURL != '') && $_NotSendSuccess == 0) {
				$Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&RtnCode=1&MerTradeID='.$FirmRow['MerTradeID'].'&MerUserID='.$FirmRow['MerUserID']);

				$SendPOST['RtnCode'] = '1';
				$SendPOST['RtnMessage'] = '交易成功';
				$SendPOST['MerTradeID'] = $FirmRow['MerTradeID'];
				$SendPOST['MerProductID'] = $FirmRow['MerProductID'];
				$SendPOST['MerUserID'] = $FirmRow['MerUserID'];
				$SendPOST['PayInfo'] = $obj->RACCNO;
				$SendPOST['Amount'] = intval($obj->AMT);
				$SendPOST['PaymentDate'] = $_PaymentDate;
				$SendPOST['Validate'] = $Validate;
				if ($SuccessURL != '') {
					try {
						$strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

						$fp = fopen('../Log/KGI12/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
						fwrite($fp, ' ---------------- Send_Notify開始 ---------------- '.PHP_EOL);
						fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
						while (list($key, $val) = each($SendPOST)) {
							fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
						}
						fwrite($fp, '$strReturn =>'.$strReturn.PHP_EOL);
						fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
						fclose($fp);
					} catch (Exception $e) {
						$fp = fopen('../Log/KGI12/CHB/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
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

						$fp = fopen('../Log/KGI12/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
						fwrite($fp, ' ---------------- Send_Notify開始 ---------------- '.PHP_EOL);
						fwrite($fp, 'NotifyURL =>'.$NotifyURL.PHP_EOL);
						while (list($key, $val) = each($SendPOST)) {
							fwrite($fp, 'key =>'.$key.'  val=>'.$val.PHP_EOL);
						}
						fwrite($fp, '$strReturn =>'.$strReturn.PHP_EOL);
						fwrite($fp, '$curlerror =>'.$curlerror.PHP_EOL);
						fclose($fp);
					} catch (Exception $e) {
						$fp = fopen('../Log/KGI12/CHB/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
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
				$fp = fopen('../Log/KGI12/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
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
			echo "ok!";
			exit;
		}else {
			$fp = fopen('../Log/KGI12/Fail_LOG_'.date('YmdHi').'.txt', 'a');
			fwrite($fp, ' ---------------- Fail_LOG開始 ---------------- '.PHP_EOL);
			fwrite($fp, 'VatmAccount =>'.$_VatmAccount.PHP_EOL);
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
		$fp = fopen('../Log/KGI12/RepeatDeposit_LOG_'.date('YmdHi').'.txt', 'a');
		fwrite($fp, ' ---------------- Fail_LOG開始 ---------------- '.PHP_EOL);
		fwrite($fp, 'OrderID => '.$obj->SEQNO.PHP_EOL);
		fclose($fp);
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