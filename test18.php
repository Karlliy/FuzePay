<?php
$XMLData = '<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
   <soap:Body>
      <MobiPrint xmlns="http://www.ibon.com.tw/">
         <HEADER>
            <MOBINO>20211129MB0001000000011701</MOBINO>
            <BUSINESS>CP0700002</BUSINESS>
            <DATE>20211129</DATE>
            <TIME>170127</TIME>
            <STATCODE>0000</STATCODE>
            <STATDESC>成功</STATDESC>
         </HEADER>
         <AP>
            <PCODE_IN>S0020</PCODE_IN>
            <KEY_1>YAN1118583245358</KEY_1>
            <KEY_2 />
            <KEY_3 />
            <KEY_4 />
            <KEY_5 />
            <KEY_6 />
            <STATUS>S</STATUS>
            <DESCRIPTION>成功</DESCRIPTION>
            <TOTAL_COUNT>1</TOTAL_COUNT>
            <TOTAL_AMOUNT>100</TOTAL_AMOUNT>
            <DETAILED_NUM>01112952789142</DETAILED_NUM>
            <Detail>
               <SERIALNO>01</SERIALNO>
               <PAY_MONEY>100</PAY_MONEY>
               <EXTRA_FEE>0</EXTRA_FEE>
               <PAY_AMOUNT>100</PAY_AMOUNT>
               <PAY_ENDDATE>2021-11-29 20:01</PAY_ENDDATE>
               <OL_CODE_1>101129555</OL_CODE_1>
               <OL_CODE_2>0111295278914201</OL_CODE_2>
               <OL_CODE_3>822001750000100</OL_CODE_3>
            </Detail>
         </AP>
      </MobiPrint>
   </soap:Body>
</soap:Envelope>';

$xml = simplexml_load_string($XMLData, NULL, NULL, "http://www.w3.org/2003/05/soap-envelope");
$xml->registerXPathNamespace('MobiPrint', 'http://www.ibon.com.tw/');

$nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:HEADER/MobiPrint:STATCODE');
$STATCODE = (string) $nodes[0];
var_dump($STATCODE);

$nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:AP/MobiPrint:TOTAL_AMOUNT');
$AMOUNT = (string) $nodes[0];
var_dump($AMOUNT);

$nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:AP/MobiPrint:Detail/MobiPrint:OL_CODE_1');
$OL_CODE_1 = (string) $nodes[0];
var_dump($OL_CODE_1);

echo "<img src=Barcode.php?".$OL_CODE_1." />";

$nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:AP/MobiPrint:Detail/MobiPrint:OL_CODE_2');
$OL_CODE_2 = (string) $nodes[0];
var_dump($OL_CODE_2);
echo "<img src=Barcode.php?".$OL_CODE_2." />";
$nodes = $xml->xpath('/soap:Envelope/soap:Body/MobiPrint:MobiPrint/MobiPrint:AP/MobiPrint:Detail/MobiPrint:OL_CODE_3');
$OL_CODE_3 = (string) $nodes[0];
var_dump($OL_CODE_3);
echo "<img src=Barcode.php?".$OL_CODE_3." />";
// then use xpath to adress the item you want (using this NS)
/*$nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:BillId');
$BillNum = (string) $nodes[0];
var_dump($BillNum);

$nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:CollInfo/services:CurAmt/services:Amt');
$Amt = (string) $nodes[0];
var_dump($Amt);

$nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:CollInfo/services:OrigDt');
$OrigDt = (string) $nodes[0];
var_dump($OrigDt);

$nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:CollInfo/services:OrigTm');
$OrigTm = (string) $nodes[0];
var_dump($OrigTm);

$nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:SettlementInfo/services:CustAcctId');
$CustAcctId = (string) $nodes[0];
var_dump($CustAcctId);*/

/*$xml = '
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
<soapenv:Header/>
<soapenv:Body>
<addItemToShoppingCartResponse xmlns="http://www.sample.com/services">
    <ack>Warning</ack>
</addItemToShoppingCartResponse>
</soapenv:Body>
</soapenv:Envelope>
';

$xml = simplexml_load_string($xml, NULL, NULL, "http://schemas.xmlsoap.org/soap/envelope/");

// register your used namespace prefixes
$xml->registerXPathNamespace('soap-env', 'http://schemas.xmlsoap.org/soap/envelope/');
$xml->registerXPathNamespace('services', 'http://www.sample.com/services'); // ? ns not in use

// then use xpath to adress the item you want (using this NS)
$nodes = $xml->xpath('/soapenv:Envelope/soapenv:Body/services:addItemToShoppingCartResponse/services:ack');
$ack = (string) $nodes[0];
var_dump($ack);*/