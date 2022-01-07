<?php
	ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: text/html; charset=utf-8');
	include_once("BaseClass/Setting.php");
	include_once("BaseClass/CDbShell.php");
	include_once("BaseClass/CommonElement.php");
	
	preg_match('/(\/)(\w+)$/', $_SERVER["UNENCODED_URL"], $_Searched);

	//echo $_Searched[COUNT($_Searched)-1];
	//exit;

	if (strcasecmp($_Searched[COUNT($_Searched)-1], "OKCheckCode") == 0) {
		CheckCode();

	}else if (strcasecmp($_Searched[COUNT($_Searched)-1], "OKNotify") == 0) {
		Notify();
	}

	function CheckCode() {
	
		$fp = fopen('Log/OKMart/CheckCodeLOG_'.date("YmdHi").'.txt', 'a');
		$XmlFile = file_get_contents('php://input');
		fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
		fwrite($fp, "XmlFile =>".iconv("big5", "UTF-8", $XmlFile).PHP_EOL);	
		fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
		foreach($_POST as $key => $val)
		//while (list ($key, $val) = each ($_POST)) 
		{
			fwrite($fp, "key =>".$key."  val=>".iconv("big5", "UTF-8", $val).PHP_EOL);
		};	
		fwrite($fp, " ---------------- 開始GET ---------------- ".PHP_EOL);
		foreach($_GET as $key => $val)
		//while (list ($key, $val) = each ($_GET)) 
		{
			fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
		};	
		
		fclose($fp);

		/*$XmlFile = "<SENDDATA>
<MMK_ID>B21</MMK_ID>
<TEN_CODE>1234</TEN_CODE>
<TRAN_NO>10110300001</TRAN_NO>
<STATUS_CODE>0000</STATUS_CODE> 
<STATUS_DESC>成功</STATUS_DESC>  
<LISTDATA>
<DATA_1>OK123456789014</DATA_1> 
<DATA_2></DATA_2>
<DATA_3></DATA_3>
<DATA_4></DATA_4>
<DATA_5></DATA_5>
</LISTDATA>
</SENDDATA>";*/
	

		$xml = simplexml_load_string(iconv("big5", "UTF-8", $_POST['XMLData']));
		//var_dump($xml);

		$_Code = $xml->LISTDATA->DATA_1;

		
		//var_dump($_ReturnXML);
		@CDbShell::connect();
		CDbShell::query("SELECT Sno, Total FROM Ledger WHERE VatmAccount = '".$_Code."' AND (State = '-1' OR State = '2')"); 
		if (CDbShell::num_rows() == 1) {
			$LRow = CDbShell::fetch_array();

			$field = array("State");
			$value = array("2");
			CDbShell::update("ledger", $field, $value, "Sno = '".$LRow['Sno']."'" );
			

			$Rxml = new SimpleXMLElement('<SHOWDATA/>');
			$Rxml->addChild("MMK_ID",$xml->MMK_ID);
			$Rxml->addChild("TEN_CODE",$xml->TEN_CODE);
			$Rxml->addChild("TRAN_NO",$xml->TRAN_NO);
			$Rxml->addChild("STATUS_CODE","0000");
			$Rxml->addChild("STATUS_DESC","成功");
			$Rxml->addChild("LISTDATA");
			$Rxml->LISTDATA->addChild("DATA_1",$_Code);
			$Rxml->LISTDATA->addChild("DATA_2",intval($LRow['Total']));
			$Rxml->LISTDATA->addChild("DATA_3",Simplify_Company);
			$Rxml->LISTDATA->addChild("DATA_4","");
			$Rxml->LISTDATA->addChild("DATA_5","");
			$Rxml->LISTDATA->addChild("DATA_6","");
			$Rxml->LISTDATA->addChild("DATA_7","");
			$Rxml->LISTDATA->addChild("DATA_8","");
			$_ReturnXML = $Rxml->asXML();
			echo $_ReturnXML;
			exit;
		}else {
			$Rxml = new SimpleXMLElement('<SHOWDATA/>');
			$Rxml->addChild("MMK_ID",$xml->MMK_ID);
			$Rxml->addChild("TEN_CODE",$xml->TEN_CODE);
			$Rxml->addChild("TRAN_NO",$xml->TRAN_NO);
			$Rxml->addChild("STATUS_CODE","1111");
			$Rxml->addChild("STATUS_DESC","代碼己過期或不存在");
			$Rxml->addChild("LISTDATA");
			$Rxml->LISTDATA->addChild("DATA_1",$_Code);
			$Rxml->LISTDATA->addChild("DATA_2",0);
			$Rxml->LISTDATA->addChild("DATA_3",Simplify_Company);
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

		$fp = fopen('Log/OKMart/Notify_LOG_'.date("YmdHi").'.txt', 'a');
		$XmlFile = file_get_contents('php://input');
		fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
		fwrite($fp, "XmlFile =>".iconv("big5", "UTF-8", $XmlFile).PHP_EOL);	
		fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
		foreach($_POST as $key => $val)
		//while (list ($key, $val) = each ($_POST)) 
		{
			fwrite($fp, "key =>".$key."  val=>".iconv("big5", "UTF-8", $val).PHP_EOL);
		};	
		fwrite($fp, " ---------------- 開始GET ---------------- ".PHP_EOL);
		foreach($_GET as $key => $val)
		//while (list ($key, $val) = each ($_GET)) 
		{
			fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
		};
		fclose($fp);

		/*$XmlFile = "<SENDDATA>
		<MMK_ID>B21</MMK_ID>
		<TEN_CODE>1234</TEN_CODE>
		<TRAN_NO>10110300001</TRAN_NO>
		<STATUS_CODE>0000</STATUS_CODE> 
		<STATUS_DESC>成功</STATUS_DESC>  
		<LISTDATA>
		<DATA_1>OK123456789014</DATA_1> 
		<DATA_2></DATA_2>
		<DATA_3></DATA_3>
		<DATA_4></DATA_4>
		<DATA_5></DATA_5>
		</LISTDATA>
		</SENDDATA>";*/
	

		$xml = simplexml_load_string(iconv("big5", "UTF-8", $_POST['XMLData']));
		//var_dump($xml);	
		$_Code = $xml->LISTDATA->DATA_1;


		//var_dump($_ReturnXML);
		@CDbShell::connect();
		CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.VatmAccount = '".$_Code."'"); 
		$FirmRow = CDbShell::fetch_array();

		$SuccessURL = $FirmRow["SuccessURL"];
		$DeviantURL = $FirmRow["DeviantURL"];
		
		CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '5' LIMIT 1");  
    	$FCRow = CDbShell::fetch_array();

		
		$fp = fopen('Log/OKMart/Notify_LOG_'.date("YmdHi").'.txt', 'a');
		fwrite($fp, " ---------------- _Code ----------------".PHP_EOL);
		//fwrite($fp, "\SQL => SELECT FC.* L.Sno FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$FirmRow["Sno"]." AND PF.Type = '4' AND FC.Enable = '1' LIMIT 1".PHP_EOL);
		fwrite($fp, "\Closing =>".$FCRow["Closing"].PHP_EOL);
		fclose($fp);
		
		if (strcasecmp($xml->STATUS_CODE, "0000") == 0) {

			$_Amt = $xml->LISTDATA->DATA_2;
			$_PaymentDate = date('Y-m-d H:i:s', strtotime($xml->LISTDATA->DATA_4.$xml->LISTDATA->DATA_5));

			switch ($FCRow["Closing"]) {
				case "Day":
					$ExpectedRecordedDate = date('Y-m-d', strtotime($_PaymentDate ." +".$FCRow["Day"]." day"));
					$Period = date("Y-m-d", strtotime($_PaymentDate));
					$ClosingDate = date("Y-m-d", strtotime($_PaymentDate));
					break;
				case "Week":
					$TodayWeek = Date('w');
					if ($TodayWeek == 0) $TodayWeek = 7;
					$ExpectedRecordedDate = date("Y-m-d", strtotime($_PaymentDate ."+".(7- $TodayWeek + $FCRow["Day"]). " day"));
					$Period = date('Y-m-d', strtotime($_PaymentDate. " -".($TodayWeek - 1)." day")) . " ~ " . date('Y-m-d',strtotime($_PaymentDate. " +".(7 - $TodayWeek)." day"));
					$ClosingDate = date('Y-m-d', strtotime($_PaymentDate. " +".(7- $TodayWeek)." day"));
					break;
				case "Month":
					$ExpectedRecordedDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month +".$FCRow["Day"]." day"));
					$Period = date("Y-m-01", strtotime($_PaymentDate)) . " ~ " . date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
					$ClosingDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
					break;
			}
			$ExpectedRecordedDate = CommonElement::CountHoliday($ClosingDate, $FCRow["Day"], true);


			$field = array("OrderID", "Period", "ClosingDate", "ExpectedRecordedDate", "ClosingTotal", "TransactionDate", "PaymentDate", "ResultCode", "ResultMesg", "State", "CardNumber");
			$value = array($xml->TRAN_NO, $Period, $ClosingDate, $ExpectedRecordedDate, $_Amt, $_PaymentDate, $_PaymentDate, "0", "交易成功", "0", $xml->TEN_CODE);
			CDbShell::update("ledger", $field, $value, "VatmAccount = '".$_Code."'");
			if (CDbShell::affected_rows() == 1) {

				$fp = fopen('Log/OKMart/Notify_LOG_'.date("YmdHi").'.txt', 'a');
				fwrite($fp, " ---------------- 核銷成功 ----------------".PHP_EOL);					
				fclose($fp);

				$Rxml = new SimpleXMLElement('<CONFIRMDATA_R/>');
				$Rxml->addChild("MMK_ID",$xml->MMK_ID);
				$Rxml->addChild("TEN_CODE",$xml->TEN_CODE);
				$Rxml->addChild("TRAN_NO",$xml->TRAN_NO);
				$Rxml->addChild("STATUS_CODE","0000");
				$Rxml->addChild("STATUS_DESC","");
				$Rxml->addChild("RETURNCODE","1");
				$_ReturnXML = $Rxml->asXML();
				echo $_ReturnXML;

				if ($SuccessURL != "") {
				
					$Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=1&MerTradeID=".$FirmRow["MerTradeID"]."&MerUserID=".$FirmRow["MerUserID"]);
					
					$SendPOST["RtnCode"] = "1";
					$SendPOST["RtnMessage"] = "交易成功";
					$SendPOST["MerTradeID"] = $FirmRow["MerTradeID"];
					$SendPOST["MerProductID"] = $FirmRow["MerProductID"];
					$SendPOST["MerUserID"] = $FirmRow["MerUserID"];
					
					$SendPOST["Amount"] = $_Amt;
					$SendPOST["PaymentDate"] = $_PaymentDate;
					$SendPOST["Validate"] = $Validate;
					try {
						$strReturn = SockPost($SuccessURL, $SendPOST, $curlerror);

						$fp = fopen('Log/OKMart/Send_Notify_LOG_'.date('YmdHi').'.txt', 'a');
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
					}
					catch (Exception $e) {
						$fp = fopen('Log/OKMart/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
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
				}else {
					$fp = fopen('Log/OKMart/Send_Notify_ErrLOG_'.date('YmdHi').'.txt', 'a');
					fwrite($fp, ' ---------------- Send_Notify_Err開始 ---------------- '.PHP_EOL);
					fwrite($fp, '$SuccessURL =>'.$SuccessURL.PHP_EOL);
					fwrite($fp, '$strReturn => 回傳網址是空的'.PHP_EOL);
					fclose($fp);
				}
			}else {
				$Rxml = new SimpleXMLElement('<CONFIRMDATA_R/>');
				$Rxml->addChild("MMK_ID",$xml->MMK_ID);
				$Rxml->addChild("TEN_CODE",$xml->TEN_CODE);
				$Rxml->addChild("TRAN_NO",$xml->TRAN_NO);
				$Rxml->addChild("STATUS_CODE","1111");
				$Rxml->addChild("STATUS_DESC","核銷失敗");
				$Rxml->addChild("RETURNCODE","0");
				$_ReturnXML = $Rxml->asXML();
				echo $_ReturnXML;
			}

			exit;
		}else {
			$Rxml = new SimpleXMLElement('<CONFIRMDATA_R/>');
			$Rxml->addChild("MMK_ID",$xml->MMK_ID);
			$Rxml->addChild("TEN_CODE",$xml->TEN_CODE);
			$Rxml->addChild("TRAN_NO",$xml->TRAN_NO);
			$Rxml->addChild("STATUS_CODE","1111");
			$Rxml->addChild("STATUS_DESC","核銷失敗");
			$Rxml->addChild("RETURNCODE","0");
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
	function _replaceChar($value)
	{
		$search_list = array('%2d', '%5f', '%2e', '%21', '%2a', '%28', '%29');
		$replace_list = array('-', '_', '.', '!', '*', '(', ')');
		$value = str_replace($search_list, $replace_list ,$value);
		
		return $value;
	}
	//產生檢查碼
	function _getMacValue($hash_key, $hash_iv, $form_array)
	{
		$encode_str = "HashKey=" . $hash_key;
		foreach ($form_array as $key => $value)
		{
			$encode_str .= "&" . $key . "=" . $value;
		}
		$encode_str .= "&HashIV=" . $hash_iv;
		echo $encode_str;
		$encode_str = strtolower(urlencode($encode_str));
		$encode_str = _replaceChar($encode_str);
		return md5($encode_str);
	}
	
	function create_aes_decrypt($parameter = "", $key = "", $iv = "")
	{
	    return strippadding(openssl_decrypt(hex2bin($parameter), 'AES-256-CBC', 'zn2E7vzUy31MRVNzIOWOPXJWiDIDltvU', OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, 'QxzY29TBdUo4QbEz'));
	}
	function strippadding($string)
	{
	    $slast  = ord(substr($string, -1));
	    $slastc = chr($slast);
	    $pcheck = substr($string, -$slast);
	    if (preg_match("/$slastc{" . $slast . "}/", $string)) {
	        $string = substr($string, 0, strlen($string) - $slast);
	        return $string;
	    } else {
	        return false;
	    }
	}
//------------------------------------------交易輸入參數------------

	function build_mysign($sort_array, $HashKey, $HashIV, $sign_type = "MD5") 
	{
	    $prestr = create_linkstring($sort_array);     	
	    $prestr = "HashKey=".$HashKey."&".$prestr."&HashIV=".$HashIV;
	    echo $prestr;
	    //exit;
	    $prestr = strtolower(urlencode($prestr));    
	    //echo $prestr;
	    //exit;
	    $mysgin = sign($prestr,$sign_type);			    
	    return $mysgin;
	}	

	function create_linkstring($array) 
	{
	    $arg  = "";
	    foreach($array as $key => $val)
	    //while (list ($key, $val) = each ($array)) 
		{
	        $arg.=$key."=".$val."&";
	    }
	    $arg = substr($arg,0,count($arg)-2);		     //去掉最后一个&字符
	    return $arg;
	}

	function sign($prestr,$sign_type) 
	{
	    $sign='';
	    if($sign_type == 'MD5') 
		{
	        $sign = md5($prestr);
	    }
		else 
		{
	        die("暂不支持".$sign_type."类型的签名方式");
	    }
	    return $sign;
	}

	function arg_sort($array) 
	{
	    ksort($array, SORT_NATURAL | SORT_FLAG_CASE);
	    reset($array);
	    return $array;
	}
?>