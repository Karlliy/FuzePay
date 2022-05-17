<?php
    ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	header('Access-Control-Allow-Origin: *');
	include_once("../BaseClass/Setting.php");
	include_once("../BaseClass/CDbShell.php");
	include_once("../BaseClass/CommonElement.php");

	preg_match('/(\/)(\w+)$/', $_SERVER["UNENCODED_URL"], $_Searched);

    //echo $_Searched[COUNT($_Searched)-1];
    //exit;

    if (strcasecmp($_Searched[COUNT($_Searched)-1], "SevenCheckCode") == 0) {
        CheckCode();

    }else if (strcasecmp($_Searched[COUNT($_Searched)-1], "SevenNotify") == 0) {
        Notify();
    }
	function CheckCode() {
		$fp = fopen('../Log/711/CheckCode_LOG_'.date("YmdHis").'.txt', 'a');
		fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
		//while (list ($key, $val) = each ($_POST)) 		
		foreach($_POST as $key => $val)
		{
			fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
		};	
		$XmlFile = file_get_contents('php://input');
		fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
		fwrite($fp, "XmlFile =>".$XmlFile.PHP_EOL);	
		fclose($fp);
		
		$xml = simplexml_load_string($_POST['XMLData']);

		$fp = fopen('../Log/711/CheckCode_LOG_'.date("YmdHis").'.txt', 'a');
		fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
		fwrite($fp, "\$xml->KEY1 => ".$xml->KEY1.PHP_EOL);
		fclose($fp);
		
		@CDbShell::connect();
		CDbShell::query("SELECT Sno, MerTradeID, MerProductID, MerUserID, Total, State FROM Ledger WHERE VatmAccount = '".$xml->KEY1."'"); 
		if (CDbShell::num_rows() == 1) {
			$LRow = CDbShell::fetch_array();

			if ($LRow["State"] == '0' || $LRow["State"] == '1') {
				$Rxml = new SimpleXMLElement('<?xml version="1.0" encoding="Big5"?><SHOWDATA/>');
				$Rxml->addChild("BUSINESS","0700QC2");
				$Rxml->addChild("STOREID",$xml->STOREID);
				$Rxml->addChild("SHOPID",$xml->SHOPID);
				$Rxml->addChild("DETAILED_NUM",$xml->DETAILED_NUM);
				$Rxml->addChild("PRODUCT_CODE",$xml->PRODUCT_CODE);
				$Rxml->addChild("STATUS_CODE","1003");
				$Rxml->addChild("STATUS_DESC","該代碼已繳費");
				$Rxml->addChild("SUB1",$xml->SUB1);
				$Rxml->addChild("SUB2",$xml->SUB2);
				$Rxml->addChild("SUB3",$xml->SUB3);
				$Rxml->addChild("KEY1",$xml->KEY1);
				$Rxml->addChild("KEY2",$xml->KEY2);
				$Rxml->addChild("KEY3",$xml->KEY3);
				$Rxml->addChild("KEY4",$xml->KEY4);
				$Rxml->addChild("KEY5",$xml->KEY5);
				$Rxml->addChild("TOTALAMOUNT",0);
				$Rxml->addChild("TOTALCOUNT",0);
				$Rxml->addChild("LISTDATA");
				$Rxml->LISTDATA->addChild("SERIALNO","00");
				$Rxml->LISTDATA->addChild("PRINT","");
				$Rxml->LISTDATA->addChild("CP_ORDER","");
				$Rxml->LISTDATA->addChild("DATA_1","");
				$Rxml->LISTDATA->addChild("DATA_2","");
				$Rxml->LISTDATA->addChild("DATA_3","");
				$Rxml->LISTDATA->addChild("DATA_4","");
				$Rxml->LISTDATA->addChild("DATA_5","");
				$Rxml->LISTDATA->addChild("DATA_6","");
				$Rxml->LISTDATA->addChild("DATA_7","");
				$Rxml->LISTDATA->addChild("DATA_8","");
				$_ReturnXML = $Rxml->asXML();
				echo $_ReturnXML;
				exit;
			}else {

				$Rxml = new SimpleXMLElement('<?xml version="1.0" encoding="Big5"?><SHOWDATA/>');
				$Rxml->addChild("BUSINESS","0700QC2");
				$Rxml->addChild("STOREID",$xml->STOREID);
				$Rxml->addChild("SHOPID",$xml->SHOPID);
				$Rxml->addChild("DETAILED_NUM",$xml->DETAILED_NUM);
				$Rxml->addChild("PRODUCT_CODE",$xml->PRODUCT_CODE);
				$Rxml->addChild("STATUS_CODE","0000");
				$Rxml->addChild("STATUS_DESC","成功");
				$Rxml->addChild("SUB1",$xml->SUB1);
				$Rxml->addChild("SUB2",$xml->SUB2);
				$Rxml->addChild("SUB3",$xml->SUB3);
				$Rxml->addChild("KEY1",$xml->KEY1);
				$Rxml->addChild("KEY2",$xml->KEY2);
				$Rxml->addChild("KEY3",$xml->KEY3);
				$Rxml->addChild("KEY4",$xml->KEY4);
				$Rxml->addChild("KEY5",$xml->KEY5);
				$Rxml->addChild("TOTALAMOUNT",intval($LRow["Total"]));
				$Rxml->addChild("TOTALCOUNT",1);
				$LISTDATA = $Rxml->addChild("LISTDATA");
				$LISTDATA->addChild("SERIALNO","00");
				$LISTDATA->addChild("PRINT","N");
				$LISTDATA->addChild("CP_ORDER","廠商訂單編號");
				$LISTDATA->addChild("DATA_1","繳費內容");
				$LISTDATA->addChild("DATA_2","用戶號碼");
				$LISTDATA->addChild("DATA_3","用戶名稱");
				$LISTDATA->addChild("DATA_4","繳費金額");
				$LISTDATA->addChild("DATA_5","說明");
				$LISTDATA->addChild("DATA_6","");
				$LISTDATA->addChild("DATA_7","");
				$LISTDATA->addChild("DATA_8","");
				$LISTDATA2 = $Rxml->addChild("LISTDATA");
				$LISTDATA2->addChild("SERIALNO","01");
				$LISTDATA2->addChild("PRINT","Y");
				$LISTDATA2->addChild("CP_ORDER",$LRow["Sno"]);
				$LISTDATA2->addChild("DATA_1",$LRow["MerTradeID"]);
				$LISTDATA2->addChild("DATA_2",$LRow["MerProductID"]);
				$LISTDATA2->addChild("DATA_3",substr_cut($LRow["MerUserID"]));
				$LISTDATA2->addChild("DATA_4",$LRow["Total"]);
				$LISTDATA2->addChild("DATA_5","請確認商品避免詐騙");
				$LISTDATA2->addChild("DATA_6","");
				$LISTDATA2->addChild("DATA_7","");
				$LISTDATA2->addChild("DATA_8","");
				$_ReturnXML = $Rxml->asXML();
				echo $_ReturnXML;
				exit;
			}
		}else {
			$Rxml = new SimpleXMLElement('<?xml version="1.0" encoding="Big5"?><SHOWDATA/>');
			$Rxml->addChild("BUSINESS","0700QC2");
			$Rxml->addChild("STOREID",$xml->STOREID);
			$Rxml->addChild("SHOPID",$xml->SHOPID);
			$Rxml->addChild("DETAILED_NUM",$xml->DETAILED_NUM);
			$Rxml->addChild("PRODUCT_CODE",$xml->PRODUCT_CODE);
			$Rxml->addChild("STATUS_CODE","1003");
			$Rxml->addChild("STATUS_DESC","查無該代碼");
			$Rxml->addChild("SUB1",$xml->SUB1);
			$Rxml->addChild("SUB2",$xml->SUB2);
			$Rxml->addChild("SUB3",$xml->SUB3);
			$Rxml->addChild("KEY1",$xml->KEY1);
			$Rxml->addChild("KEY2",$xml->KEY2);
			$Rxml->addChild("KEY3",$xml->KEY3);
			$Rxml->addChild("KEY4",$xml->KEY4);
			$Rxml->addChild("KEY5",$xml->KEY5);
			$Rxml->addChild("TOTALAMOUNT",0);
			$Rxml->addChild("TOTALCOUNT",0);
			$Rxml->addChild("LISTDATA");
			$Rxml->LISTDATA->addChild("SERIALNO","00");
			$Rxml->LISTDATA->addChild("PRINT","");
			$Rxml->LISTDATA->addChild("CP_ORDER","");
			$Rxml->LISTDATA->addChild("DATA_1","");
			$Rxml->LISTDATA->addChild("DATA_2","");
			$Rxml->LISTDATA->addChild("DATA_3","");
			$Rxml->LISTDATA->addChild("DATA_4","");
			$Rxml->LISTDATA->addChild("DATA_5","");
			$Rxml->LISTDATA->addChild("DATA_6","");
			$Rxml->LISTDATA->addChild("DATA_7","");
			$Rxml->LISTDATA->addChild("DATA_8","");
			$_ReturnXML = $Rxml->asXML();
			echo $_ReturnXML;
			exit;
		}
	}

	function Notify() {
		//print_r($_POST);
		$fp = fopen('../Log/711/Notify_LOG_'.date("YmdHis").'.txt', 'a');
		fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
		
		//while (list ($key, $val) = each ($_POST)) 
		foreach($_POST as $key => $val)
		{
			fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
		};	
		$XmlFile = file_get_contents('php://input');
		fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
		fwrite($fp, "XmlFile =>".$XmlFile.PHP_EOL);	
		fclose($fp);
		
		$xml = simplexml_load_string($_POST['XMLData']);
		
		$_PaymentDate = date("Y-m-d H:i:s", strtotime($xml->PAYDATE));

		@CDbShell::connect();
		CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.Fee, L.NotifyURL FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.Sno = '".$xml->USERDATA1."'"); 
		$FirmRow = CDbShell::fetch_array();
		$SuccessURL = $FirmRow["SuccessURL"];
		$NotifyURL = $FirmRow["NotifyURL"];
		$FailURL = $FirmRow["FailURL"];
		
		CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '3' AND FC.Enable = '1' AND PF.Mode = '711' LIMIT 1");  
		if (CDbShell::num_rows() == 0) {
			CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '3' AND (FC.FeeRatio > 0 OR FC.FixedFee > 0) LIMIT 1");  
		
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
			$Fee = floatval($_POST['AMT']) * floatval($FCRow["FeeRatio"] / 100);
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

		if ($xml->STATUS_CODE == "0000") {
			$field = array("OrderID", "PaymentType", "PaymentName", "PaymentCode", "Period", "ClosingDate", "ExpectedRecordedDate", "ClosingTotal", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber", "Parameter1", "Parameter2", "Parameter3");
			$value = array($xml->DETAIL_NUM, "3", "超商-711", "超商繳款-[7-11]", $Period, $ClosingDate, $ExpectedRecordedDate, intval($xml->AMOUNT), $_PaymentDate, $_PaymentDate, $xml->STATUS_CODE, "交易成功", "0", $xml->STOREID, $xml->BARCODE1, $xml->BARCODE2, $xml->BARCODE3);
			CDbShell::update("ledger", $field, $value, "Sno = '".$xml->USERDATA1."'" );
		
			if (CDbShell::affected_rows() == 1) {

				$Rxml = new SimpleXMLElement('<?xml version="1.0" encoding="Big5"?><PAYMONEY_R/>');
				$Rxml->addChild("SHOPID",$xml->SHOPID);
				$Rxml->addChild("DETAIL_NUM",$xml->DETAIL_NUM);
				$Rxml->addChild("STATUS_CODE","0000");
				$Rxml->addChild("STATUS_DESC","成功");
				$Rxml->addChild("CONFIRM","OK");
				$_ReturnXML = $Rxml->asXML();
				echo $_ReturnXML;

				if ('' != $SuccessURL || $NotifyURL != '') {
					$Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&RtnCode=1&MerTradeID='.$FirmRow['MerTradeID'].'&MerUserID='.$FirmRow['MerUserID']);

					$SendPOST['RtnCode'] = '1';
					$SendPOST['RtnMessage'] = '交易成功';
					$SendPOST['MerTradeID'] = $FirmRow['MerTradeID'];
					$SendPOST['MerProductID'] = $FirmRow['MerProductID'];
					$SendPOST['MerUserID'] = $FirmRow['MerUserID'];
					$SendPOST['PayInfo'] = $xml->STOREID;
					$SendPOST['Amount'] = intval($xml->AMOUNT);
					$SendPOST['PaymentDate'] = $_PaymentDate;
					$SendPOST['Validate'] = $Validate;
					if ($SuccessURL != '') {							
						try {
							$strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

							$fp = fopen('../Log/711/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
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
							$fp = fopen('../Log/711/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
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

							$fp = fopen('../Log/711/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
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
							$fp = fopen('../Log/711/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
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
					$fp = fopen('../Log/711/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
					fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
					fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
					fwrite($fp, '$strReturn => 回傳網址是空的'.PHP_EOL);
					fclose($fp);
				}
			}else {
				$Rxml = new SimpleXMLElement('<?xml version="1.0" encoding="Big5"?><PAYMONEY_R/>');
				$Rxml->addChild("SHOPID",$xml->SHOPID);
				$Rxml->addChild("DETAIL_NUM",$xml->DETAIL_NUM);
				$Rxml->addChild("STATUS_CODE","1111");
				$Rxml->addChild("STATUS_DESC","核銷失敗");
				$Rxml->addChild("CONFIRM","FAIL");
				$_ReturnXML = $Rxml->asXML();
				echo $_ReturnXML;
				exit;
			}

		}else {
			$Rxml = new SimpleXMLElement('<?xml version="1.0" encoding="Big5"?><PAYMONEY_R/>');
			$Rxml->addChild("SHOPID",$xml->SHOPID);
			$Rxml->addChild("DETAIL_NUM",$xml->DETAIL_NUM);
			$Rxml->addChild("STATUS_CODE","1111");
			$Rxml->addChild("STATUS_DESC","核銷失敗");
			$Rxml->addChild("CONFIRM","FAIL");
			$_ReturnXML = $Rxml->asXML();
			echo $_ReturnXML;
			exit;
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

	function substr_cut($user_name){
		$strlen     = mb_strlen($user_name, 'utf-8');
		$firstStr     = mb_substr($user_name, 0, 1, 'utf-8');
		$lastStr     = mb_substr($user_name, -1, 1, 'utf-8');
		return $strlen == 2 ? $firstStr . str_repeat('O', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("O", $strlen - 2) . $lastStr;
	}