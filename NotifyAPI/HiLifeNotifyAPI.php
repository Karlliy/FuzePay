<?php
    ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	header('Access-Control-Allow-Origin: *');
	include_once("../BaseClass/Setting.php");
	include_once("../BaseClass/CDbShell.php");
	include_once("../BaseClass/CommonElement.php");

	preg_match('/(\/)(\w+)\?/', $_SERVER["UNENCODED_URL"], $_Searched);

	$fp = fopen('../Log/HiLife/_LOG_'.date("YmdHis").'.txt', 'a');
	fwrite($fp, "UNENCODED_URL =>".$_SERVER["UNENCODED_URL"].PHP_EOL);
	fclose($fp);		
    //echo $_Searched[COUNT($_Searched)-1];
    //exit;

    if (strcasecmp($_Searched[COUNT($_Searched)-1], "HiLifeCheckCode") == 0) {
        CheckCode();

    }else if (strcasecmp($_Searched[COUNT($_Searched)-1], "HiLifeNotify") == 0) {
        Notify();
    }

    function CheckCode() {
		$fp = fopen('../Log/HiLife/CheckCode_LOG_'.date("YmdHis").'.txt', 'a');
		fwrite($fp, " ---------------- 開始GET ---------------- ".PHP_EOL);
		//while (list ($key, $val) = each ($_GET)) 
		foreach($_GET as $key => $val)
		{
			fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
		};	
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
		
		if (preg_match("/^(YAN)[0-9]{13,13}$/", $_GET["ORDER_NO"])) {
			@CDbShell::connect();
			CDbShell::query("SELECT Sno, MerTradeID, MerProductID, MerUserID, Total, State FROM Ledger WHERE VatmAccount = '".$_GET["ORDER_NO"]."'"); 
			if (CDbShell::num_rows() == 1) {
				$LRow = CDbShell::fetch_array();

				if ($LRow["State"] == '0' || $LRow["State"] == '1') {
					$Response = "";
					$Response .= "SHOP_ID=".$_GET["SHOP_ID"].PHP_EOL;
					$Response .= "TRANS_NO=".$_GET["TRANS_NO"].PHP_EOL;
					$Response .= "MMK_ID=".$_GET["MMK_ID"].PHP_EOL;
					$Response .= "ORDER_NO=".$_GET["ORDER_NO"].PHP_EOL;
					$Response .= "ACCOUNT=".PHP_EOL;
					$Response .= "RESULT_CODE=1001".PHP_EOL;
					$Response .= "RESULT=該代碼已繳費".PHP_EOL;
					$Response .= "RECEIPT=1".PHP_EOL;
					$Response .= "PRD_ITEM=".Simplify_Company.PHP_EOL;
					$Response .= "PRD_NAME=".Simplify_Company."代收".PHP_EOL;
					$Response .= "AMOUNT=".$LRow["Total"].PHP_EOL;
					$Response .= "FEE=0".PHP_EOL;
					$Response .= "TO_AMOUNT=".$LRow["Total"].PHP_EOL;
					echo $Response;
					exit;
				}else {
					$Response = "";
					$Response .= "SHOP_ID=".$_GET["SHOP_ID"].PHP_EOL;
					$Response .= "TRANS_NO=".$_GET["TRANS_NO"].PHP_EOL;
					$Response .= "MMK_ID=".$_GET["MMK_ID"].PHP_EOL;
					$Response .= "ORDER_NO=".$_GET["ORDER_NO"].PHP_EOL;
					$Response .= "ACCOUNT=".PHP_EOL;
					$Response .= "RESULT_CODE=0000".PHP_EOL;
					$Response .= "RESULT=".PHP_EOL;
					$Response .= "RECEIPT=1".PHP_EOL;
					$Response .= "PRD_ITEM=".Simplify_Company.PHP_EOL;
					$Response .= "PRD_NAME=".Simplify_Company."代收".PHP_EOL;
					$Response .= "AMOUNT=".$LRow["Total"].PHP_EOL;
					$Response .= "FEE=0".PHP_EOL;
					$Response .= "TO_AMOUNT=".$LRow["Total"].PHP_EOL;
					echo $Response;
					exit;
				}
			}else {
				$Response = "";
				$Response .= "SHOP_ID=".$_GET["SHOP_ID"].PHP_EOL;
				$Response .= "TRANS_NO=".$_GET["TRANS_NO"].PHP_EOL;
				$Response .= "MMK_ID=".$_GET["MMK_ID"].PHP_EOL;
				$Response .= "ORDER_NO=".$_GET["ORDER_NO"].PHP_EOL;
				$Response .= "ACCOUNT=".PHP_EOL;
				$Response .= "RESULT_CODE=1002".PHP_EOL;
				$Response .= "RESULT=查無該代碼".PHP_EOL;
				$Response .= "RECEIPT=1".PHP_EOL;
				$Response .= "PRD_ITEM=".Simplify_Company.PHP_EOL;
				$Response .= "PRD_NAME=".Simplify_Company."代收".PHP_EOL;
				$Response .= "AMOUNT=".$LRow["Total"].PHP_EOL;
				$Response .= "FEE=0".PHP_EOL;
				$Response .= "TO_AMOUNT=".$LRow["Total"].PHP_EOL;
				echo $Response;
				exit;
			}
		}
	}

    function Notify() {
		//print_r($_POST);
		$fp = fopen('../Log/HiLife/Notify_LOG_'.date("YmdHis").'.txt', 'a');
        fwrite($fp, " ---------------- 開始GET ---------------- ".PHP_EOL);
		//while (list ($key, $val) = each ($_GET)) 
		foreach($_GET as $key => $val)
		{
			fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
		};	
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
		if (preg_match("/^(YAN)[0-9]{13,13}$/", $_GET["ORDER_NO"])) {
			//$_PaymentDate = date("Y-m-d H:i:s");
			$_PaymentDate = date("Y-m-d H:i:s", strtotime($_GET["PAY_DATE"].$_GET["PAY_TIME"]));
	
			@CDbShell::connect();
			CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.Fee, L.NotifyURL FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.VatmAccount = '".$_GET["ORDER_NO"]."'"); 
			$FirmRow = CDbShell::fetch_array();
			$SuccessURL = $FirmRow["SuccessURL"];
			$NotifyURL = $FirmRow["NotifyURL"];
			$FailURL = $FirmRow["FailURL"];
			
			CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '6' AND FC.Enable = '1' AND PF.Mode = '萊爾富' LIMIT 1");  
			if (CDbShell::num_rows() == 0) {
				CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '6' AND (FC.FeeRatio > 0 OR FC.FixedFee > 0) LIMIT 1");  
			
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
				$Fee = floatval($_GET["AMOUNT"]) * floatval($FCRow["FeeRatio"] / 100);
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

			//if ($xml->STATUS_CODE == "0000") {
			$field = array("OrderID", "PaymentType", "PaymentName", "PaymentCode", "Period", "ClosingDate", "ExpectedRecordedDate", "ClosingTotal", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber");
			$value = array($_GET["TRANS_NO"], "6", "超商-萊爾富", "超商繳款-[萊爾富]", $Period, $ClosingDate, $ExpectedRecordedDate, intval($_GET["AMOUNT"]), $_PaymentDate, $_PaymentDate, "0000", "交易成功", "0", $_GET["SHOP_ID"]);
			CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_GET["ORDER_NO"]."'" );
		
			if (CDbShell::affected_rows() == 1) {
				$Response = "";
				$Response .= "SHOP_ID=".$_GET["SHOP_ID"].PHP_EOL;
				$Response .= "TRANS_NO=".$_GET["TRANS_NO"].PHP_EOL;
				$Response .= "MMK_ID=".$_GET["MMK_ID"].PHP_EOL;
				$Response .= "ORDER_NO=".$_GET["ORDER_NO"].PHP_EOL;
				$Response .= "TITLE=".Simplify_Company.PHP_EOL;
				$Response .= "A_COUNT=0".PHP_EOL;
				$Response .= "B_COUNT=0".PHP_EOL;
				$Response .= "C_COUNT=0".PHP_EOL;
				$Response .= "PRN_COUNT=0".PHP_EOL;                
				$Response .= "AMOUNT=".$_GET["AMOUNT"].PHP_EOL;
				$Response .= "PAY_AMOUNT=".$_GET["AMOUNT"].PHP_EOL;
				$Response .= "PAY_KIND=2".PHP_EOL;
				$Response .= "CARD_NO=".PHP_EOL;
				$Response .= "CON_F=1".PHP_EOL;
				$Response .= "REASON=".PHP_EOL;
				echo $Response;

				if ('' != $SuccessURL || $NotifyURL != '') {
					$Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&RtnCode=1&MerTradeID='.$FirmRow['MerTradeID'].'&MerUserID='.$FirmRow['MerUserID']);

					$SendPOST['RtnCode'] = '1';
					$SendPOST['RtnMessage'] = '交易成功';
					$SendPOST['MerTradeID'] = $FirmRow['MerTradeID'];
					$SendPOST['MerProductID'] = $FirmRow['MerProductID'];
					$SendPOST['MerUserID'] = $FirmRow['MerUserID'];
					$SendPOST['PayInfo'] = $_GET["SHOP_ID"];
					$SendPOST['Amount'] = intval($_GET["AMOUNT"]);
					$SendPOST['PaymentDate'] = $_PaymentDate;
					$SendPOST['Validate'] = $Validate;
					if ($SuccessURL != '') {							
						try {
							$strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

							$fp = fopen('../Log/HiLife/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
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
							$fp = fopen('../Log/HiLife/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
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

							$fp = fopen('../Log/HiLife/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
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
							$fp = fopen('../Log/HiLife/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
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
					$fp = fopen('../Log/HiLife/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
					fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
					fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
					fwrite($fp, '$strReturn => 回傳網址是空的'.PHP_EOL);
					fclose($fp);
				}
			}else {
				$Response = "";
				$Response .= "SHOP_ID=".$_GET["SHOP_ID"].PHP_EOL;
				$Response .= "TRANS_NO=".$_GET["TRANS_NO"].PHP_EOL;
				$Response .= "MMK_ID=".$_GET["MMK_ID"].PHP_EOL;
				$Response .= "ORDER_NO=".$_GET["ORDER_NO"].PHP_EOL;
				$Response .= "TITLE=".Simplify_Company.PHP_EOL;
				$Response .= "A_COUNT=0".PHP_EOL;
				$Response .= "B_COUNT=0".PHP_EOL;
				$Response .= "C_COUNT=0".PHP_EOL;
				$Response .= "PRN_COUNT=0".PHP_EOL;                
				$Response .= "AMOUNT=".$_GET["AMOUNT"].PHP_EOL;
				$Response .= "PAY_AMOUNT=".$_GET["AMOUNT"].PHP_EOL;
				$Response .= "PAY_KIND=2".PHP_EOL;
				$Response .= "CARD_NO=".PHP_EOL;
				$Response .= "CON_F=0".PHP_EOL;
				$Response .= "REASON=核銷失敗".PHP_EOL;
				echo $Response;
				exit;
			}
		}
		/*}else {
			$Rxml = new SimpleXMLElement('<?xml version="1.0" encoding="Big5"?><PAYMONEY_R/>');
			$Rxml->addChild("SHOPID",$xml->SHOPID);
			$Rxml->addChild("DETAIL_NUM",$xml->DETAIL_NUM);
			$Rxml->addChild("STATUS_CODE","1111");
			$Rxml->addChild("STATUS_DESC","核銷失敗");
			$Rxml->addChild("CONFIRM","FAIL");
			$_ReturnXML = $Rxml->asXML();
			echo $_ReturnXML;
			exit;
		}*/
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