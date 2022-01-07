<?php

$fp = fopen('../Log/GASH/PaySuccess_LOG_'.date("YmdHis").'.txt', 'a');
fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
//while (list ($key, $val) = each ($_POST)) 
foreach($_POST as $key => $val)
{
    fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
};	
/*$XmlFile = file_get_contents('php://input');
fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
fwrite($fp, "XmlFile =>".$XmlFile.PHP_EOL);	*/
fclose($fp);

$data = base64_decode($_POST["data"]);

$xml = simplexml_load_string($data);

if ($xml->RCODE == "0000") {
    $_PaymentResult = "付款成功";
}else {
    $_PaymentResult = "付款失敗";
}
include("../PaySuccess.html");