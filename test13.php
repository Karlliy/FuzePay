<?php
    ini_set('SHORT_OPEN_TAG', "On"); // 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
    ini_set('display_errors', "On"); // 是否將錯誤信息作為輸出的一部分顯示。
    ini_set('error_reporting', E_ALL & ~E_NOTICE);
    header('Content-Type: text/html; charset=utf-8');
    //include_once("BaseClass/Setting.php");
    include_once("BaseClass/CDbShell.php");
    echo Date('Y-m-d H:i:s', ceil(1633341436891 / 1000));
    //echo md5(base64_encode("amount35.00merchantNoATA0000000001outTradeNo21082617104610060857tradeNoAH16299690466031tradeStatus2").Lihuo_Key);
