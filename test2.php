<?php
    //$WaterAccount = date("md") . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

    $InitialAccount = "1082011298314";

    $chars          = str_split($InitialAccount);

    $passchars      = str_split("371371371371371");

    $x         = 0;
    $CheckCode = 0;
    
    foreach ($chars as $char) {
        //echo $char."|".$passchars[$x] . "|". (($char * $passchars[$x]) % 10)."<br />";
        $CheckCode += (($char * $passchars[$x]) % 10);
        
        $x++;
    }
    //帳號權數
    $AcWeights = ($CheckCode % 10);

    //echo $CheckCode ." | 權數 X1 = ". $AcWeights . "<br />";

    $chars          = str_split(str_pad("21700", 8, '0', STR_PAD_LEFT));
    $passchars      = str_split("12345678");

    $x         = 0;
    $CheckCode = 0;

    //echo "<hr>";
    
    foreach ($chars as $char) {
        echo $char."|".$passchars[$x] . "|". (($char * $passchars[$x]) % 10)."<br />";
        $CheckCode += (($char * $passchars[$x]) % 10);
        
        $x++;
    }

    //金額權數
    $AmWeights = ($CheckCode % 10);

    echo $CheckCode . " | 權數 X2 = ". $AmWeights . "<br />";

    $_Weights = (($AcWeights + $AmWeights) % 10);

    echo $AcWeights . " + ". $AmWeights . "=".($AcWeights + $AmWeights)."<br />";

    echo "權數 X3 = ".$_Weights . "<br />";

    if ($_Weights == 0) {
        $_CheckCode = 0;
    }else {
        $_CheckCode = 10 - $_Weights;
    }
    
    $VatmAccount = $InitialAccount.$_CheckCode;
    echo $VatmAccount;
?>