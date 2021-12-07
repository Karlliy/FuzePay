<?php
$html = file_get_html("http://www.taiwanlottery.com.tw/Lotto/3D/history.aspx");
echo $contents;

function get_content($url)
{
   $ch = curl_init();

   	curl_setopt ($ch, CURLOPT_URL, $url);
   	curl_setopt ($ch, CURLOPT_HEADER, 0);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 6000);
	curl_setopt ($ch, CURLOPT_TIMEOUT, 6000);

	ob_start();

	curl_exec ($ch);
	// 檢查是否有錯誤
	if(curl_errno($ch))
	{
		
		echo "curl_errno".curl_errno($ch)."<br />";
		curl_close ($ch);
		//exit;
		return get_content($url);
	}

	curl_close ($ch);
	$string = ob_get_contents();

	ob_end_clean();

	return $string;    
}