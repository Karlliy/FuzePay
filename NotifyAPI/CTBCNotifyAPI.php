<?php
ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting',E_ALL & ~E_NOTICE);
header('Access-Control-Allow-Origin: *');
include_once("../BaseClass/Setting.php");
include_once("../BaseClass/CDbShell.php");
include_once("../BaseClass/CommonElement.php");

$fp = fopen('../Log/CTBC/CheckCode_LOG_'.date("YmdHis").'.txt', 'a');
fwrite($fp, " ---------------- 開始POST ---------------- ".PHP_EOL);
foreach($_POST as $key => $val)
{
    fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
};	
fwrite($fp, " ---------------- 開始GET ---------------- ".PHP_EOL);
foreach($_GET as $key => $val)
{
    fwrite($fp, "key =>".$key."  val=>".$val.PHP_EOL);
};	
$XmlFile = file_get_contents('php://input');
fwrite($fp, " ---------------- 開始php://input ----------------".PHP_EOL);
fwrite($fp, "XmlFile =>".$XmlFile.PHP_EOL);	
fclose($fp);