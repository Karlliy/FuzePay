<?php

/*$string = "20310057266011106060000001178M00000000000000000000000500+00000000000100000041665440578825CF0130000699506388647";

var_dump(trim(mb_substr($string, 75, 14, "utf-8")));
var_dump(intval(mb_substr($string, 43, 11, "utf-8")));
//$_Year          = trim(mb_substr($_POST["body"], 49, 3, "utf-8"));
//$_DateTime      = trim(mb_substr($_POST["body"], 52, 10, "utf-8"));
var_dump(trim(mb_substr($string, 72, 1)));
var_dump(trim(mb_substr($string, 19, 7)));
var_dump(trim(mb_substr($string, 91, 19)));*/

$string = "Amount=50&Fee=0&HashKey=9B60CF7266AF5FF8C5AA5713D80AB78854DC305067D5AB4675962AADF2CF7203&LastStatusChgTime=2022%2F06%2F14%2017%3A30%3A46&OrderId=22061415474816614094&PayInfo=%7B%22Apply%22%3A%22Y%22%2C%22InAccountNo%22%3A%220041666062838649%22%2C%22DueDate%22%3A%2220220628%22%2C%22PayAccountNo%22%3A%228120028881006591663%22%7D&PayType=REG&PlatFormId=FCB008343859801&PlatFormName=%E5%AF%8C%E8%B2%AC%E6%9D%B1&ReCheckId=0b220614154748450&ReturnDesc=&ReturnStatus=&ReturnType=AsynReturn&ShippingFee=0&ToolDesc=%E5%9F%B7%E8%A1%8C%E6%88%90%E5%8A%9F&ToolStatus=0000&TransStatus=2&TransTime=2022%2F06%2F14%2015%3A49%3A15";
$string = explode('&',$string);

foreach($string as $key => $val)
{
    list($key2, $val2) = explode('=',$val);
    $Data[$key2] = $val2;
    echo "key =>".$key2."  val=>".$val2.PHP_EOL;
};	
var_dump($Data );