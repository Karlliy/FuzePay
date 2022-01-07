<?php
	ini_set('SHORT_OPEN_TAG',"On"); 				// 是否允許使用\"\<\? \?\>\"短標識。否則必須使用\"<\?php \?\>\"長標識。
	ini_set('display_errors',"On"); 				// 是否將錯誤信息作為輸出的一部分顯示。
	ini_set('error_reporting',E_ALL & ~E_NOTICE);
	ini_set('memory_limit',"-1");					// 一個腳本所能夠申請到的最大內存字節數(可以使用K和M作為單位)。如果要取消內存限制，則必須將其設為 -1 。
	header('Content-Type: text/html; charset=utf-8');
	
	date_default_timezone_set("Asia/Taipei");
	
	define("SiteID", "H");
	define("Base_Company", "富責東帳務管理平台");
	define("Simplify_Company", "富責東");	
	define("Base_Address", " ");
	define("Base_URL", "");
	define("Base_TEL", " ");
	define("Base_FAX", " ");
	define("SMS_Mobile", "");
	//define("SMS_Mobile", "0987123456,0931123456");
	define("Receive_URL", "https://fuze-pay.com/");
	#國泰		
	define("Cathay_Code", "4513");
	#新光
	define("SKBank_Code", "725");
	#合庫
	define("TCBBank_Code", "088909");
	#凱基
	define("KGI_Code", "373");
	#凱基12碼
	define("KGI12_Code", "79183");
	#中信台幣
	define("CTBC_Code", "92401");
	#永豐
	define("SinoPac_ShopNo", "NA0251_001");
	#樂力活
	define("Lihuo_URL", "https://tw1.moneypay.com.tw/Pay/V1");
	define("Lihuo_Id", "ATA0000000001");
	define("Lihuo_Key", "11e5dc15b35924c30908df95aec903b3");
	#永豐
	define("SINOPAC_URL", "https://sandbox.sinopac.com/QPay.WebAPI/api/");
	define("SINOPAC_Code", "NA0251_001");
	define("SINOPAC_HashA1", "912865293BCF446E");
	define("SINOPAC_HashA2", "3E648ED6FB4F49FC");
	define("SINOPAC_HashB1", "CA1F2B7A293F41D9");
	define("SINOPAC_HashB2", "4A301B7394E44401");
	#永豐
	// define("SINOPAC_URL", "https://funbiz.sinopac.com/QPay.WebAPI/api/");
	// define("SINOPAC_Code", "DA2989_001");
	// define("SINOPAC_HashA1", "5F3463CD24E54562");
	// define("SINOPAC_HashA2", "9142A02537A946D5");
	// define("SINOPAC_HashB1", "5B358DEFA6AF4200");
	// define("SINOPAC_HashB2", "D0BD9071107B4F61");
	#藍新
	define('Spgate_URL', 'https://core.newebpay.com/MPG/mpg_gateway');
	define('Spgate_ID', 'ASP83438598');
    define('Spgate_Key', 'yEXXCZPiWQ3E0Wd9B5HC0dRYJAnfuUMU');
	define('Spgate_IV', 'PEm2B3Vd0Sq88r9C');

	#GASH 測試
	/*define('GASH_URL', "https://stage-api.eg.gashplus.com/CP_Module/order.aspx");
	define("GASH_MID", "M1000910");
	define("GASH_CID", "C009100001416");
	define("GASH_Key1", "YljhtJY+qoLLtCDqk4x5zyb7BqBRH4Ke");
	define("GASH_Key2", "xCfCrgWAkUQ=");
	define("GASH_TKey", "Q2wU5RmK#N");*/
	#GASH 正試
	define('GASH_URL', "https://api.eg.gashplus.com/CP_Module/order.aspx");
	define("GASH_MID", "M1000825");
	define("GASH_CID", "C008250001548");
	define("GASH_Key1", "dCq0viblQTDNKIrBMwLbnYqGfleAOIzn");
	define("GASH_Key2", "K00xqwl0q2U=");
	define("GASH_TKey", "fvDcS2QapkJ");

	define('TapPay', "https://sandbox.tappaysdk.com/tpc/payment/pay-by-prime");
	define('TapPay_PartnerKey', 'partner_Mdk1b8YCaOf3C0ybU1l4IGA7n2DWf5d1Yi9f80LMFBWlH7VV5jxpkrmt');

	#711條碼測試
	define('Seven_URL', "http://61.57.234.95/CPMobiService/CPMOBI_PayService.asmx?wsdl");
?>