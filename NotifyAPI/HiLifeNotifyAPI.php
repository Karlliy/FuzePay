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

    if (strcasecmp($_Searched[COUNT($_Searched)-1], "HiLifeCheckCode") == 0) {
        CheckCode();

    }else if (strcasecmp($_Searched[COUNT($_Searched)-1], "HiLifeNotify") == 0) {
        Notify();
    }