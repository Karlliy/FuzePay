<?php
ini_set('SHORT_OPEN_TAG', "On"); // 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
ini_set('display_errors', "On"); // 是否將錯誤信息作為輸出的一部分顯示。
ini_set('error_reporting', E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=utf-8');
include_once("../BaseClass/Setting.php");
include_once("../BaseClass/CDbShell.php");
//var_dump($_POST);
try {
    if (preg_match("/^[0-9]{7}$/", trim($_POST["VerifyCode"])) == 0)
    {
        throw new exception("請輸入正確驗證碼！");
    }

    @CDbShell::connect();
    $sql = "SELECT * FROM smscheck WHERE CheckCode = '".$_POST["VerifyCode"]."' AND Token = '' AND EffectiveTime >= '".date("Y-m-d H:i:s")."'";
    $result = CDbShell::query($sql);
    if (CDbShell::num_rows($result) == 1) {
        GetRandom(50, $_Token);

        $sql = "UPDATE smscheck SET Token = '".$_Token."' WHERE CheckCode = '".$_POST["VerifyCode"]."' AND EffectiveTime >= '".date("Y-m-d H:i:s")."'";
        CDbShell::query($sql);

        $data["ReturnCode"] = "Ok";
        $data["Token"] = $_Token;
        $data["ReturnMessage"] = "";
        $return = json_encode($data);
        echo $return;

    }else {
        throw new exception("驗證碼錯誤！");
    }
    

}catch(Exception $e) {
    $data["ReturnCode"] = "Fail";
    $data["ReturnMessage"] = $e->getMessage();
    $return = json_encode($data);
    echo $return;
}

function GetRandom($length, &$randoma ) {

    $randoma = "";
    //mt_srand(mktime());
        
    for ($i=1; $i<=$length; $i=$i+1)
    {
        //亂數$c設定三種亂數資料格式大寫、小寫、數字，隨機產生
        $c=mt_rand(1,3);
        //在$c==1的情況下，設定$a亂數取值為97-122之間，並用chr()將數值轉變為對應英文，儲存在$b
        if($c==1){$a=mt_rand(97,122);$b=chr($a);}
        //在$c==2的情況下，設定$a亂數取值為65-90之間，並用chr()將數值轉變為對應英文，儲存在$b
        if($c==2){$a=mt_rand(65,90);$b=chr($a);}
        //在$c==3的情況下，設定$b亂數取值為0-9之間的數字
        if($c==3){$b=mt_rand(0,9);}
        //使用$randoma連接$b
        $randoma = $randoma.$b;
    }
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