<?php 
    $soap = new SoapClient(null,array('location'=>'http://61.57.234.95/CPMobiService/CPMOBI_PayService.asmx?wsdl','uri'=>'http://61.57.234.95/CPMobiService/CPMOBI_PayService.asmx?wsdl'));
    //ws
    $ns_wsse = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";//WS-Security namespace
    $ns_wsu = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd";//WS-Security namespace
    
    $userT = new SoapVar('admin', XSD_STRING, NULL, $ns_wsse, NULL, $ns_wsse);
    $passwT = new SoapVar('NnYZe7oD81Kd8QRS4tUMze/2CUs=', XSD_STRING, NULL, $ns_wsse, NULL, $ns_wsse);
    $createdT = new SoapVar(time(), XSD_DATETIME, NULL, $ns_wsu, NULL, $ns_wsu);
    class UsernameT1 {
    private $Username; 
    //Name must be  identical to corresponding XML tag in SOAP header
    private $Password; 
    // Name must be  identical to corresponding XML tag in SOAP header 
    private $Created;
      function __construct($username, $password, $created) {
             $this->Username=$username;
             $this->Password=$password;
             $this->Created=$created;
        }
    }
    $tmp = new UsernameT1($userT, $passwT, $createdT);
    $uuT = new SoapVar($tmp, SOAP_ENC_OBJECT, NULL, 
    $ns_wsse, 'UsernameToken', $ns_wsse);
    
    class UserNameT2 {
    private $UsernameToken;  
    //Name must be  identical to corresponding XML tag in SOAP header
    function __construct ($innerVal){
        $this->UsernameToken = $innerVal;
    }
    }
    $tmp = new UsernameT2($uuT);
    $userToken = new SoapVar($tmp, SOAP_ENC_OBJECT, NULL, $ns_wsse, 'UsernameToken', $ns_wsse);
    
    $secHeaderValue=new SoapVar($userToken, SOAP_ENC_OBJECT, NULL, 
                                            $ns_wsse, 'Security', $ns_wsse);
    $secHeader = new SoapHeader($ns_wsse, 'Security', $secHeaderValue);
    $result2 = $soap->__soapCall("CPMobiPrint",array(),null,$secHeader);
    echo $result2;