<?php

    //$_Data = "715108202019100408075874N0000000000500000010010820092495662";
    //$_Data = "715108202019100408185845T0000000000300000010010820092473657";
    /*$_Data = "715108202019101414906126R0000000000050000010010820101462301";
    $_VatmAccount = substr($_Data, -14);
    echo $_VatmAccount."<br />";

    $_Amt = substr($_Data, 25, 13);
    echo $_Amt."<br />";

    $_TX = substr($_Data, 16, 8);
    echo $_TX."<br />";

    $_DATE = substr($_Data, 8, 8);
    echo Date('Y-m-d', strtotime($_DATE))."<br />";*/
    //echo date('Y-m-d H:i:s', strtotime("20191002173459"));
    /*$XMLData = "<CONFIRMDATA><MMK_ID>GFN</MMK_ID><TEN_CODE>0821</TEN_CODE><TRAN_NO>19120106006</TRAN_NO><STATUS_CODE>0000</STATUS_CODE><STATUS_DESC></STATUS_DESC><LISTDATA><DATA_1>WP912057219781</DATA_1><DATA_2>50</DATA_2><DATA_3>智慧付</DATA_3><DATA_4>20191205</DATA_4><DATA_5>175700</DATA_5><DATA_6></DATA_6><DATA_7></DATA_7><DATA_8></DATA_8></LISTDATA></CONFIRMDATA>";
    $parameter = array(	
        "XMLData"		=> iconv("UTF-8","big5", $XMLData)
    );*/
    $XMLData = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Header><ns:PartyInfo xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns="http://www.tibco.com/namespaces/bc/2002/04/partyinfo.xsd" xmlns:ns0="http://schemas.xmlsoap.org/soap/envelope/"><from xmlns=""><name xmlns="">BANK006    </name></from><to xmlns=""><name xmlns="">TCB</name></to><operationID xmlns="">BankColl/1.0/BankCollStatusAdvise</operationID><operationType xmlns="">syncRequestResponse</operationType><transactionID xmlns="">20210505013650725302</transactionID></ns:PartyInfo></SOAP-ENV:Header><SOAP-ENV:Body><ns0:BankCollStatusAdviseRq xmlns:ns0="http://ns.tcb.com.tw/XSD/TCB/BC/Message/BankCollStatusAdviseRq/01"><ns0:SeqNo>00000000</ns0:SeqNo><ns0:TxnCode>YANGSHEN 088909 </ns0:TxnCode><ns0:RqUID>20210505013650725301</ns0:RqUID><ns0:BillId>0889090505911203</ns0:BillId><ns0:BillNum>   </ns0:BillNum><ns0:TrnSign>+</ns0:TrnSign><ns0:CollInfo><ns0:CollId>006    </ns0:CollId><ns0:CollSubId>0000888717133685</ns0:CollSubId><ns0:PostedDt>2021-05-05</ns0:PostedDt><ns0:CurAmt><ns0:Amt>50.0</ns0:Amt><ns0:CurCode>TWD</ns0:CurCode></ns0:CurAmt><ns0:OrigDt>2021-05-05</ns0:OrigDt><ns0:OrigTm>17:21:14</ns0:OrigTm><ns0:CSPRefId>0136507253000001</ns0:CSPRefId><ns0:TrnType>2</ns0:TrnType><ns0:TrnSrc>1</ns0:TrnSrc><ns0:TrnDesc>           </ns0:TrnDesc></ns0:CollInfo><ns0:SettlementInfo><ns0:SettlementId>0136507253          </ns0:SettlementId><ns0:CustAcctId>013000069950**4951*</ns0:CustAcctId><ns0:CustName>                                                                                </ns0:CustName><ns0:Memo>                                                                                </ns0:Memo></ns0:SettlementInfo></ns0:BankCollStatusAdviseRq></SOAP-ENV:Body></SOAP-ENV:Envelope>';
    
    //$strReturn = SockPost('http://wisdompay.com.tw/OKCheckCode', $parameter);
    $strReturn = SockPost('https://fuze-pay.com/TCBNotify', $XMLData);
    //$strReturn = SockPost('http://127.0.0.1/fuzePay/TCBNotify', $XMLData);
    //$strReturn = SockPost('https://cn.investing.com/equities/asia-pacific', $parameter);
    echo $strReturn;
    exit;
    /*$ch = curl_init ("https://cn.investing.com/equities/asia-pacific");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $rawdata=curl_exec ($ch);
    curl_close ($ch);*/

    /*$parameter = array(	
        "d"		=> "715108202019101504414357R0000000000045000010010820101515974  "
    );*/
    //$strReturn = SockPost('http://wisdompay.com.tw/FamilyNotify ', $parameter);
    echo $rawdata;
    /*if (!class_exists('CDbShell'))  		include_once("./BaseClass/CDbShell.php");
    if (!class_exists('CommonElement'))		include_once("./BaseClass/CommonElement.php");
    CDbShell::Connect();
    $ExpectedRecordedDate = CommonElement::CountHoliday("2019-10-10", "2", true);
    echo $ExpectedRecordedDate;
    exit;*/

    /*$xmldata = "<?xml version=\"1.0\" encoding=\"UTF-8\"?> <OLTP> <HEADER> <VER>05.05</VER> <FROM>99027</FROM> <TERMINO>123456011234567890</TERMINO> <TO>廠商統編</TO> <BUSINESS>B000001</BUSINESS> <DATE>20190331</DATE> <TIME>080858</TIME> <STATCODE>0000</STATCODE> <STATDESC /> </HEADER> <AP> <OL_OI_NO>KK1</OL_OI_NO> <PIN_CODE>12345678901234</PIN_CODE> <ORDER_NO>12345678</ORDER_NO> <ACCOUNT>02000</ACCOUNT> <OL_Code_1>123456789</OL_Code_1> <OL_Code_2>1234567890123456</OL_Code_2> <OL_Code_3>123456789012345</OL_Code_3> <STORE_DESC>中山店</STORE_DESC> <STATUS>S</STATUS> <DESC /> </AP> </OLTP>";
    $xml = simplexml_load_string($xmldata);

    echo $xml->HEADER->STATCODE. "<br />";
    echo $xml->AP->ORDER_NO;*/

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
?>