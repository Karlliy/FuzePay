<?php
ini_set('SHORT_OPEN_TAG', "On"); // 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors', "On"); // 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
include_once("BaseClass/Setting.php");
include_once("BaseClass/CDbShell.php");
@CDbShell::connect();

for ($i = 0; $i < $_GET['q']; $i++) {
    Again3:
    $WaterAccount = date('md', strtotime($ExpireDatetime)) . str_pad(rand(0, 100000), 5, '0', STR_PAD_LEFT);

    $InitialAccount = TCBBank_Code . $WaterAccount;

    $chars          = str_split($InitialAccount);

    $passchars      = str_split("654321987654321");

    $x         = 0;
    $CheckCode = 0;

    foreach ($chars as $char) {
        //echo $char."|".$passchars[$x] . "|". (($char * $passchars[$x]) % 10)."<br />";
        $CheckCode += ($char * $passchars[$x]);
        
        $x++;
    }

    $_CheckCode = ($CheckCode % 11);
    if ($_CheckCode == 10) $_CheckCode = 0;

    $VatmAccount = $InitialAccount.$_CheckCode;

    $sql = "SELECT Sno FROM ledger WHERE VatmAccount = '" . $VatmAccount . "' AND CreationDate BETWEEN '" . date('Y-m-d') . " 00:00:00' AND '" . date('Y-m-d') . " 23:59:59'";
    CDbShell::query($sql);
    if (CDbShell::num_rows() > 0) {
        goto Again3;
    }

    $sql = "SELECT * FROM fixedvirt WHERE FixedVirtAccount = '" . trim($_VirAccount) . "'";
    CDbShell::query($sql);
    if (CDbShell::num_rows() != 0) {
        goto Again3;
    }

    echo "<pre/>";
    echo $VatmAccount;
}