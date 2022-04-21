<?php
ini_set('SHORT_OPEN_TAG', "On"); // 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors', "On"); // 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
include_once("BaseClass/Setting.php");
include_once("BaseClass/CDbShell.php");

var_dump($_POST);