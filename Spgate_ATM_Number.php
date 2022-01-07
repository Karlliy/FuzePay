<?php
	ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);

	include_once("BaseClass/Setting.php");
	include_once("BaseClass/CDbShell.php");
	include_once("BaseClass/CommonElement.php");
	
	//print_r($_POST);
	$fp = fopen('Log/Spgate_ATM/Spgate_ATMNumber_LOG_'.date("YmdHis").'.txt', 'a');
	fwrite($fp, " ---------------- 開始POST ---------------- \n\r");
	foreach($_POST as $key => $val)
	//while (list ($key, $val) = each ($_POST)) 
	{
		fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
	};
	
	//if ($_POST['MerchantID'] == "MS3276119674") {
		$Data = create_aes_decrypt($_POST['TradeInfo'], Spgate_Key, Spgate_IV);
	/*}else if ($_POST['MerchantID'] == "MS3287154932") {
		$Data = create_aes_decrypt($_POST['TradeInfo'], Spgate_Key_2, Spgate_IV_2);
	}*/
	
	fwrite($fp, "\$Data =>".$Data.PHP_EOL);
	fclose($fp);                         
    
	$obj = json_decode($Data);
	
	
if ($obj->Status == 'SUCCESS' ) {
    @CDbShell::connect();
    CDbShell::query("SELECT F.*, L.PaymentName, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.CashFlowID = '".$obj->Result->MerchantOrderNo."'");
	$FirmRow = CDbShell::fetch_array();
	
    $SuccessURL     = $FirmRow["SuccessURL"];
    $FailURL        = $FirmRow["FailURL"];
    $TakeNumberURL  = $FirmRow["TakeNumberURL"];

    $ExpireDatetime = $obj2['ExpireDate'].' '.$obj2['ExpireTime'];
    $field = array('OrderID', 'VatmAccount', 'ExpireDatetime');
    $value = array($obj->Result->TradeNo, $obj->Result->BankCode.'-'.$obj->Result->CodeNo, $obj->Result->ExpireDate . " ". $obj->Result->ExpireTime);
    CDbShell::update('ledger', $field, $value, "CashFlowID = '".$obj->Result->MerchantOrderNo."'");

	$_Amt = $obj->Result->Amt;
	
    include 'ATMPay2.html';
    //echo "1|OK";

	/*$fp = fopen('Log/Spgate_ATM/Send_SuccessURL_LOG_'.date("YmdHi").'.txt', 'a');
	fwrite($fp, " ---------------- Send_SuccessURL開始 ---------------- ".PHP_EOL);                
	fwrite($fp, "\$SuccessURL =>".$SuccessURL.PHP_EOL);
	fclose($fp);*/

    if ($TakeNumberURL != '') {
        $Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&HashKey='.$FirmRow['HashKey'].'&RtnCode=1&TradeID='.$FirmRow['MerTradeID'].'&UserID='.$FirmRow['MerUserID'].'&Money='.$obj->Result->Amt);

        $SendPOST['RtnCode'] = '1';
        $SendPOST['RtnMessage'] = '取號成功';
        $SendPOST['MerTradeID'] = $FirmRow['MerTradeID'];
        $SendPOST['MerProductID'] = $FirmRow['MerProductID'];
        $SendPOST['MerUserID'] = $FirmRow['MerUserID'];

        $SendPOST['Amount'] = $obj->Result->Amt;
        $SendPOST['ExpireDatetime'] = $obj->Result->ExpireDate;
        $SendPOST['VatmBankCode'] = $obj->Result->BankCode;
        $SendPOST['VatmAccount'] = $obj->Result->CodeNo;
        $SendPOST['Validate'] = $Validate;
        try {
			$fp = fopen('Log/Spgate_ATM/Send_TakeNumber_LOG_'.date("YmdHi").'.txt', 'a');
			fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
			fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
			foreach($SendPOST as $key => $val)
			//while (list ($key, $val) = each ($SendPOST)) 
			{
				fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
			};

			$strReturn = SockPost($TakeNumberURL, $SendPOST, $curlerror);
			
			
			fwrite($fp, "\$strReturn =>".$strReturn.PHP_EOL);
			fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
			fclose($fp);
        } catch (Exception $e) {

			$fp = fopen('Log/Spgate_ATM/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
			fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
			fwrite($fp, "\$SuccessURL =>".$SuccessURL.PHP_EOL);
			foreach($SendPOST as $key => $val)
			//while (list ($key, $val) = each ($SendPOST)) 
			{
				fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
			};
			fwrite($fp, "\$strReturn =>".$e->getMessage().PHP_EOL);
			fwrite($fp, "\$curlerror =>".$curlerror.PHP_EOL);
			fclose($fp);
        }
    }else {
		$fp = fopen('Log/Spgate_ATM/Send_TakeNumber_ErrLOG_'.date("YmdHi").'.txt', 'a');
		fwrite($fp, " ---------------- Send_TakeNumber開始 ---------------- ".PHP_EOL);                
		fwrite($fp, "\$TakeNumberURL =>".$TakeNumberURL.PHP_EOL);
		fwrite($fp, "\$strReturn => 回傳網址是空的".PHP_EOL);
		fclose($fp);
	}
} else {
      echo '0|Error'.$obj['Message'];

      if ('' != $DeviantURL) {
          $Validate = md5('ValidateKey='.$FirmRow['ValidateKey'].'&HashKey='.$FirmRow['HashKey'].'&RtnCode=0&TradeID='.$FirmRow['MerTradeID'].'&UserID='.$FirmRow['MerUserID'].'&Money='.$FirmRow['Total']);

          $SendPOST['RtnCode'] = 0;
          $SendPOST['RtnMessage'] = $obj['Message'];
          $SendPOST['MerTradeID'] = $FirmRow['MerTradeID'];
          $SendPOST['MerProductID'] = $FirmRow['MerProductID'];
          $SendPOST['MerUserID'] = $FirmRow['MerUserID'];

          $SendPOST['Amount'] = $FirmRow['Total'];
          $SendPOST['Validate'] = $Validate;
          try {
              SockPost($DeviantURL, $SendPOST);
          } catch (Exception $e) {
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
	
	function create_aes_decrypt($parameter, $key = "", $iv = "")
	{
		return strippadding(openssl_decrypt(hex2bin($parameter),'aes-256-cbc', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv));
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