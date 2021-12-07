<?php
        //$client = new SoapClient("http://61.57.234.95/CPMobiService/CPMOBI_PayService.asmx?wsdl");
        $client = new SoapClient("http://61.57.234.95/CPMobiService/CPMOBI_PayService.asmx?wsdl", array(
            'encoding' => 'UTF-8',
            'trace'=>true,
            "soap_version" => SOAP_1_1
        ));
        // $client->soap_defencoding = 'utf-8';  
        // $client->decode_utf8 = false;   
        // $client->xml_encoding = 'utf-8'; 
        $headerbody = array(
            'UID' => "MB0001",
            'PWD' => "zecPUXG9QtSUGgzB"
        );
        $header = new SOAPHeader("http://www.ibon.com.tw/", 'AuthHeader', $headerbody);    
        // $header = new SoapHeader('NAMESPACE','AuthHeader', $headerbody, false);
        //set the Headers of Soap Client.
        $client->__setSoapHeaders($header);
        $_MOBINO = DATE("Ymd")."MB0001"."00000001".DATE("Hi");
        $parameter = array(
            'HEADER' => array(
                "MOBINO"    => $_MOBINO,
                "BUSINESS"  => "CP0700001",
                "DATE"      => DATE("Ymd"),
                "TIME"      => DATE("His"),
                "STATCODE"  => "0000",
                "STATDESC"  => ""
            ),
            'AP' => array(
                "PCODE_IN"      => "S0020",
                "KEY_1"         => "YAN1118583245358",
                "KEY_2"         => "",
                "KEY_3"         => "",
                "KEY_4"         => "",
                "KEY_5"         => "",
                "KEY_6"         => "",
                "TOTAL_COUNT"   => 1,
                'Detail' => array(
                    "SERIALNO"  => "01"
                )
            )
        );
        //var_dump($parameter);
        /*$obj = array();
        $obj["PCODE_IN"] = 'S0020';
        $obj["KEY_1"]       =    'YAN21321523914';

        $param = new SoapParam(new SoapVar($obj, SOAP_ENC_OBJECT), 'AP');*/

        //$result = $client->CPMobiPrint($parameter);
        $_DATE = DATE("Ymd");
        $_TIME = DATE("His");
        $_TIME2 = DATE("Hi");
        $_MOBINO = $_DATE."MB0001"."00000001".$_TIME2;

        $SendPOST = 
        <<<EOF
        <soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" >
            <soap:Header>
                <AuthHeader xmlns="http://www.ibon.com.tw/">
                        <UID>MB0001</UID>
                        <PWD>zecPUXG9QtSUGgzB</PWD>
                    </AuthHeader>
            </soap:Header>
            <soap:Body>
                <MobiPrint xmlns="http://www.ibon.com.tw/">
                        <HEADER>
                            <MOBINO>{$_MOBINO}</MOBINO>
                            <BUSINESS>CP0700001</BUSINESS>
                            <DATE>{$_DATE}</DATE>
                            <TIME>{$_TIME}</TIME>
                            <STATCODE>0000</STATCODE>
                            <STATDESC/>
                        </HEADER>
                        <AP>
                            <PCODE_IN>S0020</PCODE_IN>
                            <KEY_1>YAN1118583245358</KEY_1>
                            <KEY_2/>
                            <KEY_3/>
                            <KEY_4/>
                            <KEY_5/>
                            <KEY_6/>
                            <TOTAL_COUNT>1</TOTAL_COUNT>
                            <Detail>
                                <SERIALNO>01</SERIALNO>
                            </Detail>
                        </AP>
                    </MobiPrint>
            </soap:Body>

        </soap:Envelope>
EOF;
        $strReturn = SockPost("http://61.57.234.95/CPMobiService/CPMOBI_PayService.asmx", $SendPOST, $curlerror);

        //echo $strReturn;
        $xml = simplexml_load_string($strReturn, NULL, NULL, "http://www.w3.org/2003/05/soap-envelope");
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

        //var_dump($xml);

        function SockPost($URL, $Query, &$curlerror){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            /*curl_setopt($ch, CURLOPT_HTTPHEADER , array(
                "Cache-Control: no-cache",
                "Content-Type: application/xml"
            ));*/
            $headers = array("Content-type:text/xml; charset=utf-8");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $Query);
            $SSL = (substr($URL, 0, 8) == "https://" ? true : false); 
            if ($SSL) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            }
            $strReturn = curl_exec($ch);
            
            if(curl_errno($ch)){
                $curlerror = "Request Error(".curl_errno($ch)."):" . curl_error($ch) ;
            }else {
                $curlerror = "Request OK(".curl_errno($ch)."):" . curl_error($ch);
            }
            
            curl_close ($ch);
            
            return $strReturn;
            
        }