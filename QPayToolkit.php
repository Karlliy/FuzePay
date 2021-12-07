<?php
	//API 位置
	$targetUrl= "https://sandbox.sinopac.com/QPay.WebAPI/api/";
	//商店代號
	$ShopNo = "NA0251_001";
	//雜湊值
	$Hash = Array(
					"A1" => "912865293BCF446E",
					"A2" => "3E648ED6FB4F49FC",
					"B1" => "CA1F2B7A293F41D9",
					"B2" => "4A301B7394E44401"
				);

	//執行 API 服務方法
	function APIService($service, $objService){
		global $ShopNo, $Hash, $targetUrl;

		$url = $targetUrl . "/Order";

		//取得 Nonce 值
		$Nonce = getNonce($ShopNo, $targetUrl);
		//取得 HashID
		$HashID = getHashID($Hash);
		//取得 IV
		$IV = getIV($Nonce);
		//取得 Sign
		$Sign = getSign($objService, $Nonce, $HashID);
		//訊息內文 E2E 加密(先將變數為 null 移除)
		$objService = array_filter((array) ($objService));
		//print_r($objService);
		//echo json_encode($objService)."<hr>".$HashID."<hr>".$IV."<hr>";

		$Message = EncryptAesCBC(json_encode($objService), $HashID, $IV);
		echo $Nonce."<pre />";
		echo $HashID."<pre />";
		echo $IV."<pre />";
		echo json_encode($objService)."<pre />";
		echo $Sign."<pre />";
		echo $Message."<pre />";
		$AA = DecryptAesCBC($Message, $HashID, $IV);
		echo "[".$AA."]<pre />";
		//echo $Message;
		//建立 Request Class
		$Request = new API;
		$Request->ShopNo = $ShopNo;
		$Request->APIService = $service;
		$Request->Sign = $Sign;
		$Request->Nonce = $Nonce;
		$Request->Message = $Message;

		//建立 Response Class
		$Response = new API;

		//取得永豐銀行回覆訊息
		$Response = json_decode(WebAPI($url, json_encode($Request)));
		print_r($Response);
		//取得 Response Nonce
		$ResNonce = $Response->Nonce;
		//echo $ResNonce;
		//取得 Response IV
		$ResIV = getIV($ResNonce);
		//取得永豐銀行訊息內文
		$result = DecryptAesCBC($Response->Message, $HashID, $ResIV);
		echo $result."<pre />";
		//echo $HashID."|".$ResIV;
		return $result;
	}

	//取得 Nonce 方法
	function getNonce($shopno, $targeturl){
		$url = $targeturl . "/Nonce";

		$post_data = json_encode(array('ShopNo' => $shopno));
		$result = WebAPI($url, $post_data);
		$result = json_decode($result);

		return $result->Nonce;
	}

	//取得 Hash ID 計算方法
	function getHashID($hash){

		//var_dump($hash);

		$Byte_A1 = strToHexBytes($hash["A1"]);
		$Byte_A2 = strToHexBytes($hash["A2"]);
		$Byte_B1 = strToHexBytes($hash["B1"]);
		$Byte_B2 = strToHexBytes($hash["B2"]);

		$XOR1 = setXOR($Byte_A1, $Byte_A2);
		$XOR2 = setXOR($Byte_B1, $Byte_B2);

		$result = hexBytesToString($XOR1).hexBytesToString($XOR2);

		return $result;
	}

	//取得 IV 計算方法
	function getIV($nonce){
		$data = SHA256($nonce);
		return substr($data, strlen($data) - 16, 16);
	}

	//取得 Sign 計算方法
	function getSign($data, $nonce, $hashid){
		$result = '';
		$content = '';

		//移除 null
		$data = array_filter((array) ($data));

		//對欄位升序排序
		ksort($data);

		//訊息排序組合
		while ($fruit_name = current($data)) {

			//僅抓取第一層變數作組合
			if (is_array($data[key($data)]) == false){
				$content .= key($data) . '=' . $data[key($data)] . '&';
			}

			next($data);
		}
		$content = substr($content , 0, strlen($content) -1);

		//字串雜湊
		$content .=  $nonce . $hashid;

		//SHA256
		$result = SHA256($content);

		return $result;
	}

	//字串轉 Hex 方法
	function strToHexBytes($string){
		$hex = array();
		$j = 0;

		for ($i=0; $i<strlen($string); $i+=2){
			$hex[$j] = (int)base_convert(substr($string, $i, 2), 16, 10);

			$j+=1;
		}

		return $hex;
	}

	//Hex 轉字串方法
	function hexBytesToString($hex){
		$result = '';
		$str = '';

		for ($i=0; $i<sizeof($hex); $i++){
			$str = (string)base_convert($hex[$i], 10, 16);

			if (strlen($str) < 2){
				$str = '0' . $str;
			}

			$result .= $str;
		}

		return strtoupper($result);
	}

	//XOR 計算方法
	function setXOR($byte1, $byte2){

		$result = array();

		for ( $i=0 ; $i<sizeof($byte1) ; $i++ ) {
			$result[$i] = ($byte1[$i] ^ $byte2[$i]);
		}

		return $result;
	}

	//AES CBC 加密
	function EncryptAesCBC($data, $key, $iv) {
		$result = '';

		$padding = 16 - (strlen($data) % 16);
		//$data .= str_repeat(chr($padding), $padding);
		$encrypt = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

		$result = strtoupper(bin2hex($encrypt));

		return $result;
	}

	//AES CBC 解密
	function DecryptAesCBC($data, $key, $iv) {
		$result = '';

		$encrypt = hex2bin($data);

		$decrypt = openssl_decrypt($encrypt, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
		//echo "<br />\$decrypt".$decrypt;
		//$padding = ord($decrypt[strlen($decrypt) - 1]);
		//echo "<br />\$padding".$padding;
		//$result = substr($decrypt, 0, -$padding);
		$result = $decrypt;
		return $result;
	}

	//SHA256 後字串轉大寫
	function SHA256($data){
		return strtoupper(hash('sha256', $data));
	}

	//WebAPI 串接方法
	function WebAPI($url, $post_data) {
		$ch=curl_init($url);
		$options=Array(
			CURLOPT_HEADER => 0,
			CURLOPT_NOBODY => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HTTPHEADER => array("Content-type: application/json; charset=utf-8"),
			CURLOPT_POST=> 1,
			CURLOPT_POSTFIELDS=> $post_data ,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSLVERSION => 6					//(1:TLSv1 / 6:TLSv1_2)  使用 TLS 1.2 Protocol
		);
		curl_setopt_array($ch, $options);
		$result=curl_exec($ch);
		curl_close($ch);
		//echo "1".$result ."<br />";

		return $result;
	}

	//Request、Response 呼叫用
	class API
	{
		//API 版本
		public $Version = '1.0.0';
		//商店代號
		public $ShopNo;
		//API服務
		public $APIService;
		//安全簽章
		public $Sign;
		//時間戳記
		public $Nonce;
		//交易訊息內文
		public $Message;
	}

	//建立訂單交易(OrderCreate)
	class OrderCreate
	{
		//商店代號
		public $ShopNo;
		//訂單編號
		public $OrderNo;
		//訂單總金額
		public $Amount;
		//幣別
		public $CurrencyID = 'TWD';
		//收款名稱
		public $PrdtName;
		//備註
		public $Memo;
		//自訂參數一
		public $Param1;
		//自訂參數二
		public $Param2;
		//自訂參數三
		public $Param3;
		//導回頁面
		public $ReturnURL;
		//背端通知
		public $BackendURL;
		//收款方式
		public $PayType;
		//虛擬帳號用 Param
		public $ATMParam  = array(
								//'ExpireDate'=> null
							);
		//信用卡用 Param
		public $CardParam = array(
								//'AutoBilling'=> null,
								//'ExpBillingDays'=> null,
								//'ExpMinutes'=> null,
								//'PayTypeSub'=> null,
								//'Staging'=> null
							);
	}

	//信用卡 - 待請款訂單查詢(OrderUnCapturedQuery)
	class OrderUnCapturedQuery
	{
		//商店代號
		public $ShopNo;
		//訂單編號
		public $OrderNo;
	}

	//信用卡 – 訂單維護服務(OrderMaintain)
	class OrderMaintain
	{
		//商店代號
		public $ShopNo;
		//訂單編號
		public $OrderNo;
		//發送要求
		public $Command;
		//申請退款金額
		public $Amount;
		//退款原因
		public $Remark;
	}

	//訂單交易查詢(OrderQuery)
	class OrderQuery
	{
		//商店代號
		public $ShopNo;
		//訂單編號
		public $OrderNo;
		//收款方式
		public $PayType;
		//交易日期(起)
		public $OrderDateTimeS;
		//交易日期(迄)
		public $OrderDateTimeE;
		//付款日期(起)
		public $PayDateTimeS;
		//付款日期(迄)
		public $PayDateTimeE;
		//付款狀態為條件查詢
		public $PayFlag;
	}

	//訊息查詢服務(OrderPayQuery)
	class OrderPayQuery
	{
		//商店代號
		public $ShopNo;
		//Token 值
		public $PayToken;
	}

	//交易對帳檔查詢服務(BillQuery)
	class BillQuery
	{
		//商店代號
		public $ShopNo;
		//交易對帳日期
		public $BillDate;
	}

	//撥款檔查詢服務(AllotQuery)
	class AllotQuery
	{
		//商店代號
		public $ShopNo;
		//撥款日期(起)
		public $AllotDateS;
		//撥款日期(迄)
		public $AllotDateE;
		//收款方式
		public $PayType;
	}
?>