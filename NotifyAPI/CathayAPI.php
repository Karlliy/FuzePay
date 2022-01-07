<?php
    ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	header('Access-Control-Allow-Origin: *');
	include_once("../BaseClass/Setting.php");
	include_once("../BaseClass/CDbShell.php");
	include_once("../BaseClass/CommonElement.php");
	
	$_IPV4 = get_client_ip(1, true);
    $_IP = get_client_ip(0, true);
	//print_r($_POST);
	$fp = fopen('../Log/Cathay/temp/Notify_LOG_'.date("YmdHis").'.txt', 'a');
	fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
	foreach($_POST as $key => $val)
	//while (list ($key, $val) = each ($_POST)) 
	{
		fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
    };	
    fwrite($fp, " ---------------- 開始GET ---------------- ".PHP_EOL);
    	foreach($_GET as $key => $val)
	//while (list ($key, $val) = each ($_GET)) 
	{
		fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
    };	
    $XmlFile = file_get_contents('php://input');
    fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
	fwrite($fp, "XmlFile =>".$XmlFile.PHP_EOL);	

	fwrite($fp, " ---------------- 開始IP ----------------".PHP_EOL);
	fwrite($fp, "\$_IPV4 =>".$_IPV4.PHP_EOL);	
	fwrite($fp, "\$_IP =>".$_IP.PHP_EOL);	
	fclose($fp);
	$xml = simplexml_load_string($XmlFile);
	
	//$_Data1 = base64_encode($xml->BODY->DATA);
	
	$_Data = mb_convert_encoding(@mcrypt_decrypt(MCRYPT_RIJNDAEL_128, "1234567890111213", base64_decode(hex2bin($xml->BODY->DATA)), MCRYPT_MODE_ECB, ""), "UTF-8", "Big5");
    //echo $_Data;
    $my_string = preg_replace(array('/\n/', '/\r/'), '#PH#', $_Data);
    $data_array = explode('#PH#', $my_string);
    //var_dump($data_array);
	//exit;
	//$i = 0;
	/*ob_start();
	var_dump($data_array);
	$data = ob_get_clean();*/
	$fp = fopen('../Log/Cathay/Really_LOG_'.date('YmdHi').'.txt', 'a');
	fwrite($fp, ' ---------------- Really_LOG開始 ---------------- '.PHP_EOL);
	fwrite($fp, print_r($data_array, TRUE).PHP_EOL);
	fclose($fp);
	
	@CDbShell::connect();
	$data_array = array_filter($data_array);
	$key = array_keys($data_array);
	$size = sizeOf($key);
	/*ob_start();
	var_dump($data_array);
	$data = ob_get_clean();*/
	$data_array = array_values($data_array);
	$fp = fopen('../Log/Cathay/Really_LOG_'.date('YmdHi').'.txt', 'a');
	fwrite($fp, ' ---------------- Really_LOG開始 ---------------- '.PHP_EOL);
	fwrite($fp, print_r($data_array, TRUE).PHP_EOL);
	fclose($fp);
	for ($i = 0; $i < $size; $i++) {
    //while (list ($k, $val) = @each ($data_array)) {
		$fp = fopen('../Log/Cathay/Really_LOG_'.date('YmdHi').'.txt', 'a');
		//fwrite($fp, ' ---------------- data_array_LOG開始 ---------------- '.PHP_EOL);
		fwrite($fp, "\$data_array[$i] > ".$data_array[$i].PHP_EOL);
		fclose($fp);

		/*$_OrderID       = trim(mb_substr($data_array[$key[$i]], 18, 9, "utf-8"));
		$_Amount        = intval(mb_substr($data_array[$key[$i]], 39, 13, "utf-8"));
		$_VirAccount    = mb_substr($data_array[$key[$i]], 68, 14, "utf-8");
		$_BankCode      = trim(mb_substr($data_array[$key[$i]], 97, 7, "utf-8"));
		$_Account       = trim(substr($data_array[$key[$i]], -34, 20));
		$_Date          = trim(substr($data_array[$key[$i]], -8));
		$_Time          = trim(substr($data_array[$key[$i]], -14, 6));*/
		$_OrderID       = trim(mb_substr($data_array[$i], 18, 9, "utf-8"));
		$_Amount        = intval(mb_substr($data_array[$i], 39, 13, "utf-8"));
		$_VirAccount    = mb_substr($data_array[$i], 68, 14, "utf-8");
		$_BankCode      = trim(mb_substr($data_array[$i], 97, 7, "utf-8"));
		$_Account       = trim(substr($data_array[$i], -34, 20));
		$_Date          = trim(substr($data_array[$i], -8));
		$_Time          = trim(substr($data_array[$i], -14, 6));
		
		$_PaymentDate = Date('Y-m-d H:i:s', strtotime($_Date.$_Time));
		
		$fp = fopen('../Log/Cathay/Really_LOG_'.date('YmdHi').'.txt', 'a');
		//fwrite($fp, ' ---------------- Really_LOG開始 ---------------- '.PHP_EOL);
		fwrite($fp, "DATA > ". $_OrderID."|".$_Amount."|".$_VirAccount."|".$_BankCode."|".$_Account."|".$_Date."|".$_Time."|".$_PaymentDate.PHP_EOL);
		fclose($fp);

        //if (is_numeric(mb_substr($data_array[$key[$i]], 68, 14, "utf-8")) ) {
		if (is_numeric($_VirAccount) ) {
            //echo $val."<br/>";
            //var_dump($_OrderID."|".$_Amount."|".$_VirAccount."|".$_BankCode."|".$_Account."|".$_Date."|".$_Time."|".$_PaymentDate);

			CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.ClosingTotal, L.Fee, L.State, L.NotifyURL FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.VatmAccount = '".$_VirAccount."'"); 
			$FirmRow = CDbShell::fetch_array();
			$_NotSendSuccess = 0;
			$SuccessURL = $FirmRow["SuccessURL"];
			$NotifyURL = $FirmRow["NotifyURL"];
			$FailURL = $FirmRow["FailURL"];
			$_NotSendSuccess = $FirmRow["NotSendSuccess"];
			
			//if ($FirmRow["ClosingTotal"] == 0) {
				CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '1' AND FC.Enable = '1' AND PF.Kind = '虛擬帳號' LIMIT 1");  
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
					case "Month":
						//$ExpectedRecordedDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month +".$FCRow["Day"]." day"));
						$Period = date("Y-m-01", strtotime($_PaymentDate)) . " ~ " . date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
						$ClosingDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
						break;
				}
				$ExpectedRecordedDate = CommonElement::CountHoliday($ClosingDate, $FCRow["Day"], true);
				
				$Fee = 0;
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

				$field = array("OrderID", "PaymentCode", "Period", "ClosingDate", "ExpectedRecordedDate", "ClosingTotal", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber");
				$value = array($_OrderID, "虛擬帳號-[國泰]", $Period, $ClosingDate, $ExpectedRecordedDate,  $_Amount, $_PaymentDate, $_PaymentDate, "0", "交易成功", "0", "(".$_BankCode.")".$_Account);
				CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VirAccount."'");

				if (CDbShell::affected_rows() == 1) {

					$fp = fopen('../Log/Cathay/Fee_LOG_'.date('YmdHi').'.txt', 'a');
					fwrite($fp, "Fee > ". $Fee.PHP_EOL);
					fclose($fp);
					if ($Fee > $FirmRow['Fee']) {
						$field = array("Fee");
						$value = array($Fee);
						CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_VirAccount."'");
					}

					if (('' != $SuccessURL || $NotifyURL != '') && $_NotSendSuccess == 0) {
						$Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&RtnCode=1&MerTradeID='.$FirmRow['MerTradeID'].'&MerUserID='.$FirmRow['MerUserID']);

						$SendPOST['RtnCode'] = '1';
						$SendPOST['RtnMessage'] = '交易成功';
						$SendPOST['MerTradeID'] = $FirmRow['MerTradeID'];
						$SendPOST['MerProductID'] = $FirmRow['MerProductID'];
						$SendPOST['MerUserID'] = $FirmRow['MerUserID'];
						$SendPOST["PayInfo"] = "(".$_BankCode.")".$_Account;
						$SendPOST['Amount'] =  $_Amount;
						$SendPOST['PaymentDate'] = $_PaymentDate;
						$SendPOST['Validate'] = $Validate;
						if ($SuccessURL != '') {
							try {
								$strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

								$fp = fopen('../Log/Cathay/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
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
								$fp = fopen('../Log/Cathay/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
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

								$fp = fopen('../Log/Cathay/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
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
								$fp = fopen('../Log/Cathay/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
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
						$fp = fopen('../Log/Cathay/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
						fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
						fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
						fwrite($fp, '$strReturn => 回傳網址是空的'.PHP_EOL);
						fclose($fp);
					}
				}else {
					/*$fp = fopen('../Log/Cathay/Fail_LOG_'.date('YmdHi').'.txt', 'a');
					fwrite($fp, ' ---------------- Fail_LOG開始 ---------------- '.PHP_EOL);
					fwrite($fp, 'VatmAccount =>'.$_VirAccount.PHP_EOL);
					fclose($fp);*/

				}
			//}
        }
	}
	
	CDbShell::DB_close();
	echo "0000";
	/*
	/*$Rxml = new SimpleXMLElement('<row/>');
	$Rxml->addChild("TransactionNo",$xml->MMK_ID);
	$Rxml->addChild("Result",1);
	$Rxml->addChild("Message",'');
	$Rxml->addChild("StatusCode","200");
	$_ReturnXML = $Rxml->asXML();

	$_ReturnXML = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8"?>', $_ReturnXML);
	echo $_ReturnXML;*/

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
?>