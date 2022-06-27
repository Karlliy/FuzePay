<?php
ini_set('SHORT_OPEN_TAG', "On"); // 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors', "On"); // 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
include_once("BaseClass/Setting.php");
include_once("BaseClass/CDbShell.php");
//echo date('Y-m-d H:i:s', strtotime("2022-04-06T22:20:18+08:00" . " +8 hours"));
// $server_url = "https://4128888card.com.tw/token";
// $token = get_token($server_url, "834385980001", "83438598Aa@");
// var_dump($token);
$CashFlowID = Date("ymdHis") . str_pad(floor(microtime() * 10000), 4, '0', STR_PAD_LEFT) . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

$parameter = array(	
    "icpId"             => "regentcp",
    "icpOrderId"        => $CashFlowID,
    "icpProdId"         => "rcp0001",
    "mpId"              => "TSTART",
    "memo"              => "TEST",
    "icpUserId"         => "User123",
    "icpProdDesc"       => "3C",
    "price"             => "50",
    "returnUrl"         => "https://pay.fueastpay.com/TelecomNotify.php",
    "doAction"          => "authOrder"
);


//$strReturn = SockPost("https://mpay-dev.so-net.net.tw/paymentRule.php", $parameter, $curlerror);
$strReturn = SockPost("https://mpapi-dev.so-net.net.tw/microPaymentPost.php", http_build_query($parameter,'','&'), $curlerror);

$finalAry = getResult($strReturn);
				
$rtMsg = (string)$finalAry['resultCode'];

if($rtMsg == "00000"){

    $parameter = array(	
        "icpId"             => "regentcp",
        "icpOrderId"        => $CashFlowID,
        "icpProdId"         => "rcp0001",
        "mpId"              => "TSTART",
        "memo"              => "TEST",
        "icpUserId"         => "User123",
        "icpProdDesc"       => "3C",
        "authCode"          => $finalAry['authCode']
    );

    $sHtml = "<form id='rongpaysubmit' name='rongpaysubmit' action='https://mpay-dev.so-net.net.tw/paymentRule.php' method='POST'>";
    foreach($parameter as $key => $value) 
    {
        $sHtml = $sHtml."<input type='hidden' name='".$key."' value='".$value."'/>";
    }
    $sHtml = $sHtml."<input type='submit' value='付款' style='display:none'></form>";
    $sHtml = $sHtml."<script>document.forms['rongpaysubmit'].submit();</script>";
    echo $sHtml;
    exit;
}
var_dump($finalAry);

/*
*/
// function get_token($server_url, $username, $password) {
//     $cl = curl_init($server_url);
//     curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, 0);
//     curl_setopt($cl, CURLOPT_SSLVERSION, 6); //TLS v1.2
//     curl_setopt($cl, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($cl, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-formurlencoded"));
//     curl_setopt($cl, CURLOPT_POST, true);
//     curl_setopt($cl, CURLOPT_POSTFIELDS,"grant_type=password&username=$username&password=$password");
//     $auth_response = curl_exec($cl);
//     if ($auth_response === false) {
//         echo "Failed to authenticate\n";
//         var_dump(curl_getinfo($cl));
//         curl_close($cl);
//         return NULL;
//     }
//     curl_close($cl);
//     return json_decode($auth_response, true);
// }

function SockPost($URL, $Query, &$curlerror){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $Query);
    $SSL = (substr($URL, 0, 8) == "https://" ? true : false); 
    if ($SSL) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $Query);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $postfield);
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

function getResult($result,$method = "post"){
    $rtAry = explode("\t",$result);
    $keyAry = explode("|",$rtAry[0]);
    $valueAry = explode("|",$rtAry[1]);
    $finalAry = array();
    for ($i=0; $i<count($keyAry); $i++) {
        $finalAry[$keyAry[$i]] = $valueAry[$i];
    }
    return $finalAry;
}

   
