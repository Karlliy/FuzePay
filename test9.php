<?php
$chars          = str_split("088909051901180");

$passchars      = str_split("654321987654321");

$x         = 0;
$CheckCode = 0;

foreach ($chars as $char) {
    echo $char."|".$passchars[$x] . "|". ($char * $passchars[$x])."<br />";
    $CheckCode += ($char * $passchars[$x]);
    
    $x++;
}
echo "<pre/>";
echo $CheckCode;
$_CheckCode = ($CheckCode % 11);
echo "<pre/>";
echo $_CheckCode;
if ($_CheckCode == 10) $_CheckCode = 0;

$VatmAccount = $InitialAccount.$_CheckCode;
echo "<pre/>";
echo $VatmAccount;