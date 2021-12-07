<?php
$XMLData = '<?xml version = "1.0" encoding = "UTF-8"?>
<outputMessage>
    <ns4:BankCollStatusAdviseRs xmlns:S = "http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENV = "http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns2 = "http://ns.tcb.com.tw/XSD/TCB/BC/Message/BankCollStatusAdviseRq/01" xmlns:ns3 = "http://www.tibco.com/namespaces/bc/2002/04/partyinfo.xsd" xmlns:ns4 = "http://ns.tcb.com.tw/XSD/TCB/BC/Message/BankCollStatusAdviseRs/01">
        <ns4:Status_Res>
            <ns4:StatusCode>0000</ns4:StatusCode>
            <ns4:StatusDesc>success</ns4:StatusDesc>
        </ns4:Status_Res>
        <ns4:RqUID_Res>16202110551467</ns4:RqUID_Res>
    </ns4:BankCollStatusAdviseRs>
</outputMessage>';

$xml = simplexml_load_string($XMLData, NULL, NULL, "http://schemas.xmlsoap.org/soap/envelope/");

// register your used namespace prefixes
$xml->registerXPathNamespace('header', 'http://www.tibco.com/namespaces/bc/2002/04/partyinfo.xsd');
$xml->registerXPathNamespace('services', 'http://ns.tcb.com.tw/XSD/TCB/BC/Message/BankCollStatusAdviseRq/01'); // ? ns not in use

$nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Header/header:PartyInfo/transactionID');
$transactionID = (string) $nodes[0];
var_dump($transactionID);

// then use xpath to adress the item you want (using this NS)
$nodes = $xml->xpath('/SOAP-ENV:Envelope/SOAP-ENV:Body/services:BankCollStatusAdviseRq/services:BillId');
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
var_dump($CustAcctId);

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