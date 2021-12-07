<?php
$_POST["data"] = "<?xml version='1.0' encoding='Big5'?><SENDDATA><BUSINESS>0700QC1</BUSINESS><STOREID>895651</STOREID><SHOPID>KE</SHOPID><DETAILED_NUM>011102KE035787</DETAILED_NUM><PRODUCT_CODE>07700000000346</PRODUCT_CODE><STATUS_CODE>0000</STATUS_CODE><STATUS_DESC>¦¨¥\</STATUS_DESC><SUB1>1007</SUB1><SUB2>1002</SUB2><SUB3>994</SUB3><KEY1>YAN1102247402701</KEY1><KEY2></KEY2><KEY3></KEY3><KEY4></KEY4><KEY5></KEY5></SENDDATA>";
$_data = "<?xml version='1.0' encoding='Big5'?><SENDDATA><BUSINESS>0700QC1</BUSINESS><STOREID>895651</STOREID><SHOPID>KE</SHOPID><DETAILED_NUM>011102KE035787</DETAILED_NUM><PRODUCT_CODE>07700000000346</PRODUCT_CODE><STATUS_CODE>0000</STATUS_CODE><STATUS_DESC>¦¨¥\</STATUS_DESC><SUB1>1007</SUB1><SUB2>1002</SUB2><SUB3>994</SUB3><KEY1>YAN1102247402701</KEY1><KEY2></KEY2><KEY3></KEY3><KEY4></KEY4><KEY5></KEY5></SENDDATA>";

$strReturn = SockPost('https://fuze-pay.com/SevenCheckCode', $_data);
echo $strReturn;
exit;
function SockPost($URL, $Query){
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
    
    curl_close ($ch);
    
    return $strReturn;
    
}