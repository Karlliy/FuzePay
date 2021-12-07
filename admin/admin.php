<?php 
ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting',E_ALL & ~E_NOTICE);

header('Content-Type: text/html; charset=utf-8');
//==============================================

ob_start(); //output buffer control 這樣才不會有cookie問題
if (!class_exists('CDbShell'))			include_once("../BaseClass/CDbShell.php");
if (!class_exists('CToday'))			include_once("../BaseClass/CToday.php"); 
if (!class_exists('CSession'))			include_once("../BaseClass/CSession.php");  
if (!class_exists('CUrlQuery'))			include_once("../BaseClass/CUrlQuery.php");
if (!class_exists('CommonElement'))		include_once("../BaseClass/CommonElement.php");
if (!class_exists('JSModule'))			include_once("../BaseClass/JSModule.php");
if (!class_exists('Setting'))			include_once("../BaseClass/Setting.php");
if (!class_exists('CLedger'))			include_once("../ledger/CLedger.php");

include_once("../BaseClass/header.php");
include("./Cadmin.php");


$admin = new Cadmin();
$admin->CaseFunctions_Admin();

$RealOutput = ob_get_contents(); 
ob_end_clean();
echo $RealOutput; 
	
?>