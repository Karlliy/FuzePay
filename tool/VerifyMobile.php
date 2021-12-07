<?php
ini_set('SHORT_OPEN_TAG', "On"); // 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors', "On"); // 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
include_once("../BaseClass/Setting.php");
include_once("../BaseClass/CDbShell.php");
//var_dump($_POST);
try {
    if (preg_match("/^09[0-9]{8}$/", trim($_POST['Mobile'])) == 0)
    {
        throw new exception("請輸入正確手機號碼,手機號碼只能數字,不得有其他符號！");
    }

    @CDbShell::connect();

    $VerificationCode = ""; 
    $VerificationCode = str_pad(rand(0, 10000000), 7, '0', STR_PAD_LEFT);

    Again:
    $sql = "SELECT * FROM smscheck WHERE CheckCode = '".$VerificationCode."' AND EffectiveTime >= '".date("Y-m-d H:i:s")."'";
    $result = CDbShell::query($sql);
    if (CDbShell::num_rows($result) > 0) {
        goto Again;
    }
    $url="https://api.twsms.com/smsSend.php";
    $get_string = "username=humorcorp1&password=ht708hifw10q&mobile=".Trim($_POST["Mobile"])."&message=驗證碼為 ".$VerificationCode."，請在3分鐘內輸入完畢並按驗證，非本人操作請勿理會本訊息。";

    $string = sock_post($url, $get_string);
    $xml = simplexml_load_string($string);
    //print_r($xml);
    $code = (string)$xml->code;
    $text = (string)$xml->text;
    //$obj = json_decode($string);
    if ($code == "00000") {			//發送簡訊成功

        $EffectiveTime = date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s") ." +3 minute"));
        $sql = "INSERT INTO smscheck (Mobile, CheckCode, EffectiveTime) VALUES ('".trim($_POST["Mobile"])."', '".$VerificationCode."', '".$EffectiveTime."')";
        CDbShell::query($sql);	

        $data["ReturnCode"] = "Ok";
        $data["ReturnMessage"] = "";
        $return = json_encode($data);
        echo $return;

    }else {
        throw new exception("驗證碼發送失敗！[".$text."]");
    }

}catch(Exception $e) {
    $data["ReturnCode"] = "Fail";
    $data["ReturnMessage"] = $e->getMessage();
    $return = json_encode($data);
    echo $return;
}

function sock_post($url,$query){
    $data = "";
    $info = parse_url($url);
    $fp = fsockopen($info["host"], 80, $errno, $errstr, 30);
    if(!$fp){
        return $data;
    }
    $head="POST ".$info['path']." HTTP/1.0\r\n";
    $head.="Host: ".$info['host']."\r\n";
    $head.="Referer: http://".$info['host'].$info['path']."\r\n";
    $head.="Content-type: application/x-www-form-urlencoded\r\n";
    $head.="Content-Length: ".strlen(trim($query))."\r\n";
    $head.="\r\n";
    $head.=trim($query);
    $write=fputs($fp, $head);
    $header = "";
    while ($str = trim(fgets($fp,4096))) {
        $header.=$str;
    }
    while (!feof($fp)) {
        $data .= fgets($fp,4096);
    }
    return $data;
}
?>