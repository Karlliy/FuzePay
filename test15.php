<?php
$_POST["XMLData"] = mb_convert_encoding("<?xml version='1.0' encoding='big5'?><PAYMONEY><SENDTIME>0</SENDTIME><STOREID>958749</STOREID><SHOPID>52</SHOPID><DETAIL_NUM>01111052787725</DETAIL_NUM><STATUS_CODE>0000</STATUS_CODE><STATUS_DESC>æå</STATUS_DESC><BARCODE1>101110555</BARCODE1><BARCODE2>0111105278772501</BARCODE2><BARCODE3>871716320000050</BARCODE3><AMOUNT>50</AMOUNT><PAYDATE>20211110141942</PAYDATE><USERDATA1>338734</USERDATA1><USERDATA2></USERDATA2><USERDATA3></USERDATA3><USERDATA4></USERDATA4><USERDATA5></USERDATA5></PAYMONEY>", "BIG5", "UTF-8");
/*$_data = "<?xml version='1.0' encoding='Big5'?><SENDDATA><BUSINESS>0700QC1</BUSINESS><STOREID>895651</STOREID><SHOPID>KE</SHOPID><DETAILED_NUM>011102KE035787</DETAILED_NUM><PRODUCT_CODE>07700000000346</PRODUCT_CODE><STATUS_CODE>0000</STATUS_CODE><STATUS_DESC>¦¨¥\</STATUS_DESC><SUB1>1007</SUB1><SUB2>1002</SUB2><SUB3>994</SUB3><KEY1>YAN1102247402701</KEY1><KEY2></KEY2><KEY3></KEY3><KEY4></KEY4><KEY5></KEY5></SENDDATA>";
*/

//$_POST["XMLData"] = mb_convert_encoding($_POST["XMLData"], "BIG5", "UTF-8");
//$strReturn = SockPost('http://fueastpay.com/SevenBarcodeNotify', $_POST);
$strReturn = SockPost('http://fueastpay.com/SevenBarcodeNotify', $_POST);
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