<?php 
class CFirm {
	
	var $DB_Table			= "firm";
	var $PageItems			= 100000;
	var $GetAdmHtmlPath		= "../adm_html/firm/";
	var $AdminSno			= -1;
	var $SearchKeyword		= "";
	
	public function	__construct () {
		$db = new CDbShell();    
		$Session = new CSession;	
		$JSModule = new JSModule();
		$this->AdminSno = $Session->GetVar("AdminSno");
	}
	
	function CaseFunctions_Admin() {
		$curl = new CUrlQuery();
    	$td = @mcrypt_module_open('tripledes', '', MCRYPT_MODE_ECB, '');  		
		$mcrypt_key = substr("karlliy", 0, @mcrypt_enc_get_key_size($td));			  
		// 從一個隨機來源建立一個初始向量     
		$iv = @mcrypt_create_iv (@mcrypt_enc_get_iv_size($td), MCRYPT_RAND);	
		@mcrypt_generic_init($td, $mcrypt_key, $iv);
		$curl->getvars2(@mdecrypt_generic($td, urldecode($_SERVER["QUERY_STRING"])));
		
		$url = @urlencode(@mcrypt_generic($td, "func=center"));
        $GetFunc = $curl->getvars3("func");
		if($GetFunc!="") $func = $GetFunc;
		//else if($_GET["func"]!="") $func=$_GET["func"];
		
		switch ($func) {
			case "Added":
				$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0 || (CSession::getVar("IsChild") == 1 && array_search("FirmAdd", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				$this->Added();
    			}
    			break;
    		case "AddedBranch":
				$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0  || (CSession::getVar("IsChild") == 1 && array_search("FirmEdit", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				self::AddedBranch();
    			}
    			break;
    		case "Modify":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0 || (CSession::getVar("IsChild") == 1 && array_search("FirmEdit", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				$this->Modify();
    			}
    			break;
    		case "Deletion":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0 || (CSession::getVar("IsChild") == 1 && array_search("FirmDel", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				self::Deletion();
    			}
    			break;
    		case "Detail":
    			self::Detail();
    			break;
    		case "Commission":
    			if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("ChargeLayout", CSession::getVar("Purview")) != false)) {
					self::Commission();
	    		}
    			break;
    		case "SetCommission":
    			if ((CSession::getVar("IsChild") == 0 || (CSession::getVar("IsChild") == 1 && array_search("ChargeSet", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
	    			self::SetCommission();
	    		}
    			break;
    		case "SetInterfaced":
    			if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("InterfacedSet", CSession::getVar("Purview")) != false)) {
	    			self::SetInterfaced();
	    		}
    			break;
    		case "FirmSetInterfaced":
    			if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("InterfacedSet", CSession::getVar("Purview")) != false)) {
	    			self::FirmSetInterfaced();
	    		}
    			break;
    		case "Cashier":
    			if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("CashierLayout", CSession::getVar("Purview")) != false)) {
	    			self::Cashier();
	    		}
    			break;
    		case "RefusalIP":
    			if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("RefusalIP", CSession::getVar("Purview")) != false)) {
	    			self::RefusalIP();
	    		}
    			break;
    		case "SetRefusalIP":
    			if (((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("RefusalIP", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
	    			self::RefusalIP();
	    		}
    			break;
    		case "WithdrawPoints":
				self::WithdrawPoints();
    			break;
    		case "WithdrawPointsTW":
				self::WithdrawPointsTW();
    			break;
    		case "WithdrawPointsManage":
				self::WithdrawPointsManage();
    			break;
    		case "WithdrawPointsTWManage":
				self::WithdrawPointsTWManage();
    			break;
    		case "PointChangeLog":
				self::PointChangeLog();
    			break;
    		case "PointChangeLogManage":
				self::PointChangeLogManage();
    			break;
    		case "Withdraw":
				self::Withdraw();
    			break;
    		case "WithdrawManage":
    			self::WithdrawManage();
    			break;
    		case "GetPoints":
    			self::GetPoints();
    			break;
    		case "Instead":
    			self::Instead();
    			break;
    		case "GetSmsSwitch":
    			self::GetSmsSwitch();
    			break;
    		case "UpdateSmsSwitch":
    			self::UpdateSmsSwitch();
    			break;
    		case "GetNoLooked":
    			self::GetNoLooked();
    			break;
			default:
				$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
				if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("FirmLayout", CSession::getVar("Purview")) != false)) {
					$this->Manage();
				}
				break;
		}
	}
	
	static function VerifyData()
    {
        if (strlen(trim($_POST['Name'])) < 2)
        {
            throw new exception("請輸入特店登記名稱!");
        }
        
        if (strlen(trim($_POST['PublicName'])) < 2)
        {
            throw new exception("請輸入特店對外名稱!");
        }
        
        if ($_POST['Industry'] == "其他" && strlen(trim($_POST['OtherIndustry'])) < 2)
        {
            throw new exception("特店產業別選擇其他時請輸入特店產業別!");
        }
        
        if (strlen(trim($_POST['FirmCode'])) < 2)
        {
            throw new exception("請輸入正確特店代號!");
        }
        
        if ($_GET["func"] != "Modify") {
	        CDbShell::query("SELECT * FROM Firm WHERE FirmCode = '".$_POST['FirmCode']."'");
			if (CDbShell::num_rows() > 0) {
				throw new exception("特店代號不得重複");
			}
		}else {
			CDbShell::query("SELECT * FROM Firm WHERE FirmCode = '".$_POST['FirmCode']."' AND Sno != ".$_GET["Sno"]);
			if (CDbShell::num_rows() > 0) {
				throw new exception("特店代號不得重複");
			}
		}
        /*if (strlen(trim($_POST["Invoice"])) < 2 ) {
        	throw new exception("請選擇發票資料");
        }*/
        
        if (trim($_POST["Invoice"]) == "三聯式，不同商店資料" && strlen(trim($_POST["OtherInvoice"])) < 2 ) {
        	throw new exception("發票選擇「不同商店資料」時，請輸入發票抬頭!");
        }
        
        /*if (strlen(trim($_POST['ResponsiblePerson'])) < 2)
        {
            throw new exception("請輸入負責人!");
        }
        
        if (strlen(trim($_POST['RegisterAddress'])) < 6)
        {
            throw new exception("請輸入正確登記地址!");
        }
        
        if (strlen(trim($_POST['TEL'])) < 10)
        {
            throw new exception("請輸入正確電話!");
        }*/
    }
    
    static function CheckDateTime($date_time)
	{
	    $check = false;
	    if (strtotime($date_time)){
	    //不管檢查時間或日期格式，都只取第一個陣列值
	        list($first) = explode(" ", $date_time);
	        //如果包含「:」符號，表示只檢查時間
	        if (strpos($first, ":")){
	            //strtotime函數已經檢查過，直接給true
	            $check = true;
	        }else{
	            //將日期分年、月、日，區隔符用「-/」都適用
	            list($y, $m, $d) = preg_split("/[-\/]/", $first);
	            //檢查是否為4碼的西元年及日期邏輯(潤年、潤月、潤日）
	            if (substr($date_time, 0, 4)==$y && checkdate($m, $d, $y)){
	                $check = true;
	            }
	        }
	    }
		return $check;
	}
	
	function AllPages() {
		$db=new CDbShell;
		
		if(strlen($this->search_key)) $db->query("select id from $this->DB_Table $this->search_key"); // search_key = "where ...." 
		else $db->query("select Sno from $this->DB_Table");		
		$all = $db->num_rows();
		$db->DB_close();
		
		$pages = (INT)($all / $this->PageItems);
		if($all % $this->PageItems == 0) $pages--;
		return $pages;
	}
    
    function showPageBar($prod_ipage, $pages, $option_query)
    {
        if($pages < 1) return;
		$prevpage=$prod_ipage1; 
		if($prevpage<0) $prevpage=0;
		$nextpage=$prod_ipage+1;
		if($nextpage>$pages) $nextpage=$pages;
		$pagebar="";
		
		if($prod_ipage > 0) $pagebar.="<span onclick=\"document.location='".$_SERVER["PHP_SELF"]."?news_ipage=".($prod_ipage-1)."$option_query'\" style=\"cursor: pointer;\"></span>";
        else $pagebar.= "<span></span>";
                
        $tp=9;
        $pi=0;
        $hpages=$pages;
        if($pages>$tp) {
        	$htp=(INT)($tp/2);
        	$pi=$prod_ipage-$htp;
        	if($pi<0) $pi=0; 
        	$hpages=$pi+$tp;
        	if($hpages >$pages ) { 
				$hpages=$pages; 
				$pi=$hpages-$tp;
			}
        }
		for($i=$pi;$i<= $hpages ;$i++) {
			if($prod_ipage != $i) $pagebar.="<li onclick=\"document.location='".$_SERVER["PHP_SELF"]."?news_ipage=".$i."$option_query'\" style=\"cursor: pointer;\">". ($i+1);
			else $pagebar.= "<li class=\"on\">" . ($i+1);
			
			if($prod_ipage!=$i) $pagebar.="</li>";
			else $pagebar.="</li>";
		}
		
		if($prod_ipage < $pages) $pagebar.="<span onclick=\"document.location='".$_SERVER["PHP_SELF"]."?news_ipage=".($prod_ipage+1)."$option_query'\" style=\"cursor: pointer;\"></span>";
        else $pagebar.= "<span></span>";
		return $pagebar;
    } 
    
    function Manage() {
    	$AdminLevel = CSession::getVar("AdminLevel");
        $Boss 		= CSession::getVar("Boss");
            	
        if ($AdminLevel == 3 && $_GET["attr"] == "interfaced" ) {
        	CDbShell::query("select * from Firm WHERE Sno = ".CSession::getVar("FirmSno").""); 
    		$Row = CDbShell::fetch_array();
    		
    		$card = Receive_URL."card.php?Sno=".Cryptographic::encrypt($Row["Sno"]);
    		include($this->GetAdmHtmlPath . "FirmSetInterfaced.html");
        }else {
	    	$nowitem = $_GET["ipage"] * $this->PageItems;
	    	$PageBar = self::showPageBar($_GET["ipage"], $Pages, "");
	    	
	    	$Keyword = (strlen(trim($_POST["Keyword"])) > 0) ? $_POST["Keyword"] : $_GET["Keyword"];
	    	if (strlen(trim($Keyword)) > 0 ) {
	    		$this->SearchKeyword = " WHERE (Name like '%".$Keyword."%' or ResponsiblePerson like '%".$Keyword."%' or FirmCode like '%".$Keyword."%')";
	    	}
	    	
	    	if ($AdminLevel == 3) {
	    		if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    		else $this->SearchKeyword .= " AND ";
	    		
	    		$_ChildSno .= CSession::getVar("FirmSno")."";
	    		$_ChildSno .= ",";
	    		
	    		CDbShell::query("SELECT * FROM ".$this->DB_Table." WHERE ParentSno = ".CSession::getVar("FirmSno"));
	    		if (CDbShell::num_rows() > 0) {
		    		while ($Row = CDbShell::fetch_array()) { 
		    			$_ChildSno .= $Row["Sno"];
		    			$_ChildSno .= ",";
		    		}
		    	}
		    	
	    		$_ChildSno = substr_replace($_ChildSno,'',-1);
	    		$this->SearchKeyword .= " Sno IN (".$_ChildSno.")";
	    	}
	    	
	    	CDbShell::query("SELECT * FROM $this->DB_Table ". $this->SearchKeyword ." ORDER BY Sno LIMIT ".$nowitem."," . $this->PageItems); 
	    	//echo "SELECT * FROM $this->DB_Table ". $this->SearchKeyword ." ORDER BY Sno LIMIT ".$nowitem."," . $this->PageItems;
	    	while ($Row = CDbShell::fetch_array()) {
	    		if (date("Y-m-d", strtotime(date('Y-m-d'). " +30 day")) >= $Row["ContractDate"]) $Row["background"] = "style='background-color:#FFEEFA'";
	    		else $Row["background"] = "";
	    		$Row["DelLink"] = $_SERVER["PHP_SELF"]."?func=Deletion&Sno=".$Row["Sno"];
	    		$Html[] = $Row;
	    	}
	    	include($this->GetAdmHtmlPath . "Manage.html");
    	}
    }
    
    function Commission() {
    	$AdminLevel = CSession::getVar("AdminLevel");
        $Boss 		= CSession::getVar("Boss");
            	
        if ($AdminLevel <= 2) {
	    	$nowitem = $_GET["ipage"] * $this->PageItems;
	    	
	    	//$Pages = self::AllPages();
	    	$PageBar = self::showPageBar($_GET["ipage"], $Pages, "");
	    	$Keyword = (strlen(trim($_POST["Keyword"])) > 0) ? $_POST["Keyword"] : $_GET["Keyword"];
	    	if (strlen(trim($Keyword)) > 0 ) {
	    		$this->SearchKeyword = " where (FirmCode like '%".$Keyword."%' or Name like '%".$Keyword."%' or ResponsiblePerson like '%".$Keyword."%')";
	    	}
	    	
	    	// $Result = CDbShell::query("SELECT * FROM $this->DB_Table ". $this->SearchKeyword ." ORDER BY Sno LIMIT ".$nowitem."," . $this->PageItems); 
	    	// //echo "select Main.*, admin.Department, admin.Name from $this->DB_Table AS Main left join admin on Main.AdminSno = admin.Sno ". $this->SearchKeyword ." order by Main.ReleaseTime desc, Main.Sno desc limit ".$nowitem."," . $this->PageItems;
	    	// while ($Row = CDbShell::fetch_array($Result)) {
	    	// 	$Result2 = CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno  WHERE FC.FirmSno = ".$Row["Sno"]." AND FC.Enable = 1 AND (PF.Kind = '信用卡' OR PF.Kind = '信用卡3期' OR PF.Kind = '信用卡12期')");
	    	// 	$Param1Row = CDbShell::fetch_array($Result2);
	    	// 	if (CDbShell::num_rows($Result2) > 0) {
	    	// 		$Row["Param1"] = $Param1Row["Mode"]."<font color='blue'>[手續費". (($Param1Row["FeeRatio"] == 0 && $Param1Row["FixedFee"] > 0) ? $Param1Row["FixedFee"]." 元" : $Param1Row["FeeRatio"]." %")."]</font>";
	    	// 	}
	    		
	    	// 	$Result3 = CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno  WHERE FC.FirmSno = ".$Row["Sno"]." AND FC.Enable = 1 AND PF.Type = 2");
	    	// 	$Param2Row = CDbShell::fetch_array($Result3);
	    	// 	if (CDbShell::num_rows($Result3) > 0) {
	    	// 		$Row["Param2"] = $Param2Row["Mode"]."<font color='blue'>[手續費".(($Param2Row["FeeRatio"] == 0 && $Param2Row["FixedFee"] > 0) ? $Param2Row["FixedFee"]." 元" : $Param2Row["FeeRatio"]." %")."]</font>";
	    	// 	}
	    		
	    	// 	$Result4 = CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno  WHERE FC.FirmSno = ".$Row["Sno"]." AND FC.Enable = 1 AND PF.Type = 3");
	    	// 	$Param3Row = CDbShell::fetch_array($Result4);
	    	// 	if (CDbShell::num_rows($Result4) > 0) {
	    	// 		$Row["Param3"] = $Param3Row["Mode"]."<font color='blue'>[手續費".(($Param3Row["FeeRatio"] == 0 && $Param3Row["FixedFee"] > 0) ? $Param3Row["FixedFee"]." 元" : $Param3Row["FeeRatio"]." %")."]</font>";
	    	// 	}
	    		
	    	// 	$Result5 = CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno  WHERE FC.FirmSno = ".$Row["Sno"]." AND FC.Enable = 1 AND PF.Type = 4");
	    	// 	$Param4Row = CDbShell::fetch_array($Result5);
	    	// 	if (CDbShell::num_rows($Result5) > 0) {
	    	// 		$Row["Param4"] = $Param4Row["Mode"]."<font color='blue'>[手續費".(($Param4Row["FeeRatio"] == 0 && $Param4Row["FixedFee"] > 0) ? $Param4Row["FixedFee"]." 元" : $Param4Row["FeeRatio"]." %")."]</font>";
	    	// 	}
	    		
	    	// 	$Result6 = CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno  WHERE FC.FirmSno = ".$Row["Sno"]." AND FC.Enable = 1 AND PF.Type = 5");
	    	// 	$Param5Row = CDbShell::fetch_array($Result6);
	    	// 	if (CDbShell::num_rows($Result6) > 0) {
	    	// 		$Row["Param5"] = $Param5Row["Mode"]."<font color='blue'>[手續費".(($Param5Row["FeeRatio"] == 0 && $Param5Row["FixedFee"] > 0) ? $Param5Row["FixedFee"]." 元" : $Param5Row["FeeRatio"]." %")."]</font>";
	    	// 	}
	    		
	    	// 	$Html[] = $Row;
	    	// }

			CDbShell::query('SET @@group_concat_max_len = 1000000;');
			$Result = CDbShell::query('SELECT F.Sno, F.Name, F.FirmCode, GROUP_CONCAT("<span style=font-weight:bold;>",PF.Kind,"(<font color=db8a00>", PF.Mode,"</font>)[<font color=#ff0080>手續費</font> <font color=blue>固定", FC.FixedFee ,"+", FC.FeeRatio,"%</font>]</span>" ORDER BY PF.Type) AS channel, F.Remark FROM firm AS F INNER JOIN firmcommission AS FC LEFT JOIN paymentflow AS PF ON F.Sno = FC.FirmSno AND FC.PaymentFlowSno = PF.Sno WHERE FC.Enable = 1 GROUP BY F.Sno ORDER BY F.Sno, PF.Type');
			
			while ($Row = CDbShell::fetch_array($Result)) {

				$Row['channel'] = str_replace(",", "<BR />", $Row['channel']);
				$Html[] = $Row;
			}
	    	include($this->GetAdmHtmlPath . "ManageCommission.html");
    	}else {
    		self::FirmCommission();
    		exit;
    	}
    }
	function Added() {
		
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				self::VerifyData();
				
				$i = 0;
				AgainHashKey:
				$HashKey = ""; 
				$Character = array ("A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M", "N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y" ,"3", "4", "5", "6", "7", "8", "9");

				for ($i = 0; $i <= 25; $i++) {
					$RandChar = "";
					$RandChar = $Character[rand(0, Count($Character))];
					$HashKey .= $RandChar;
				}

				$sql = "SELECT * FROM $this->DB_Table WHERE HashKey = '".$HashKey."' OR HashIV = '".$HashKey."'";
				CDbShell::query($sql);
				if (CDbShell::num_rows() > 0) {
					goto AgainHashKey;
				}
				
				AgainHashIV:
				$HashIV = ""; 
				$Character = array ("A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M", "N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y" ,"3", "4", "5", "6", "7", "8", "9");

				for ($i = 0; $i <= 25; $i++) {
					$RandChar = "";
					$RandChar = $Character[rand(0, Count($Character))];
					$HashIV .= $RandChar;
				}

				$sql = "SELECT * FROM $this->DB_Table WHERE HashKey = '".$HashIV."' OR HashIV = '".$HashIV."'";
				CDbShell::query($sql);
				if (CDbShell::num_rows() > 0) {
					goto AgainHashIV;
				}
				$ValidateKey = "";
				for ($i = 0; $i <= 10; $i++) {
					$RandChar = "";
					$RandChar = $Character[rand(0, Count($Character))];
					$ValidateKey .= $RandChar;
				}
		
				$_SMSCheck = (isset($_POST['SMSCheck']) ? $_POST['SMSCheck'] : 0);
				
				$field = array("Name", "PublicName","Industry","OtherIndustry","FirmCode","TaxID","Invoice","OtherInvoice","ResponsiblePerson","RegisterAddress","BusinessAddress","Address","TEL",
								"FAX", "ContractDate","BusinessWindow","BusinessTEL","BusinessMobile","BusinessMail","Business1","Business2","Web","WebUrl", 
								"Bank", "Branch","BankAccount", "AccountName", "Bank2", "Branch2","BankAccount2", "AccountName2", "Bank3", "Branch3","BankAccount3", "AccountName3",
								"Bank4", "Branch4","BankAccount4", "AccountName4", "Bank5", "Branch5","BankAccount5", "AccountName5", "HashKey", "HashIV", "ValidateKey", "InsteadFee", "Country", "SMSCheck");
				$value = array($_POST['Name'], $_POST['PublicName'], $_POST['Industry'],$_POST['OtherIndustry'], $_POST['FirmCode'], $_POST['TaxID'], $_POST['Invoice'], $_POST['OtherInvoice'], $_POST['ResponsiblePerson'], $_POST['RegisterAddress']
							 , $_POST['BusinessAddress'], $_POST['Address'], $_POST['TEL'], $_POST['FAX'], $_POST["ContractDate"], $_POST['Window'], $_POST['BusinessTEL'], $_POST['BusinessMobile'], $_POST['BusinessMail'], $_POST['Business1']
							 , $_POST['Business2'], $_POST['Web'], $_POST['WebUrl'], $_POST['Bank'], $_POST['Branch'], $_POST['BankAccount'], $_POST['AccountName']
							 , $_POST['Bank2'], $_POST['Branch2'], $_POST['BankAccount2'], $_POST['AccountName2'], $_POST['Bank3'], $_POST['Branch3'], $_POST['BankAccount3'], $_POST['AccountName3']
							 , $_POST['Bank4'], $_POST['Branch4'], $_POST['BankAccount4'], $_POST['AccountName4'], $_POST['Bank4'], $_POST['Branch5'], $_POST['BankAccount5'], $_POST['AccountName5']
							 , $HashKey, $HashIV, $ValidateKey, $_POST["InsteadFee"], $_POST["Country"], $_SMSCheck);
				CDbShell::insert($this->DB_Table, $field, $value);
				CDbShell::DB_close();
				
				JSModule::BoxCloseJSMessage("廠商 新增成功。");
			}else {
				/*$oFCKeditor = new ckeditor();
				$oFCKeditor->BasePath = '../ckeditor/';
				$oFCKeditor->Width = '100%';
				$oFCKeditor->Height = '1000px';
				$oFCKeditor->replace("Detail");*/

				include($this->GetAdmHtmlPath . "AddedNode.html");
			}
		} catch(Exception $e) {
		   	JSModule::ErrorJSMessage($e->getMessage());
		} 
		/*finally {
		   	CDbShell::DB_close();
		}*/
	}
	
	function AddedBranch() {
		
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				self::VerifyData();
				
				$i = 0;
				AgainHashKey:
				$HashKey = ""; 
				$Character = array ("A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M", "N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y" ,"3", "4", "5", "6", "7", "8", "9");

				for ($i = 0; $i <= 25; $i++) {
					$RandChar = "";
					$RandChar = $Character[rand(0, Count($Character))];
					$HashKey .= $RandChar;
				}

				$sql = "SELECT * FROM $this->DB_Table WHERE HashKey = '".$HashKey."' OR HashIV = '".$HashKey."'";
				CDbShell::query($sql);
				if (CDbShell::num_rows() > 0) {
					goto AgainHashKey;
				}
				
				AgainHashIV:
				$HashIV = ""; 
				$Character = array ("A", "B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M", "N", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y" ,"3", "4", "5", "6", "7", "8", "9");

				for ($i = 0; $i <= 25; $i++) {
					$RandChar = "";
					$RandChar = $Character[rand(0, Count($Character))];
					$HashIV .= $RandChar;
				}

				$sql = "SELECT * FROM $this->DB_Table WHERE HashKey = '".$HashIV."' OR HashIV = '".$HashIV."'";
				CDbShell::query($sql);
				if (CDbShell::num_rows() > 0) {
					goto AgainHashIV;
				}
				
				$ValidateKey = "";
				for ($i = 0; $i <= 10; $i++) {
					$RandChar = "";
					$RandChar = $Character[rand(0, Count($Character))];
					$ValidateKey .= $RandChar;
				}
				$_MonthlyFee = (($_POST["Toll"] == "1") ? $_POST['MonthlyFee1'] : (($_POST["Toll"] == "3") ? $_POST['MonthlyFee2'] : $Row["MonthlyFee"]));
				$_SMSCheck = (isset($_POST['SMSCheck']) ? $_POST['SMSCheck'] : 0);
				
				$field = array("ParentSno", "Name", "PublicName","Industry","OtherIndustry","FirmCode","TaxID","Invoice","OtherInvoice","ResponsiblePerson","RegisterAddress",
								"BusinessAddress","Address","TEL","FAX", "ContractDate","BusinessWindow","BusinessTEL","BusinessMobile","BusinessMail","Business1","Business2","Web","WebUrl",
								"Bank", "Branch","BankAccount", "AccountName", "Bank2", "Branch2","BankAccount2", "AccountName2", "Bank3", "Branch3","BankAccount3", "AccountName3",
								"Bank4", "Branch4","BankAccount4", "AccountName4", "Bank5", "Branch5","BankAccount5", "AccountName5", "HashKey", "HashIV", "ValidateKey", "Toll", "MonthlyFee", "InsteadFee", "Country", "Remark", "SMSCheck");
				$value = array($_POST['ParentSno'], $_POST['Name'], $_POST['PublicName'], $_POST['Industry'],$_POST['OtherIndustry'], $_POST['FirmCode'], $_POST['TaxID'], $_POST['Invoice'], $_POST['OtherInvoice'], $_POST['ResponsiblePerson'], $_POST['RegisterAddress']
							 , $_POST['BusinessAddress'], $_POST['Address'], $_POST['TEL'], $_POST['FAX'], $_POST["ContractDate"], $_POST['Window'], $_POST['BusinessTEL'], $_POST['BusinessMobile'], $_POST['BusinessMail'], $_POST['Business1']
							 , $_POST['Business2'], $_POST['Web'], $_POST['WebUrl'], $_POST['Bank'], $_POST['Branch'], $_POST['BankAccount'], $_POST['AccountName']
							 , $_POST['Bank2'], $_POST['Branch2'], $_POST['BankAccount2'], $_POST['AccountName2'], $_POST['Bank3'], $_POST['Branch3'], $_POST['BankAccount3'], $_POST['AccountName3']
							 , $_POST['Bank4'], $_POST['Branch4'], $_POST['BankAccount4'], $_POST['AccountName4'], $_POST['Bank5'], $_POST['Branch5'], $_POST['BankAccount5'], $_POST['AccountName5']
							 , $HashKey, $HashIV, $ValidateKey, $_POST['Toll'], $_MonthlyFee, $_POST["InsteadFee"], $_POST["Country"], $_POST['Remark'], $_SMSCheck);
				CDbShell::insert($this->DB_Table, $field, $value);
				CDbShell::DB_close();
				
				JSModule::BoxCloseJSMessage("廠商分店 新增成功。");
			}else {
				/*$oFCKeditor = new ckeditor();
				$oFCKeditor->BasePath = '../ckeditor/';
				$oFCKeditor->Width = '100%';
				$oFCKeditor->Height = '1000px';
				$oFCKeditor->replace("Detail");*/

				include($this->GetAdmHtmlPath . "AddedBranchNode.html");
			}
		} catch(Exception $e) {
		   	JSModule::ErrorJSMessage($e->getMessage());
		} 
		/*finally {
		   	CDbShell::DB_close();
		}*/
	}
	
	function Modify() {
		
		CDbShell::query("select * from $this->DB_Table where Sno = ". $_GET["Sno"]);
    	$Row = CDbShell::fetch_array();
    	
    	if ($Row["ParentSno"] != 0) {
    		CDbShell::query("select * from $this->DB_Table where Sno = ". $Row["ParentSno"]);
    		$ParentRow = CDbShell::fetch_array();
    	}
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				self::VerifyData();
				$_MonthlyFee = (($_POST["Toll"] == "1") ? $_POST['MonthlyFee1'] : (($_POST["Toll"] == "3") ? $_POST['MonthlyFee2'] : $Row["MonthlyFee"]));
				$_SMSCheck = (isset($_POST['SMSCheck']) ? $_POST['SMSCheck'] : 0);
				$field = array("Name", "PublicName","Industry","OtherIndustry","FirmCode","TaxID","Invoice","OtherInvoice","ResponsiblePerson","RegisterAddress",
								"BusinessAddress","Address","TEL","FAX", "ContractDate","BusinessWindow","BusinessTEL","BusinessMobile","BusinessMail","Business1","Business2","Web","WebUrl", 
								"Bank", "Branch","BankAccount", "AccountName", "Bank2", "Branch2","BankAccount2", "AccountName2", "Bank3", "Branch3","BankAccount3", "AccountName3",
								"Bank4", "Branch4","BankAccount4", "AccountName4", "Bank5", "Branch5","BankAccount5", "AccountName5", "Toll", "MonthlyFee", "InsteadFee", "Country", "Remark", "SMSCheck");
				$value = array($_POST['Name'], $_POST['PublicName'], $_POST['Industry'],$_POST['OtherIndustry'], $_POST['FirmCode'], $_POST['TaxID'], $_POST['Invoice'], $_POST['OtherInvoice'], $_POST['ResponsiblePerson'], $_POST['RegisterAddress']
							 , $_POST['BusinessAddress'], $_POST['Address'], $_POST['TEL'], $_POST['FAX'], $_POST["ContractDate"], $_POST['Window'], $_POST['BusinessTEL'], $_POST['BusinessMobile'], $_POST['BusinessMail'], $_POST['Business1']
							 , $_POST['Business2'], $_POST['Web'], $_POST['WebUrl'], $_POST['Bank'], $_POST['Branch'], $_POST['BankAccount'], $_POST['AccountName']
							 , $_POST['Bank2'], $_POST['Branch2'], $_POST['BankAccount2'], $_POST['AccountName2'], $_POST['Bank3'], $_POST['Branch3'], $_POST['BankAccount3'], $_POST['AccountName3']
							 , $_POST['Bank4'], $_POST['Branch4'], $_POST['BankAccount4'], $_POST['AccountName4'], $_POST['Bank5'], $_POST['Branch5'], $_POST['BankAccount5'], $_POST['AccountName5']
							 , $_POST['Toll'], $_MonthlyFee, $_POST["InsteadFee"], $_POST['Country'], $_POST['Remark'], $_SMSCheck);
				CDbShell::update($this->DB_Table, $field, $value, "Sno = ". $_GET["Sno"]);
				CDbShell::DB_close();
				
				JSModule::BoxCloseJSMessage("廠商 修改成功。");
			}
			else {
				
				$oFCKeditor = new ckeditor();
		        $oFCKeditor->BasePath = '../ckeditor/';
		        $oFCKeditor->Width = '100%';
		        $oFCKeditor->Height = '1000px';
		        $oFCKeditor->replace("Detail");
        
				include($this->GetAdmHtmlPath . "ModifyNode.html");
			}
		} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
		/*finally {
		   CDbShell::DB_close();
		}*/
	}
	
	function Deletion() {
		
		CDbShell::query("delete from $this->DB_Table where Sno = ". $_GET["Sno"]);
		
		JSModule::Message("廠商 刪除成功。", $_SERVER["PHP_SELF"]);
	}
	
	function Detail() {
		try {
			CDbShell::query("select Main.*, admin.Department, admin.Name from $this->DB_Table AS Main left join admin on Main.AdminSno = admin.Sno where Main.Sno = ". $_GET["Sno"]);
	    	$Row = CDbShell::fetch_array(); 
        
			include($this->GetAdmHtmlPath . "DetailNode.html");
		} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
		/*finally {
		   CDbShell::DB_close();
		}*/
	}
	function FirmCommission() {
		CDbShell::query("SELECT PF.Kind, PF.Mode, FC.* FROM paymentflow AS PF INNER JOIN firmcommission AS FC ON PF.Sno = FC.PaymentFlowSno WHERE FC.Enable = 1 AND FC.FirmSno = ".CSession::getVar("FirmSno")." ORDER BY PF.Type, PF.Kind, PF.Sno"); 
    	while ($Row = CDbShell::fetch_array()) {
    		if ($Row["Kind"] == "電信小額" ) $Row["Kind"] = $Row["Mode"];
    		$Html[] = $Row;
    	}
		
		include($this->GetAdmHtmlPath . "FirmCommission.html");
	}
	function SetCommission() {
		CDbShell::query("select * from paymentflow order by Type, Kind, Mode"); 
    	while ($Row = CDbShell::fetch_array()) {
    		$Html[] = $Row;
    	}
    	
    	try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				//echo $_POST["FirmSno"];
				//exit;
				 $AllNull = true;
				foreach ((array)$_POST["FeeRatio"] as $key => $value) {
					//$value as $key2 => $value2;
					foreach ((array)$value as $key2 => $value2){
						if ($value2 != "") {
							$AllNull = false;
							//echo $key2 ."|". $_POST['FixedFee'][$key][$key2] . "|" . $_POST['Explain'][$key][$key2];
							CDbShell::query("SELECT Sno FROM FirmCommission WHERE FirmSno = ". $_POST["FirmSno"] . " AND PaymentFlowSno = ".$key2);
							//echo CDbShell::num_rows();
	    					if (CDbShell::num_rows() != 0) {
	    						$field3 = array("FeeRatio", "FixedFee","MinFee","MaxFee","Closing","Day", "Enable", "Note");
								$value3 = array($value2, $_POST['FixedFee'][$key][$key2], $_POST['MinFee'][$key][$key2], $_POST['MaxFee'][$key][$key2], $_POST['Closing'][$key][$key2], $_POST['Day'][$key][$key2], $_POST['Enable'][$key][$key2], $_POST['Explain'][$key][$key2]);
								CDbShell::update("FirmCommission", $field3, $value3, "FirmSno = ". $_POST["FirmSno"] . " AND PaymentFlowSno = ".$key2);
	    					}else {
	    						$field3 = array("FirmSno", "PaymentFlowSno", "FeeRatio", "FixedFee","MinFee","MaxFee","Closing","Day", "Enable", "Note");
								$value3 = array($_POST["FirmSno"], $key2, $value2, $_POST['FixedFee'][$key][$key2], $_POST['MinFee'][$key][$key2], $_POST['MaxFee'][$key][$key2], $_POST['Closing'][$key][$key2], $_POST['Day'][$key][$key2], $_POST['Enable'][$key][$key2], $_POST['Explain'][$key][$key2]);
								$Row = CDbShell::insert("FirmCommission", $field3, $value3);
								//echo "456". $Row;
	    					}
							
						}
					}
				}
				
				foreach ((array)$_POST["FixedFee"] as $key => $value) {
					//$value as $key2 => $value2;
					foreach ((array)$value as $key2 => $value2){
						if ($value2 != "") {
							$AllNull = false;
							//echo $key2 ."|". $_POST['FixedFee'][$key][$key2] . "|" . $_POST['Explain'][$key][$key2];
							CDbShell::query("SELECT Sno FROM FirmCommission WHERE FirmSno = ". $_POST["FirmSno"] . " AND PaymentFlowSno = ".$key2);
							//echo CDbShell::num_rows();
	    					if (CDbShell::num_rows() != 0) {
	    						$field3 = array("FeeRatio", "FixedFee","MinFee","MaxFee","Closing","Day","Note");
								$value3 = array($_POST['FeeRatio'][$key][$key2], $_POST['FixedFee'][$key][$key2], $_POST['MinFee'][$key][$key2], $_POST['MaxFee'][$key][$key2], $_POST['Closing'][$key][$key2], $_POST['Day'][$key][$key2], $_POST['Explain'][$key][$key2]);
								CDbShell::update("FirmCommission", $field3, $value3, "FirmSno = ". $_POST["FirmSno"] . " AND PaymentFlowSno = ".$key2);
	    					}else {
	    						$field3 = array("FirmSno", "PaymentFlowSno", "FeeRatio", "FixedFee","MinFee","MaxFee","Closing","Day","Note");
								$value3 = array($_POST["FirmSno"], $key2, $_POST['FeeRatio'][$key][$key2], $_POST['FixedFee'][$key][$key2], $_POST['MinFee'][$key][$key2], $_POST['MaxFee'][$key][$key2], $_POST['Closing'][$key][$key2], $_POST['Day'][$key][$key2], $_POST['Explain'][$key][$key2]);
								$Row = CDbShell::insert("FirmCommission", $field3, $value3);
								//echo "456". $Row;
	    					}
							
						}
					}
				}
				
				
				if ($AllNull) {
					throw new exception("請最少輸入一項手續費!");
					exit;
				}
				JSModule::BoxCloseJSMessage("交易手續費 設定成功。", "admin.php?func=Commission");
				exit;
			}
			include($this->GetAdmHtmlPath . "SetCommission.html");
		} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
	}
	
	function SetInterfaced() {
    	
    	try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				if (strlen(trim($_POST["TakeNumberURL"])) < 6) {
					throw new exception("請輸入取號回傳網址!");
				}

				if (strlen(trim($_POST["SuccessURL"])) < 6) {
					throw new exception("請輸入支付成功回傳網址!");
				}
				
				$field = array("AllowIP", "TakeNumberURL", "FailURL", "SuccessURL", "VirtualATMDisburse");
				$value = array($_POST['AllowIP'], $_POST['TakeNumberURL'], $_POST['FailURL'], $_POST['SuccessURL'], $_POST['VirtualATMDisburse']);
				CDbShell::update($this->DB_Table, $field, $value, "Sno = ". $_POST["FirmSno"]);
				CDbShell::DB_close();
				
				JSModule::BoxCloseJSMessage("系統介接 設定成功。", "admin.php?attr=interfaced");
				exit;
			}else {
				CDbShell::query("select * from Firm WHERE Sno = ".$_GET["Sno"].""); 
    			$Row = CDbShell::fetch_array();
    			
    			$card = Receive_URL."card.php?Sno=".Cryptographic::encrypt($Row["Sno"]);
			}
		} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		   exit;
		} 
    	include($this->GetAdmHtmlPath . "SetInterfaced.html");
	
	}
	
	function FirmSetInterfaced() {
    	
    	try {
			
			if (strlen(trim($_POST["TakeNumberURL"])) < 6) {
				throw new exception("請輸入取號回傳網址!");
			}
			
			if (strlen(trim($_POST["SuccessURL"])) < 6) {
				throw new exception("請輸入一般商品履約完成回傳網址!");
			}
			
			$field = array("AllowIP", "TakeNumberURL", "FailURL", "SuccessURL");
			$value = array($_POST['AllowIP'], $_POST['TakeNumberURL'], $_POST['FailURL'], $_POST['SuccessURL']);
			CDbShell::update($this->DB_Table, $field, $value, "Sno = ". CSession::getVar("FirmSno"));
			CDbShell::DB_close();
			
			JSModule::BoxCloseJSMessage("系統介接 設定成功。", "admin.php?attr=interfaced");
			exit;
		} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		   exit;
		} 
	
	}
	
	function Cashier() {
    	$AdminLevel = CSession::getVar("AdminLevel");
        $Boss 		= CSession::getVar("Boss");
            	
    	$nowitem = $_GET["ipage"] * $this->PageItems;
		
		//$Pages = self::AllPages();
		$PageBar = self::showPageBar($_GET["ipage"], $Pages, "");
		
		$Keyword = (strlen(trim($_POST["Keyword"])) > 0) ? $_POST["Keyword"] : $_GET["Keyword"];
		if (strlen(trim($Keyword)) > 0 ) {
			$this->SearchKeyword = " WHERE (Name like '%".$Keyword."%' or ResponsiblePerson like '%".$Keyword."%')";
		}
		
		if ($AdminLevel == 3) {
			if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
			else $this->SearchKeyword .= " AND ";
			
			$_ChildSno .= CSession::getVar("FirmSno")."";
			$_ChildSno .= ",";
			
			CDbShell::query("SELECT * FROM ".$this->DB_Table." WHERE ParentSno = ".CSession::getVar("FirmSno"));
			if (CDbShell::num_rows() > 0) {
	    		while ($Row = CDbShell::fetch_array()) { 
	    			$_ChildSno .= $Row["Sno"];
	    			$_ChildSno .= ",";
	    		}
	    	}
	    	
			$_ChildSno = substr_replace($_ChildSno,'',-1);
			$this->SearchKeyword .= " Sno IN (".$_ChildSno.")";
		}
		
		CDbShell::query("SELECT * FROM $this->DB_Table ". $this->SearchKeyword ." ORDER BY Sno LIMIT ".$nowitem."," . $this->PageItems); 
		//echo "SELECT * FROM $this->DB_Table ". $this->SearchKeyword ." ORDER BY Sno LIMIT ".$nowitem."," . $this->PageItems;
		while ($Row = CDbShell::fetch_array()) {
			$Row["background"] = "";
			$Row["Url"] = Receive_URL."card.php?Sno=".Cryptographic::encrypt($Row["Sno"]);
			$Html[] = $Row;
		}
		include($this->GetAdmHtmlPath . "Cashier.html");
	}
	
	function RefusalIP() {
		if (CSession::getVar("AdminLevel") >= 3) {
			
			try {
				if ($_SERVER['REQUEST_METHOD'] == 'POST') {
					CDbShell::query("UPDATE Firm SET RefusalIP = '".$_POST["RefusalIP"]."' WHERE Sno = ".CSession::getVar("FirmSno").""); 
					
					JSModule::JSMessage("拒絕交易IP設定成功。", $_SERVER["PHP_SELF"]."?func=RefusalIP");
				}else {
					CDbShell::query("SELECT * FROM Firm WHERE Sno = ".CSession::getVar("FirmSno").""); 
		    		$Row = CDbShell::fetch_array();
		    		
					include($this->GetAdmHtmlPath . "FirmRefusalIP.html");
				}
			} catch(Exception $e) {
			   JSModule::ErrorJSMessage($e->getMessage());
			   exit;
			} 
		}else if ($_GET["func"] == "SetRefusalIP") {
			try {
				if ($_SERVER['REQUEST_METHOD'] == 'POST') {
					CDbShell::query("UPDATE Firm SET RefusalIP = '".$_POST["RefusalIP"]."' WHERE Sno = ".$_POST["Sno"].""); 
					
					JSModule::JSMessage("拒絕交易IP設定成功。", $_SERVER["PHP_SELF"]."?func=RefusalIP");
				}else {
					CDbShell::query("SELECT * FROM Firm WHERE Sno = ".$_GET["Sno"].""); 
		    		$Row = CDbShell::fetch_array();
		    		
					include($this->GetAdmHtmlPath . "SetRefusalIP.html");
				}
			} catch(Exception $e) {
			   JSModule::ErrorJSMessage($e->getMessage());
			   exit;
			} 
		}else {
			$AdminLevel = CSession::getVar("AdminLevel");
        	$Boss 		= CSession::getVar("Boss");
			$nowitem = $_GET["ipage"] * $this->PageItems;
	    	$PageBar = self::showPageBar($_GET["ipage"], $Pages, "");
	    	
	    	$Keyword = (strlen(trim($_POST["Keyword"])) > 0) ? $_POST["Keyword"] : $_GET["Keyword"];
	    	if (strlen(trim($Keyword)) > 0 ) {
	    		$this->SearchKeyword = " WHERE (Name like '%".$Keyword."%' or ResponsiblePerson like '%".$Keyword."%')";
	    	}
	    	
			CDbShell::query("SELECT * FROM $this->DB_Table ". $this->SearchKeyword ." ORDER BY Sno LIMIT ".$nowitem."," . $this->PageItems); 
	    	//echo "SELECT * FROM $this->DB_Table ". $this->SearchKeyword ." ORDER BY Sno LIMIT ".$nowitem."," . $this->PageItems;
	    	while ($Row = CDbShell::fetch_array()) {
	    		if (date("Y-m-d", strtotime(date('Y-m-d'). " +30 day")) >= $Row["ContractDate"]) $Row["background"] = "style='background-color:#FFEEFA'";
	    		else $Row["background"] = "";
	    		$Row["DelLink"] = $_SERVER["PHP_SELF"]."?func=Deletion&Sno=".$Row["Sno"];
	    		$Html[] = $Row;
	    	}
	    	include($this->GetAdmHtmlPath . "Manage.html");
		}
	}
	
	function WithdrawPointsManage() {
		$AdminLevel = CSession::getVar("AdminLevel");
		
		if (CSession::getVar("Boss") != 1) {
			header("location:../admin/admin.php");
			exit;
		}
		$_DefaultPoints = null;
		CDbShell::query("select * from Firm WHERE Country = 'CH' ORDER BY Sno"); 
    	while ($Row = CDbShell::fetch_array()) {
    		if (is_null($_DefaultPoints)) $_DefaultPoints = $Row["Points"];
    		$CHRow[] = $Row;
    	}
    	
    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    		
			CDbShell::query("select * from Firm WHERE Sno = ".$_POST["FirmSno"].""); 
	    	$Row = CDbShell::fetch_array();
    	
    		if ($_POST["func"] == "GetPoints") {
    			switch ($_POST["ProductType"]) {
    				case "WEIXIN":
    					echo number_format($Row["Points"], 2);
    					break;
    				case "B2CPAY":
    					echo number_format($Row["OBPoints"], 2);
    					break;
    				case "UNIONPAY":
    					echo number_format($Row["UPoints"], 2);
    					break;
    				case "QUICKPAY":
    					echo number_format($Row["QPoints"], 2);
    					break;
    				case "QQ":
    					echo number_format($Row["QQPoints"], 2);
    					break;
    			}
    			exit;
    		}
    		if (strlen(trim($_POST["BankAccount"])) <= 4) {
    			JSModule::ErrorJSMessage("请输入银行卡号！");
    			exit;
    		}
    		if (strlen(trim($_POST["AccountName"])) <= 1) {
    			JSModule::ErrorJSMessage("请输入银行账户名！");
    			exit;
    		}
    		if (strlen(trim($_POST["BankBranch"])) <= 1) {
    			JSModule::ErrorJSMessage("请输入支行名称！");
    			exit;
    		}
    		if (!is_numeric($_POST["Withdraw"])) {
    			JSModule::ErrorJSMessage("请输入正确的提领金额！");
    			exit;
    		}
    		if (intval($_POST["Withdraw"]) <= (floatval($Row["InsteadFee"]) + 10)) {
    			JSModule::ErrorJSMessage("提领金额最少超过".(floatval($Row["InsteadFee"]) + 10)."点！");
    			exit;
    		}
    		
    		switch ($_POST["ProductType"]) {
				case "WEIXIN":
					if (intval($_POST["Withdraw"]) > intval($Row["Points"])) {
		    			JSModule::ErrorJSMessage("提领金额不足！");
		    			exit;
		    		}
		    		$_PaymentType = 8;
		    		$_BeforePoints = $Row["Points"];
		    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$_POST["FirmSno"]." AND PF.Kind = '微信' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					break;
				case "B2CPAY":
					if (intval($_POST["Withdraw"]) > intval($Row["OBPoints"])) {
		    			JSModule::ErrorJSMessage("提领金额不足！");
		    			exit;
		    		}
		    		$_PaymentType = 9;
		    		$_BeforePoints = $Row["OBPoints"];
		    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$_POST["FirmSno"]." AND PF.Kind = '網銀' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					break;
				case "UNIONPAY":
					if (intval($_POST["Withdraw"]) > intval($Row["UPoints"])) {
		    			JSModule::ErrorJSMessage("提领金额不足！");
		    			exit;
		    		}
		    		$_PaymentType = 14;
		    		$_BeforePoints = $Row["UPoints"];
		    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$_POST["FirmSno"]." AND PF.Kind = '銀聯掃瞄' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					break;
				case "QUICKPAY":
					if (intval($_POST["Withdraw"]) > intval($Row["QPoints"])) {
		    			JSModule::ErrorJSMessage("提领金额不足！");
		    			exit;
		    		}
		    		$_PaymentType = 15;
		    		$_BeforePoints = $Row["QPoints"];
		    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$_POST["FirmSno"]." AND PF.Kind = '快捷支付' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					break;
				case "QQ":
					if (intval($_POST["Withdraw"]) > intval($Row["QQPoints"])) {
		    			JSModule::ErrorJSMessage("提领金额不足！");
		    			exit;
		    		}
		    		$_PaymentType = 16;
		    		$_BeforePoints = $Row["QQPoints"];
		    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$_POST["FirmSno"]." AND PF.Kind = 'QQ' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					break;
			}
    		
    		/*if(strcmp($_POST["Password"], CSession::getVar("admin_password")) != 0){ 
    			JSModule::ErrorJSMessage("密码不正确！");
    			exit;
    		}*/
    		
    		$TNumber = "";
    		Again:
    			$TNumber = date("ymd").str_pad(rand(0,9999), 4, '0', STR_PAD_LEFT);
    			CDbShell::query("select * from withdraw WHERE TNumber = ".$TNumber.""); 
    			if (CDbShell::num_rows() > 0) {
    				goto Again;
    			}
    		//echo "alert('".$TNumber."')";
    		//exit;
    		$_ActualPoints = floatval($_POST["Withdraw"]) - floatval($Row["InsteadFee"]);
    		switch ($_POST["ProductType"]) {
				case "WEIXIN":
	    			CDbShell::query("UPDATE $this->DB_Table SET Points = Points - ".$_POST["Withdraw"]." WHERE Sno = ".$_POST["FirmSno"].""); 
	    			break;
	    		case "B2CPAY":
					CDbShell::query("UPDATE $this->DB_Table SET OBPoints = OBPoints - ".$_POST["Withdraw"]." WHERE Sno = ".$_POST["FirmSno"].""); 
	    			
					break;
				case "UNIONPAY":
					CDbShell::query("UPDATE $this->DB_Table SET UPoints = UPoints - ".$_POST["Withdraw"]." WHERE Sno = ".$_POST["FirmSno"].""); 
	    			
					break;
				case "QUICKPAY":
					CDbShell::query("UPDATE $this->DB_Table SET QPoints = QPoints - ".$_POST["Withdraw"]." WHERE Sno = ".$_POST["FirmSno"].""); 
	    			
					break;
				case "QQ":
					CDbShell::query("UPDATE $this->DB_Table SET QQPoints = QQPoints - ".$_POST["Withdraw"]." WHERE Sno = ".$_POST["FirmSno"].""); 
	    			
					break;
	    	}
    		$field = array("TNumber", "FirmSno", "BankAccount", "AccountName", "BankCardType", "Points", "ActualPoints", "InsteadFee", "BankCategory", "TransactionType", "ProductType", "Province", "City", "BankName", "BankBranch", "IDNumber", "Mobile");
    		$value = array($TNumber, $_POST["FirmSno"], $_POST["BankAccount"], $_POST["AccountName"], $_POST["BankCardType"], $_POST["Withdraw"], $_ActualPoints, intval($Row["InsteadFee"]), $_POST["BankCategory"], "T0", $_POST["ProductType"], "北京市", "北京市", "工商银行槐树岭支行", $_POST["BankBranch"], "xxxxxx", "17341568745");
			CDbShell::insert("withdraw", $field, $value);
			
    		$field = array("FirmSno", "PaymentType", "BeforePoints", "ChangePoints","AfterPoints","ChangeEvent","Note");
    		$value = array($_POST["FirmSno"], $_PaymentType, $_BeforePoints, (intval($_POST["Withdraw"]) * -1), ($_BeforePoints - $_POST["Withdraw"]), 2, "提領金額 交易編號「".$TNumber."」");
			CDbShell::insert("pointchangerecord", $field, $value);
			
			$NotifyUrl = Receive_URL."PrePayment_JIN.php";
			if (is_numeric(mb_strpos($FCRow["Mode"], "金順", "0", "UTF-8"))) {
				$XML = '<?xml version="1.0" encoding="utf-8" standalone="no"?>
						<message 
						application="ReceivePay" 
						version="1.0.1"
						merchantId="1000058"
						tranId="'.$TNumber.'"
						timestamp="'.$timestamp.'"
						receivePayNotifyUrl="'.$NotifyUrl.'" 
						receivePayType="1" 
						accountProp="0" 
						accNo="'.$_POST["BankAccount"].'" 
						accName="'.$_POST["AccountName"].'" 
						credentialType="01"
						credentialNo="230224195632121122"
						tel="13120033859"
						amount="'.number_format($_ActualPoints, 2, "" ,"").'" />';
				$strMD5 =  MD5($XML,true);	
		
				$certs = array();
				openssl_pkcs12_read(file_get_contents(dirname(dirname(__FILE__))."\merchant_cert.pfx"), $certs,"7533967"); //其中password为你的证书密码
				//print_r($certs);
				//exit;
				$signature = '';  
				openssl_sign($strMD5, $signature, $certs['pkey']);
				$strsign =  base64_encode($signature);
				
				$base64_src = base64_encode($XML);
				$msg = $base64_src."|".$strsign;
				
				//echo $msg;
				
				$result = self::SockPost2(jinpay_URL, $msg);
				$tmp = explode("|", $result);
				$resp_xml = base64_decode($tmp[0]);
				$resp_sign = $tmp[1];
				//echo ">>".$resp_xml;
				//exit;
				$xml = simplexml_load_string($resp_xml);
				
				$respCode = (string)$xml->attributes()->respCode;
				$respDesc = (string)$xml->attributes()->respDesc;
				$tranId = (string)$xml->attributes()->tranId;
				
				$fp = fopen('../Log/ProxyPay/ProxyPay_LOG_'.date("YmdHi").'.txt', 'a');
				fwrite($fp, " ---------------- 代付通知結果 ---------------- \n\r");
				fwrite($fp, "\$resp_xml =>".$resp_xml."\n\r");
				fwrite($fp, "\$respCode =>".$respCode."\n\r");
				fwrite($fp, "\$respDesc =>".$respDesc."\n\r");
				fwrite($fp, "\$tranId =>".$tranId."\n\r");
				fclose($fp);
				
				if ($respCode == "000") {
					CDbShell::query("UPDATE withdraw SET Status = 1 WHERE TNumber = '".$tranId."'"); 
					//JSModule::JsMessage("提出 提领金额 成功，请等待管理员拨款处理！", "?func=WithdrawPoints");    		
				}
			}
			
			/*if (is_numeric(mb_strpos($FCRow["Mode"], "太陽城", "0", "UTF-8"))) {
				switch ($_POST["ProductType"]) {
					case "WEIXIN":
						$payKey = array("payKey"=>tfhmye_key);
						break;
					case "B2CPAY":
						if (is_numeric(mb_strpos($FCRow["Mode"], "-1", "0", "UTF-8"))) {
							$payKey = array("payKey"=>tfhmye_key);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-2", "0", "UTF-8"))) {
							$payKey = array("payKey"=>tfhmye_key2);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-3", "0", "UTF-8"))) {
							$payKey = array("payKey"=>tfhmye_key3);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-4", "0", "UTF-8"))) {
							$payKey = array("payKey"=>tfhmye_key4);
						}
						break;
					case "UNIONPAY":
						$payKey = array("payKey"=>tfhmye_key);
						break;
					case "QUICKPAY":
						$payKey = array("payKey"=>tfhmye_key);
						break;
					case "QQ":
						$payKey = array("payKey"=>tfhmye_key);
						break;
				}
    			$parameter = array(
					"outTradeNo"=>$TNumber,
			        "orderPrice"=>number_format($_ActualPoints, 2, "." ,""), 
			        "proxyType"=>"T0",
			        "productType"=>$_POST["ProductType"],
			        "bankAccountType"=>"PRIVATE_DEBIT_ACCOUNT",
			        "phoneNo"=>"17341568745",
			        "receiverName"=>$_POST["AccountName"],
			        "certType"=>"IDENTITY",
			        "receiverAccountNo"=>$_POST["BankAccount"], 
			        "bankBranchNo"=>"1",
			        "bankCode"=>"ICBC",
			        "bankBranchName"=>"工商银行槐树岭支行",
			        "province"=>"北京市",
			        "city"=>"北京市",
			        "notifyUrl"=>Receive_URL."ReplacePayout_tfhmye.php"
				);
				$parameter = array_merge($payKey , $parameter);
				
				switch ($_POST["ProductType"]) {
					case "WEIXIN":
						$parameter["sign"] = self::md5Sign($parameter, tfhmye_password);
						break;
					case "B2CPAY":
						if (is_numeric(mb_strpos($FCRow["Mode"], "-1", "0", "UTF-8"))) {
							$parameter["sign"] = self::md5Sign($parameter, tfhmye_password);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-2", "0", "UTF-8"))) {
							$parameter["sign"] = self::md5Sign($parameter, tfhmye_password2);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-3", "0", "UTF-8"))) {
							$parameter["sign"] = self::md5Sign($parameter, tfhmye_password3);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-4", "0", "UTF-8"))) {
							$parameter["sign"] = self::md5Sign($parameter, tfhmye_password4);
						}
						break;
					case "UNIONPAY":
						$parameter["sign"] = self::md5Sign($parameter, tfhmye_password);
						break;
					case "QUICKPAY":
						$parameter["sign"] = self::md5Sign($parameter, tfhmye_password);
						break;
					case "QQ":
						$parameter["sign"] = self::md5Sign($parameter, tfhmye_password);
						break;
				}
				$fp = fopen('../Log/ProxyPay/ProxyPay_LOG_'.date("YmdHi").'.txt', 'a');
				fwrite($fp, " ---------------- POST Parameter---------------- \n\r");
				fwrite($fp, "POST Parameter =>".json_encode($parameter)."\n\r");
				fclose($fp);
				
				$options = array(
				    'http' => array(
				        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				        'method'  => 'POST',
				        'content' => http_build_query($parameter)
				    )
				);

				$context  = stream_context_create($options);
				$string = file_get_contents(tfhmye_ProxyPay_url, false, $context);
				
				$response = json_decode($string);
				$resultCode = $response->resultCode;
				$outTradeNo = $response->outTradeNo;
				$errMsg = $response->errMsg;
				if ($resultCode == "0000") {
					CDbShell::query("UPDATE withdraw SET Status = 1 WHERE TNumber = '".$outTradeNo."'"); 
				}elseif ($resultCode == "9996") {
					CDbShell::query("UPDATE withdraw SET Status = 2 WHERE TNumber = '".$outTradeNo."'");
				}
				
				$fp = fopen('../Log/ProxyPay/ProxyPay_LOG_'.date("YmdHi").'.txt', 'a');
				fwrite($fp, " ---------------- 代付通知結果 ---------------- \n\r");
				fwrite($fp, "\$string =>".$string."\n\r");
				fwrite($fp, "\$outTradeNo =>".$outTradeNo."\n\r");
				fwrite($fp, "\$resultCode =>".$resultCode."\n\r");
				fwrite($fp, "\$errMsg =>".$errMsg."\n\r");
				fclose($fp);
    		}*/
			
			/*CDbShell::query("SELECT Switch FROM smsswitch"); 
    		$SMSRow = CDbShell::fetch_array();
    		if ($SMSRow["Switch"] == "1") {
				$_Parameter = "username=53232789&password=0987409408";
				$_Parameter .= "&dstaddr=".SMS_Mobile;
				//$_Parameter .= "&dlvtime=".Date('YmdHis', strtotime(date('Y-m-d H:i:s') ." +15 second"))."&vldtime=".Date('YmdHis', strtotime(date('Y-m-d H:i:s') ." +15 second"));
				$_Parameter .= "&smbody=".iconv("UTF-8","big5","客戶目前有給付的需求");
				//echo $_Parameter;
				//exit;
				
				//$ReturnStr = self::SockPost('http://smexpress.mitake.com.tw:9600/SmQueryGet.asp?username=0987409408&password=53232789',"");
				$ReturnStr = self::SockPost('http://smexpress.mitake.com.tw:9600/SmSendGet.asp', $_Parameter);
				//echo "ReturnStr =>".iconv("big5","UTF-8", $ReturnStr);
				//exit;
			}*/
			JSModule::JsMessage("提出 提领金额 成功，请等待管理员拨款处理！", "?func=WithdrawPointsManage");
    		exit;
				
    	}
    		
		include($this->GetAdmHtmlPath . "WithdrawPointsManage.html");
	}
	
	function WithdrawPointsTWManage() {
		$AdminLevel = CSession::getVar("AdminLevel");
		
		if (CSession::getVar("Boss") != 1) {
			header("location:../admin/admin.php");
			exit;
		}
		$_DefaultPoints = null;
		CDbShell::query("select * from Firm WHERE Country = 'TW' ORDER BY Sno"); 
    	while ($Row = CDbShell::fetch_array()) {
    		if (is_null($_DefaultPoints)) $_DefaultPoints = $Row["Points"];
    		$CHRow[] = $Row;
    	}
    	
    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    		
			CDbShell::query("select * from Firm WHERE Sno = ".$_POST["FirmSno"].""); 
	    	$Row = CDbShell::fetch_array();
    	
    		if ($_POST["func"] == "GetPoints") {
    			echo number_format($Row["Points"], 2);
    			exit;
    		}
    		if (strlen(trim($_POST["BankAccount"])) <= 4) {
    			JSModule::ErrorJSMessage("請輸入銀行帳號！");
    			exit;
    		}
    		if (strlen(trim($_POST["AccountName"])) <= 1) {
    			JSModule::ErrorJSMessage("請輸入銀行帳戶名！");
    			exit;
    		}
    		if (strlen(trim($_POST["BankBranch"])) <= 1) {
    			JSModule::ErrorJSMessage("請輸入分行名稱！");
    			exit;
    		}
    		if (!is_numeric($_POST["Withdraw"])) {
    			JSModule::ErrorJSMessage("請輸入正確的提領金額！");
    			exit;
    		}
    		if (intval($_POST["Withdraw"]) <= (floatval($Row["InsteadFee"]) + 10)) {
    			JSModule::ErrorJSMessage("提領金額最少超過".(floatval($Row["InsteadFee"]) + 10)."點！");
    			exit;
    		}
    		
    		if (intval($_POST["Withdraw"]) > intval($Row["Points"])) {
    			JSModule::ErrorJSMessage("提領金額不足！");
    			exit;
    		}
    		$_PaymentType = 0;
    		$_BeforePoints = $Row["Points"];
    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$_POST["FirmSno"]." AND PF.Kind = '微信' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
			$FCRow = CDbShell::fetch_array();
			
    		$TNumber = "";
    		Again:
    			$TNumber = date("ymd").str_pad(rand(0,9999), 4, '0', STR_PAD_LEFT);
    			CDbShell::query("select * from withdraw WHERE TNumber = ".$TNumber.""); 
    			if (CDbShell::num_rows() > 0) {
    				goto Again;
    			}
    		//echo "alert('".$TNumber."')";
    		//exit;
    		$_ActualPoints = floatval($_POST["Withdraw"]) - floatval($Row["InsteadFee"]);
    		CDbShell::query("UPDATE $this->DB_Table SET Points = Points - ".$_POST["Withdraw"]." WHERE Sno = ".$_POST["FirmSno"].""); 
	    	
    		$field = array("TNumber", "FirmSno", "BankAccount", "AccountName", "BankCardType", "Points", "ActualPoints", "InsteadFee", "BankCategory", "TransactionType", "ProductType", "Province", "City", "BankName", "BankBranch", "IDNumber", "Mobile");
    		$value = array($TNumber, $_POST["FirmSno"], $_POST["BankAccount"], $_POST["AccountName"], $_POST["BankCardType"], $_POST["Withdraw"], $_ActualPoints, intval($Row["InsteadFee"]), $_POST["BankCategory"], "T0", " ", " ", " ", " ", $_POST["BankBranch"], "xxxxxx", "17341568745");
			CDbShell::insert("withdraw", $field, $value);
			
    		$field = array("FirmSno", "PaymentType", "BeforePoints", "ChangePoints","AfterPoints","ChangeEvent","Note");
    		$value = array($_POST["FirmSno"], $_PaymentType, $_BeforePoints, (intval($_POST["Withdraw"]) * -1), ($_BeforePoints - $_POST["Withdraw"]), 2, "提領金額 交易編號「".$TNumber."」");
			CDbShell::insert("pointchangerecord", $field, $value);
			
			/*CDbShell::query("SELECT Switch FROM smsswitch"); 
    		$SMSRow = CDbShell::fetch_array();
    		if ($SMSRow["Switch"] == "1") {
				$_Parameter = "username=53232789&password=0987409408";
				$_Parameter .= "&dstaddr=".SMS_Mobile;
				//$_Parameter .= "&dlvtime=".Date('YmdHis', strtotime(date('Y-m-d H:i:s') ." +15 second"))."&vldtime=".Date('YmdHis', strtotime(date('Y-m-d H:i:s') ." +15 second"));
				$_Parameter .= "&smbody=".iconv("UTF-8","big5","客戶目前有給付的需求");
				//echo $_Parameter;
				//exit;
				
				//$ReturnStr = self::SockPost('http://smexpress.mitake.com.tw:9600/SmQueryGet.asp?username=0987409408&password=53232789',"");
				$ReturnStr = self::SockPost('http://smexpress.mitake.com.tw:9600/SmSendGet.asp', $_Parameter);
				//echo "ReturnStr =>".iconv("big5","UTF-8", $ReturnStr);
				//exit;
			}*/
			JSModule::JsMessage("提出 提領金額 成功，請等待管理員撥款處理！", "?func=WithdrawPointsTWManage");
    		exit;
				
    	}
    		
		include($this->GetAdmHtmlPath . "WithdrawPointsTWManage.html");
	}
	
	function WithdrawPoints() {
		CDbShell::query("select * from Firm WHERE Sno = ".CSession::getVar("FirmSno").""); 
    	$Row = CDbShell::fetch_array();
    	
    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    		if ($_POST["func"] == "GetPoints") {
    			switch ($_POST["ProductType"]) {
    				case "WEIXIN":
    					echo number_format($Row["Points"], 2);
    					break;
    				case "B2CPAY":
    					echo number_format($Row["OBPoints"], 2);
    					break;
    				case "UNIONPAY":
    					echo number_format($Row["UPoints"], 2);
    					break;
    				case "QUICKPAY":
    					echo number_format($Row["QPoints"], 2);
    					break;
    				case "QQ":
    					echo number_format($Row["QQPoints"], 2);
    					break;
    			}
    			exit;
    		}
    		if (strlen(trim($_POST["BankAccount"])) <= 4) {
    			JSModule::ErrorJSMessage("请输入银行卡号！");
    			exit;
    		}
    		if (strlen(trim($_POST["AccountName"])) <= 1) {
    			JSModule::ErrorJSMessage("请输入银行账户名！");
    			exit;
    		}
    		if (strlen(trim($_POST["BankBranch"])) <= 1) {
    			JSModule::ErrorJSMessage("请输入支行名称！");
    			exit;
    		}
    		if (!is_numeric($_POST["Withdraw"])) {
    			JSModule::ErrorJSMessage("请输入正确的提领金额！");
    			exit;
    		}
    		if (intval($_POST["Withdraw"]) <= (floatval($Row["InsteadFee"]) + 10)) {
    			JSModule::ErrorJSMessage("提领金额最少超过".(floatval($Row["InsteadFee"]) + 10)."点！");
    			exit;
    		}
    		
    		switch ($_POST["ProductType"]) {
				case "WEIXIN":
					if (intval($_POST["Withdraw"]) > intval($Row["Points"])) {
		    			JSModule::ErrorJSMessage("提领金额不足！");
		    			exit;
		    		}
		    		$_PaymentType = 8;
		    		$_BeforePoints = $Row["Points"];
		    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".CSession::getVar("FirmSno")." AND PF.Kind = '微信' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					break;
				case "B2CPAY":
					if (intval($_POST["Withdraw"]) > intval($Row["OBPoints"])) {
		    			JSModule::ErrorJSMessage("提领金额不足！");
		    			exit;
		    		}
		    		$_PaymentType = 9;
		    		$_BeforePoints = $Row["OBPoints"];
		    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".CSession::getVar("FirmSno")." AND PF.Kind = '網銀' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					break;
				case "UNIONPAY":
					if (intval($_POST["Withdraw"]) > intval($Row["UPoints"])) {
		    			JSModule::ErrorJSMessage("提领金额不足！");
		    			exit;
		    		}
		    		$_PaymentType = 14;
		    		$_BeforePoints = $Row["UPoints"];
		    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".CSession::getVar("FirmSno")." AND PF.Kind = '銀聯掃瞄' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					break;
				case "QUICKPAY":
					if (intval($_POST["Withdraw"]) > intval($Row["QPoints"])) {
		    			JSModule::ErrorJSMessage("提领金额不足！");
		    			exit;
		    		}
		    		$_PaymentType = 15;
		    		$_BeforePoints = $Row["QPoints"];
		    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".CSession::getVar("FirmSno")." AND PF.Kind = '快捷支付' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					break;
				case "QQ":
					if (intval($_POST["Withdraw"]) > intval($Row["QQPoints"])) {
		    			JSModule::ErrorJSMessage("提领金额不足！");
		    			exit;
		    		}
		    		$_PaymentType = 16;
		    		$_BeforePoints = $Row["QQPoints"];
		    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".CSession::getVar("FirmSno")." AND PF.Kind = 'QQ' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					break;
			}
    		
    		if(strcmp($_POST["Password"], CSession::getVar("admin_password")) != 0){ 
    			JSModule::ErrorJSMessage("密码不正确！");
    			exit;
    		}
    		
    		$TNumber = "";
    		Again:
    			$TNumber = date("ymd").str_pad(rand(0,9999), 4, '0', STR_PAD_LEFT);
    			CDbShell::query("select * from withdraw WHERE TNumber = ".$TNumber.""); 
    			if (CDbShell::num_rows() > 0) {
    				goto Again;
    			}
    		//echo "alert('".$TNumber."')";
    		//exit;
    		$_ActualPoints = floatval($_POST["Withdraw"]) - floatval($Row["InsteadFee"]);
    		switch ($_POST["ProductType"]) {
				case "WEIXIN":
	    			CDbShell::query("UPDATE $this->DB_Table SET Points = Points - ".$_POST["Withdraw"]." WHERE Sno = ".CSession::getVar("FirmSno").""); 
	    			break;
	    		case "B2CPAY":
					CDbShell::query("UPDATE $this->DB_Table SET OBPoints = OBPoints - ".$_POST["Withdraw"]." WHERE Sno = ".CSession::getVar("FirmSno").""); 
	    			
					break;
				case "UNIONPAY":
					CDbShell::query("UPDATE $this->DB_Table SET UPoints = UPoints - ".$_POST["Withdraw"]." WHERE Sno = ".CSession::getVar("FirmSno").""); 
	    			
					break;
				case "QUICKPAY":
					CDbShell::query("UPDATE $this->DB_Table SET QPoints = QPoints - ".$_POST["Withdraw"]." WHERE Sno = ".CSession::getVar("FirmSno").""); 
	    			
					break;
				case "QQ":
					CDbShell::query("UPDATE $this->DB_Table SET QQPoints = QQPoints - ".$_POST["Withdraw"]." WHERE Sno = ".CSession::getVar("FirmSno").""); 
	    			
					break;
	    	}
    		$field = array("TNumber", "FirmSno", "BankAccount", "AccountName", "BankCardType", "Points", "ActualPoints", "InsteadFee", "BankCategory", "TransactionType", "ProductType", "Province", "City", "BankName", "BankBranch", "IDNumber", "Mobile");
    		$value = array($TNumber, CSession::getVar("FirmSno"), $_POST["BankAccount"], $_POST["AccountName"], $_POST["BankCardType"], $_POST["Withdraw"], $_ActualPoints, intval($Row["InsteadFee"]), $_POST["BankCategory"], "T0", $_POST["ProductType"], "北京市", "北京市", "工商银行槐树岭支行", $_POST["BankBranch"], "xxxxxx", "17341568745");
			CDbShell::insert("withdraw", $field, $value);
			
    		$field = array("FirmSno", "PaymentType", "BeforePoints", "ChangePoints","AfterPoints","ChangeEvent","Note");
    		$value = array(CSession::getVar("FirmSno"), $_PaymentType, $_BeforePoints, (intval($_POST["Withdraw"]) * -1), ($_BeforePoints - $_POST["Withdraw"]), 2, "提領金額 交易編號「".$TNumber."」");
			CDbShell::insert("pointchangerecord", $field, $value);
			
			$NotifyUrl = Receive_URL."PrePayment_JIN.php";
			if (is_numeric(mb_strpos($FCRow["Mode"], "金順", "0", "UTF-8"))) {
				$XML = '<?xml version="1.0" encoding="utf-8" standalone="no"?>
						<message 
						application="ReceivePay" 
						version="1.0.1"
						merchantId="1000058"
						tranId="'.$TNumber.'"
						timestamp="'.$timestamp.'"
						receivePayNotifyUrl="'.$NotifyUrl.'" 
						receivePayType="1" 
						accountProp="0" 
						accNo="'.$_POST["BankAccount"].'" 
						accName="'.$_POST["AccountName"].'" 
						credentialType="01"
						credentialNo="230224195632121122"
						tel="13120033859"
						amount="'.number_format($_ActualPoints, 2, "" ,"").'" />';
				$strMD5 =  MD5($XML,true);	
		
				$certs = array();
				openssl_pkcs12_read(file_get_contents(dirname(dirname(__FILE__))."\merchant_cert.pfx"), $certs,"7533967"); //其中password为你的证书密码
				//print_r($certs);
				//exit;
				$signature = '';  
				openssl_sign($strMD5, $signature, $certs['pkey']);
				$strsign =  base64_encode($signature);
				
				$base64_src = base64_encode($XML);
				$msg = $base64_src."|".$strsign;
				
				//echo $msg;
				
				$result = self::SockPost2(jinpay_URL, $msg);
				$tmp = explode("|", $result);
				$resp_xml = base64_decode($tmp[0]);
				$resp_sign = $tmp[1];
				//echo ">>".$resp_xml;
				//exit;
				$xml = simplexml_load_string($resp_xml);
				
				$respCode = (string)$xml->attributes()->respCode;
				$respDesc = (string)$xml->attributes()->respDesc;
				$tranId = (string)$xml->attributes()->tranId;
				
				$fp = fopen('../Log/ProxyPay/ProxyPay_LOG_'.date("YmdHi").'.txt', 'a');
				fwrite($fp, " ---------------- 代付通知結果 ---------------- \n\r");
				fwrite($fp, "\$resp_xml =>".$resp_xml."\n\r");
				fwrite($fp, "\$respCode =>".$respCode."\n\r");
				fwrite($fp, "\$respDesc =>".$respDesc."\n\r");
				fwrite($fp, "\$tranId =>".$tranId."\n\r");
				fclose($fp);
				
				if ($respCode == "000") {
					CDbShell::query("UPDATE withdraw SET Status = 1 WHERE TNumber = '".$tranId."'"); 
					//JSModule::JsMessage("提出 提领金额 成功，请等待管理员拨款处理！", "?func=WithdrawPoints");    		
				}
			}
			/*if (is_numeric(mb_strpos($FCRow["Mode"], "太陽城", "0", "UTF-8"))) {
				switch ($_POST["ProductType"]) {
					case "WEIXIN":
						$payKey = array("payKey"=>tfhmye_key);
						break;
					case "B2CPAY":
						if (is_numeric(mb_strpos($FCRow["Mode"], "-1", "0", "UTF-8"))) {
							$payKey = array("payKey"=>tfhmye_key);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-2", "0", "UTF-8"))) {
							$payKey = array("payKey"=>tfhmye_key2);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-3", "0", "UTF-8"))) {
							$payKey = array("payKey"=>tfhmye_key3);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-4", "0", "UTF-8"))) {
							$payKey = array("payKey"=>tfhmye_key4);
						}
						break;
					case "UNIONPAY":
						$payKey = array("payKey"=>tfhmye_key);
						break;
					case "QUICKPAY":
						$payKey = array("payKey"=>tfhmye_key);
						break;
					case "QQ":
						$payKey = array("payKey"=>tfhmye_key);
						break;
				}
    			$parameter = array(
					"outTradeNo"=>$TNumber,
			        "orderPrice"=>number_format($_ActualPoints, 2, "." ,""), 
			        "proxyType"=>"T0",
			        "productType"=>$_POST["ProductType"],
			        "bankAccountType"=>"PRIVATE_DEBIT_ACCOUNT",
			        "phoneNo"=>"17341568745",
			        "receiverName"=>$_POST["AccountName"],
			        "certType"=>"IDENTITY",
			        "receiverAccountNo"=>$_POST["BankAccount"], 
			        "bankBranchNo"=>"1",
			        "bankCode"=>"ICBC",
			        "bankBranchName"=>"工商银行槐树岭支行",
			        "province"=>"北京市",
			        "city"=>"北京市",
			        "notifyUrl"=>Receive_URL."ReplacePayout_tfhmye.php"
				);
				$parameter = array_merge($payKey , $parameter);
				
				switch ($_POST["ProductType"]) {
					case "WEIXIN":
						$parameter["sign"] = self::md5Sign($parameter, tfhmye_password);
						break;
					case "B2CPAY":
						if (is_numeric(mb_strpos($FCRow["Mode"], "-1", "0", "UTF-8"))) {
							$parameter["sign"] = self::md5Sign($parameter, tfhmye_password);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-2", "0", "UTF-8"))) {
							$parameter["sign"] = self::md5Sign($parameter, tfhmye_password2);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-3", "0", "UTF-8"))) {
							$parameter["sign"] = self::md5Sign($parameter, tfhmye_password3);
						}elseif (is_numeric(mb_strpos($FCRow["Mode"], "-4", "0", "UTF-8"))) {
							$parameter["sign"] = self::md5Sign($parameter, tfhmye_password4);
						}
						break;
					case "UNIONPAY":
						$parameter["sign"] = self::md5Sign($parameter, tfhmye_password);
						break;
					case "QUICKPAY":
						$parameter["sign"] = self::md5Sign($parameter, tfhmye_password);
						break;
					case "QQ":
						$parameter["sign"] = self::md5Sign($parameter, tfhmye_password);
						break;
				}
				$fp = fopen('../Log/ProxyPay/ProxyPay_LOG_'.date("YmdHi").'.txt', 'a');
				fwrite($fp, " ---------------- POST Parameter---------------- \n\r");
				fwrite($fp, "POST Parameter =>".json_encode($parameter)."\n\r");
				fclose($fp);
				
				$options = array(
				    'http' => array(
				        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				        'method'  => 'POST',
				        'content' => http_build_query($parameter)
				    )
				);

				$context  = stream_context_create($options);
				$string = file_get_contents(tfhmye_ProxyPay_url, false, $context);
				
				$response = json_decode($string);
				$resultCode = $response->resultCode;
				$outTradeNo = $response->outTradeNo;
				$errMsg = $response->errMsg;
				if ($resultCode == "0000") {
					CDbShell::query("UPDATE withdraw SET Status = 1 WHERE TNumber = '".$outTradeNo."'"); 
				}elseif ($resultCode == "9996") {
					CDbShell::query("UPDATE withdraw SET Status = 2 WHERE TNumber = '".$outTradeNo."'");
				}
				
				$fp = fopen('../Log/ProxyPay/ProxyPay_LOG_'.date("YmdHi").'.txt', 'a');
				fwrite($fp, " ---------------- 代付通知結果 ---------------- \n\r");
				fwrite($fp, "\$string =>".$string."\n\r");
				fwrite($fp, "\$outTradeNo =>".$outTradeNo."\n\r");
				fwrite($fp, "\$resultCode =>".$resultCode."\n\r");
				fwrite($fp, "\$errMsg =>".$errMsg."\n\r");
				fclose($fp);
    		}*/
			
			CDbShell::query("SELECT Switch FROM smsswitch"); 
    		$SMSRow = CDbShell::fetch_array();
    		if ($SMSRow["Switch"] == "1") {
				$_Parameter = "username=53232789&password=0987409408";
				$_Parameter .= "&dstaddr=".SMS_Mobile;
				//$_Parameter .= "&dlvtime=".Date('YmdHis', strtotime(date('Y-m-d H:i:s') ." +15 second"))."&vldtime=".Date('YmdHis', strtotime(date('Y-m-d H:i:s') ." +15 second"));
				$_Parameter .= "&smbody=".iconv("UTF-8","big5","客戶目前有給付的需求");
				//echo $_Parameter;
				//exit;
				
				//$ReturnStr = self::SockPost('http://smexpress.mitake.com.tw:9600/SmQueryGet.asp?username=0987409408&password=53232789',"");
				$ReturnStr = self::SockPost('http://smexpress.mitake.com.tw:9600/SmSendGet.asp', $_Parameter);
				//echo "ReturnStr =>".iconv("big5","UTF-8", $ReturnStr);
				//exit;
			}
			JSModule::JsMessage("提出 提领金额 成功，请等待管理员拨款处理！", "?func=WithdrawPoints");
    		
			exit;
				
    	}
    		
		include($this->GetAdmHtmlPath . "WithdrawPoints.html");
	}
	function md5Sign($arr=array(),$paySecret){
	    ksort($arr);
	    //echo signStr($arr,$paySecret);
	    //echo "<p>";
	    return strtoupper(md5(self::signStr($arr,$paySecret)));
	}
	function signStr($array,$paySecret){
	    $str = "";
	    $i = 0;
	    foreach ($array as $key => $val) {
	        if($key != "sign" && $key != "paySecret"){
	            if($i == 0 ){
	                $str = $str."$key=$val";
	            }else {
	                $str = $str."&$key=$val";
	            }
	            $i++;
	        }
	    }
	    $str = $str."&paySecret=".$paySecret;
	    return  $str;
	}
	
	function WithdrawPointsTW() {
		CDbShell::query("select * from Firm WHERE Sno = ".CSession::getVar("FirmSno").""); 
    	$Row = CDbShell::fetch_array();
    	
    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    		if ($_POST["func"] == "GetPoints") {
    			echo number_format($Row["Points"], 2);
    			exit;
    		}
    		if (strlen(trim($_POST["BankAccount"])) <= 4) {
    			JSModule::ErrorJSMessage("請輸入銀行帳號！");
    			exit;
    		}
    		if (strlen(trim($_POST["AccountName"])) <= 1) {
    			JSModule::ErrorJSMessage("請輸入銀行帳戶名！");
    			exit;
    		}
    		if (strlen(trim($_POST["BankBranch"])) <= 1) {
    			JSModule::ErrorJSMessage("請輸入分行名稱！");
    			exit;
    		}
    		if (!is_numeric($_POST["Withdraw"])) {
    			JSModule::ErrorJSMessage("請輸入正確的提領金額！");
    			exit;
    		}
    		if (intval($_POST["Withdraw"]) <= (floatval($Row["InsteadFee"]) + 10)) {
    			JSModule::ErrorJSMessage("提領金額最少超過".(floatval($Row["InsteadFee"]) + 10)."點！");
    			exit;
    		}
    		
    		if (intval($_POST["Withdraw"]) > intval($Row["Points"])) {
    			JSModule::ErrorJSMessage("提領金額不足！");
    			exit;
    		}
    		$_PaymentType = 0;
    		$_BeforePoints = $Row["Points"];
    		CDbShell::query("SELECT FC.*, PF.Mode FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".CSession::getVar("FirmSno")." AND PF.Kind = '微信' AND FC.Enable = 1 AND (FC.FeeRatio >= 0 OR FC.FixedFee) LIMIT 1"); 
			$FCRow = CDbShell::fetch_array();
			
    		if(strcmp($_POST["Password"], CSession::getVar("admin_password")) != 0){ 
    			JSModule::ErrorJSMessage("密碼不正確！");
    			exit;
    		}
    		
    		$TNumber = "";
    		Again:
    			$TNumber = date("ymd").str_pad(rand(0,9999), 4, '0', STR_PAD_LEFT);
    			CDbShell::query("select * from withdraw WHERE TNumber = ".$TNumber.""); 
    			if (CDbShell::num_rows() > 0) {
    				goto Again;
    			}
    		//echo "alert('".$TNumber."')";
    		//exit;
    		$_ActualPoints = floatval($_POST["Withdraw"]) - floatval($Row["InsteadFee"]);
    		CDbShell::query("UPDATE $this->DB_Table SET Points = Points - ".$_POST["Withdraw"]." WHERE Sno = ".CSession::getVar("FirmSno").""); 
	    	
    		$field = array("TNumber", "FirmSno", "BankAccount", "AccountName", "BankCardType", "Points", "ActualPoints", "InsteadFee", "BankCategory", "TransactionType", "ProductType", "Province", "City", "BankName", "BankBranch", "IDNumber", "Mobile");
    		$value = array($TNumber, CSession::getVar("FirmSno"), $_POST["BankAccount"], $_POST["AccountName"], $_POST["BankCardType"], $_POST["Withdraw"], $_ActualPoints, intval($Row["InsteadFee"]), $_POST["BankCategory"], "T0", " ", " ", " ", " ", $_POST["BankBranch"], "xxxxxx", "17341568745");
			CDbShell::insert("withdraw", $field, $value);
			
    		$field = array("FirmSno", "PaymentType", "BeforePoints", "ChangePoints","AfterPoints","ChangeEvent","Note");
    		$value = array(CSession::getVar("FirmSno"), $_PaymentType, $_BeforePoints, (intval($_POST["Withdraw"]) * -1), ($_BeforePoints - $_POST["Withdraw"]), 2, "提領金額 交易編號「".$TNumber."」");
			CDbShell::insert("pointchangerecord", $field, $value);
			
			CDbShell::query("SELECT Switch FROM smsswitch"); 
    		$SMSRow = CDbShell::fetch_array();
    		if ($SMSRow["Switch"] == "1") {
				$_Parameter = "username=53232789&password=0987409408";
				$_Parameter .= "&dstaddr=".SMS_Mobile;
				//$_Parameter .= "&dlvtime=".Date('YmdHis', strtotime(date('Y-m-d H:i:s') ." +15 second"))."&vldtime=".Date('YmdHis', strtotime(date('Y-m-d H:i:s') ." +15 second"));
				$_Parameter .= "&smbody=".iconv("UTF-8","big5","客戶目前有給付的需求");
				//echo $_Parameter;
				//exit;
				
				//$ReturnStr = self::SockPost('http://smexpress.mitake.com.tw:9600/SmQueryGet.asp?username=0987409408&password=53232789',"");
				$ReturnStr = self::SockPost('http://smexpress.mitake.com.tw:9600/SmSendGet.asp', $_Parameter);
				//echo "ReturnStr =>".iconv("big5","UTF-8", $ReturnStr);
				//exit;
			}
			JSModule::JsMessage("提出 提領金額 成功，請等待管理員撥款處理！", "?func=WithdrawPointsTW");
    		exit;
				
    	}
    		
		include($this->GetAdmHtmlPath . "WithdrawPointsTW.html");
	}
	
	function PointChangeLog() {
		$AdminLevel = CSession::getVar("AdminLevel");
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($_POST["ChangeEvent"] != "") {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " ChangeEvent = ". $_POST["ChangeEvent"];
			}
			if (self::CheckDateTime($_POST["StartTime"]) && self::CheckDateTime($_POST["EndTime"])) {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " (RecordTime BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]." 23:59:59')";
			}
			
			if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
			else $this->SearchKeyword .= " WHERE";
				
			$this->SearchKeyword .= " FirmSno = ". CSession::getVar("FirmSno");
			
			CDbShell::query("SELECT * FROM pointchangerecord $this->SearchKeyword ORDER BY RecordTime DESC, Sno DESC"); 
	    	//echo "SELECT * FROM pointchangerecord $this->SearchKeyword ORDER BY RecordTime DESC";
	    	//exit;
	    	$i = 1;
	    	while ($Row = CDbShell::fetch_array()) {
	    		$Layout .= "<tr>";
	    	
	    		$Layout .= "<td class=\"nowrap\">". $Row["RecordTime"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["BeforePoints"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["ChangePoints"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["AfterPoints"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". (($Row["ChangeEvent"] == 1) ? "交易入款" : (($Row["ChangeEvent"] == 2) ? "提領點數" : (($Row["ChangeEvent"] == 3) ? "退款" : ""))) ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["Note"] ."</td>";
	    		$Layout .= "</tr>";
	    		$i++;
	    	}
	    	
	    	echo $Layout;
	    	exit;
		}
		include($this->GetAdmHtmlPath . "PointChangeLog.html");
	}	
	function PointChangeLogManage () {
		$AdminLevel = CSession::getVar("AdminLevel");
		
		if (CSession::getVar("Boss") != 1) {
			header("location:../admin/admin.php");
			exit;
		}
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($_POST["ChangeEvent"] != "") {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " ChangeEvent = ". $_POST["ChangeEvent"];
			}
			if (self::CheckDateTime($_POST["StartTime"]) && self::CheckDateTime($_POST["EndTime"])) {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " (RecordTime BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]." 23:59:59')";
			}
			
			/*if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
			else $this->SearchKeyword .= " WHERE";
				
			$this->SearchKeyword .= " FirmSno = ". CSession::getVar("FirmSno");*/
			
			$Result1 = CDbShell::query("SELECT * FROM pointchangerecord $this->SearchKeyword ORDER BY RecordTime DESC, Sno DESC"); 
	    	//echo "SELECT * FROM pointchangerecord $this->SearchKeyword ORDER BY RecordTime DESC";
	    	//exit;
	    	$i = 1;
	    	while ($Row = CDbShell::fetch_array($Result1)) {
	    		$Result2 = CDbShell::query("SELECT Name FROM firm WHERE Sno = ".$Row["FirmSno"]); 
	    		$Row2 = CDbShell::fetch_array($Result2);
	    		
	    		$Layout .= "<tr>";
	    	
	    		$Layout .= "<td class=\"nowrap\">". $Row["RecordTime"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row2["Name"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["BeforePoints"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["ChangePoints"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["AfterPoints"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". (($Row["ChangeEvent"] == 1) ? "交易入款" : (($Row["ChangeEvent"] == 2) ? "提領點數" : (($Row["ChangeEvent"] == 3) ? "退款" : ""))) ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["Note"] ."</td>";
	    		$Layout .= "</tr>";
	    		$i++;
	    	}
	    	
	    	echo $Layout;
	    	exit;
		}
		include($this->GetAdmHtmlPath . "PointChangeLogManage.html");
	}	
	function Withdraw() {
		$AdminLevel = CSession::getVar("AdminLevel");
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($_POST["Status"] != "") {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " Status = ". $_POST["Status"];
			}
			if (self::CheckDateTime($_POST["StartTime"]) && self::CheckDateTime($_POST["EndTime"])) {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " (RequestTime BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]." 23:59:59')";
			}
			/*else {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " (RequestTime BETWEEN '".date('Y-m-d 00:00:00')."' AND '".date('Y-m-d 23:59:59')."')";
			}*/
			
			if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
			else $this->SearchKeyword .= " WHERE";
				
			$this->SearchKeyword .= " FirmSno = ". CSession::getVar("FirmSno");
			
			CDbShell::query("SELECT * FROM withdraw $this->SearchKeyword ORDER BY RequestTime DESC"); 
	    	//echo "SELECT * FROM pointchangerecord $this->SearchKeyword ORDER BY RecordTime DESC";
	    	//exit;
	    	$i = 1;
	    	while ($Row = CDbShell::fetch_array()) {
	    		$Layout .= "<tr>";
	    	
	    		$Layout .= "<td class=\"nowrap\">". $Row["RequestTime"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["TNumber"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["BankAccount"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["AccountName"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["BankName"] ."</td>";
	    		//$Layout .= "<td class=\"nowrap\">". $Row["BankBranch"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\" style=\"text-align: right\">". number_format($Row["Points"]) ."</td>";
	    		$Layout .= "<td class=\"nowrap\" style=\"text-align: right\">". number_format($Row["ActualPoints"]) ."</td>";
	    		$Layout .= "<td class=\"nowrap\" style=\"text-align: right\">". number_format($Row["InsteadFee"]) ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". (($Row["Status"] == 0) ? "未撥款" : (($Row["Status"] == 1) ? "己撥款" : (($Row["Status"] == 2) ? "出款中" : (($Row["Status"] == 3) ? "己退款" : "")))) ."</td>";
	    		$Layout .= "</tr>";
	    		$i++;
	    	}
	    	
	    	echo $Layout;
	    	exit;
		}
		include($this->GetAdmHtmlPath . "Withdraw.html");
	}
	
	function WithdrawManage() {
		$AdminLevel = CSession::getVar("AdminLevel");
		
		if (CSession::getVar("Boss") != 1) {
			header("location:../admin/admin.php");
			exit;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($_POST["operate"] == "SendChange") {
				CDbShell::query("UPDATE withdraw SET Status = ".$_POST["Status"]." WHERE Sno = ".$_POST["Sno"]);
				if ($_POST["Status"] == 1) {
					echo "己變更「己撥款」";
				}else {
					echo "己變更「未撥款」";
				}
				exit;
			}else if ($_POST["operate"] == "Refund") {
				$Result1 = CDbShell::query("SELECT * FROM withdraw WHERE Sno = ".$_POST["Sno"]); 
				$WRow = CDbShell::fetch_array($Result1);
				if ($WRow["Status"] == 3) {
					echo "此單己經退款";
					exit;
				}
				
				$Result2 = CDbShell::query("SELECT * FROM firm WHERE Sno = ".$WRow["FirmSno"]); 
				$FRow = CDbShell::fetch_array($Result2);
					
				switch ($WRow["ProductType"]) {
					case "WEIXIN":
						$_PaymentType = 8;
						$_FPoints = $FRow["Points"];
						CDbShell::query("UPDATE firm SET Points = Points + ".$WRow["Points"]." WHERE Sno = ".$WRow["FirmSno"]);
						break;
					case "B2CPAY":
						$_PaymentType = 9;
						$_FPoints = $FRow["OBPoints"];
						CDbShell::query("UPDATE firm SET OBPoints = OBPoints + ".$WRow["Points"]." WHERE Sno = ".$WRow["FirmSno"]);
						break;
					case "UNIONPAY":
						$_PaymentType = 14;
						$_FPoints = $FRow["UPoints"];
						CDbShell::query("UPDATE firm SET UPoints = UPoints + ".$WRow["Points"]." WHERE Sno = ".$WRow["FirmSno"]);
						break;
					case "QUICKPAY":
						$_PaymentType = 15;
						$_FPoints = $FRow["QPoints"];
						CDbShell::query("UPDATE firm SET QPoints = QPoints + ".$WRow["Points"]." WHERE Sno = ".$WRow["FirmSno"]);
						break;
					case "QQ":
						$_PaymentType = 16;
						$_FPoints = $FRow["QQPoints"];
						CDbShell::query("UPDATE firm SET QQPoints = QQPoints + ".$WRow["Points"]." WHERE Sno = ".$WRow["FirmSno"]);
						break;
				}
				
				CDbShell::query("UPDATE withdraw SET Status = 3 WHERE Sno = ".$_POST["Sno"]);
				
				$field = array("FirmSno", "PaymentType", "BeforePoints", "ChangePoints","AfterPoints","ChangeEvent","Note");
	    		$value = array($WRow["FirmSno"], $_PaymentType, $_FPoints, $WRow["Points"], (floatval($_FPoints) + floatval($WRow["Points"])), 3, "提領退款 撥款編號「".$WRow["TNumber"]."」");
				CDbShell::insert("pointchangerecord", $field, $value);
				echo "success";
				exit;
			}
			if ($_POST["Status"] != "") {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " Status = ". $_POST["Status"];
			}
			if (self::CheckDateTime($_POST["StartTime"]) && self::CheckDateTime($_POST["EndTime"])) {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " (RequestTime BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]." 23:59:59')";
			}
			/*else {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " (RequestTime BETWEEN '".date('Y-m-d 00:00:00')."' AND '".date('Y-m-d 23:59:59')."')";
			}*/
			
			$Result1 = CDbShell::query("SELECT * FROM withdraw $this->SearchKeyword ORDER BY RequestTime DESC"); 
	    	//echo "SELECT * FROM pointchangerecord $this->SearchKeyword ORDER BY RecordTime DESC";
	    	//exit;
	    	$i = 1;
	    	while ($Row = CDbShell::fetch_array($Result1)) {
	    		$Result2 = CDbShell::query("SELECT Name FROM firm WHERE Sno = ".$Row["FirmSno"]); 
	    		$Row2 = CDbShell::fetch_array($Result2);
	    		
	    		$Layout .= "<tr>";
	    	
	    		$Layout .= "<td class=\"nowrap\">". $Row["RequestTime"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["TNumber"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row2["Name"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["BankAccount"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["AccountName"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["BankName"] ."</td>";
	    		//$Layout .= "<td class=\"nowrap\">". $Row["BankBranch"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\" style=\"text-align: right\">". number_format($Row["Points"]) ."</td>";
	    		$Layout .= "<td class=\"nowrap\" style=\"text-align: right\">". number_format($Row["ActualPoints"]) ."</td>";
	    		$Layout .= "<td class=\"nowrap\" style=\"text-align: right\">". number_format($Row["InsteadFee"]) ."</td>";
	    		$Layout .= "<td class=\"nowrap\"><select name=\"Status\" id=\"Status\" class=\"form-control\">
						<option value=\"0\" ".(($Row["Status"] == "0") ? "selected" : "").">未撥款</option>
						<option value=\"2\" ".(($Row["Status"] == "2") ? "selected" : "").">出款中</option>
						<option value=\"1\" ".(($Row["Status"] == "1") ? "selected" : "").">己撥款</option>
						<option value=\"3\" ".(($Row["Status"] == "3") ? "selected" : "").">己退款</option>
					  </select>";
	    		//$Layout .= "<td class=\"nowrap\">". (($Row["Status"] == 0) ? "未撥款" : (($Row["Status"] == 1) ? "己撥款" : "")) ."</td>";
	    		$Layout .= "<td class=\"nowrap\">";
	    		if ($Row["Status"] == "3") {
	    			
	    		}else {
	    			$Layout .= "<a href=\"javascript:;\" class=\"btn btn-info btn-small\" id=\"ChangeStatus\" data-sno=\"".$Row["Sno"]."\"><i class=\"icon-edit\"></i> 更改狀態</a>
	    						<a href=\"javascript:;\" class=\"btn btn-info btn-small\" id=\"Refund\" data-sno=\"".$Row["Sno"]."\" data-confirm><i class=\"icon-reply\"></i> 退款</a>";
	    		}
	    		$Layout .= "</td>";
	    		$Layout .= "</tr>";
	    		$i++;
	    	}
	    	
	    	echo $Layout;
	    	exit;
		}
		
		CDbShell::query("UPDATE withdraw SET HaveLooked = '1' WHERE HaveLooked = 0"); 
		include($this->GetAdmHtmlPath . "WithdrawManage.html");
	}
	function GetPoints() {
		CDbShell::query("SELECT Points, OBPoints, UPoints, QPoints, QQPoints FROM Firm WHERE Sno = ".CSession::getVar("FirmSno").""); 
    	$Row = CDbShell::fetch_array();
    	
    	echo number_format(($Row["Points"] + $Row["OBPoints"] + $Row["UPoints"] + $Row["QPoints"] + $Row["QQPoints"]), 2);
	}
	
	function SockPost($URL, $Query){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $URL."?".$Query);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		//curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS, $Query);
		$strReturn = curl_exec($ch);
		
		curl_close ($ch);
		
		return $strReturn;
		
	}
	
	function SockPost2($URL, $Query){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
	function GetSmsSwitch() {
		$AdminLevel = CSession::getVar("AdminLevel");
		
		if (CSession::getVar("Boss") != 1) {
			header("location:../admin/admin.php");
			exit;
		}
		
		CDbShell::query("select Switch from smsswitch"); 
    	$Row = CDbShell::fetch_array();
    	
    	echo $Row["Switch"];
	}
	function UpdateSmsSwitch() {
		$AdminLevel = CSession::getVar("AdminLevel");
		
		if (CSession::getVar("Boss") != 1) {
			header("location:../admin/admin.php");
			exit;
		}
		
		CDbShell::query("UPDATE smsswitch SET Switch = '".$_POST["Switch"]."'"); 
	}
	
	function GetNoLooked() {
		$AdminLevel = CSession::getVar("AdminLevel");
		
		if (CSession::getVar("Boss") != 1) {
			//header("location:../admin/admin.php");
			echo "0";
			exit;
		}
		
		CDbShell::query("SELECT Sno FROM withdraw WHERE HaveLooked = 0"); 
    	$Row = CDbShell::fetch_array();
    	$Num = CDbShell::num_rows();
    	echo $Num;
	}
	function Instead() {
		
		$AdminLevel = CSession::getVar("AdminLevel");
		
		if (CSession::getVar("Boss") != 1) {
			header("location:../admin/admin.php");
			exit;
		}
		
		include '../PHPExcel/IOFactory.php';

		$inputFileName = '../instead.xls';

		//  Read your Excel workbook
		try {
		    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
		    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
		    $objPHPExcel = $objReader->load($inputFileName);
		} catch(Exception $e) {
		    die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ($_POST["operate"] == "SendChange") {
				CDbShell::query("UPDATE withdraw SET Status = ".$_POST["Status"]." WHERE Sno = ".$_POST["Sno"]);
				if ($_POST["Status"] == 1) {
					echo "己變更「己撥款」";
				}else {
					echo "己變更「未撥款」";
				}
				exit;
			}
			if ($_POST["Status"] != "") {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " Status = ". $_POST["Status"];
			}
			if (self::CheckDateTime($_POST["StartTime"]) && self::CheckDateTime($_POST["EndTime"])) {
				if (strlen($this->SearchKeyword) > 0 ) $this->SearchKeyword .= " AND";
				else $this->SearchKeyword .= " WHERE";
				
				$this->SearchKeyword .= " (RequestTime BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]." 23:59:59')";
			}
			
			$Result1 = CDbShell::query("SELECT * FROM withdraw $this->SearchKeyword ORDER BY RequestTime DESC"); 
	    	//echo "SELECT * FROM pointchangerecord $this->SearchKeyword ORDER BY RecordTime DESC";
	    	//exit;
	    	$i = 1;
	    	$row_index = 3;
	    	while ($Row = CDbShell::fetch_array($Result1)) {
	    		$Result2 = CDbShell::query("SELECT Name FROM firm WHERE Sno = ".$Row["FirmSno"]); 
	    		$Row2 = CDbShell::fetch_array($Result2);
	    		
	    		$Layout .= "<tr>";
	    	
	    		$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($Row["BankAccount"], PHPExcel_Cell_DataType::TYPE_STRING); 
	    		$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["AccountName"], PHPExcel_Cell_DataType::TYPE_STRING);
	    		$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($Row["BankCardType"], PHPExcel_Cell_DataType::TYPE_STRING);
	    		$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValue($Row["ActualPoints"]);
	    		$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit($Row["BankCategory"], PHPExcel_Cell_DataType::TYPE_STRING); 
	    		$objPHPExcel->getActiveSheet()->getCell("F{$row_index}")->setValueExplicit($Row["TransactionType"], PHPExcel_Cell_DataType::TYPE_STRING); 
	    		$objPHPExcel->getActiveSheet()->getCell("G{$row_index}")->setValueExplicit($Row["ProductType"], PHPExcel_Cell_DataType::TYPE_STRING); 
	    		$objPHPExcel->getActiveSheet()->getCell("H{$row_index}")->setValueExplicit($Row["Province"], PHPExcel_Cell_DataType::TYPE_STRING); 
	    		$objPHPExcel->getActiveSheet()->getCell("I{$row_index}")->setValueExplicit($Row["City"], PHPExcel_Cell_DataType::TYPE_STRING); 
	    		$objPHPExcel->getActiveSheet()->getCell("J{$row_index}")->setValueExplicit($Row["BankName"], PHPExcel_Cell_DataType::TYPE_STRING); 
	    		$objPHPExcel->getActiveSheet()->getCell("K{$row_index}")->setValueExplicit($Row["BankBranch"], PHPExcel_Cell_DataType::TYPE_STRING); 
	    		$objPHPExcel->getActiveSheet()->getCell("L{$row_index}")->setValueExplicit($Row["IDNumber"], PHPExcel_Cell_DataType::TYPE_STRING); 
	    		$objPHPExcel->getActiveSheet()->getCell("M{$row_index}")->setValueExplicit($Row["Mobile"], PHPExcel_Cell_DataType::TYPE_STRING); 
	    		
	    		$i++;
	    		$row_index++;
	    	}
		}
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5"); 
		$DateSrt = date("YmdHi");
    	//$objWriter->save("../instead".$DateSrt.".xls");
    	header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=../instead".$DateSrt.".xls");
		header('Cache-Control: max-age=0');
		$objWriter->save('php://output');
    	/*echo "<script language=javascript>";
    	echo "document.location='../instead".$DateSrt.".xls';";
    	//echo "javascript:window.open('../instead".$DateSrt.".xls');";
	    //echo "javascript:window.close();";
    	echo "</script>";*/
    	
    	//if(file_exists("../instead".$DateSrt.".xls")) unlink("../instead".$DateSrt.".xls");
	}
	public function	__destruct() {

	}
	
}

class Cryptographic {
	static $iv = "M#yC!ash";
	static $key = "6BAE2CF100974F9D88AE";

	static function encrypt($str) {

		$td = @mcrypt_module_open(MCRYPT_TRIPLEDES, '', MCRYPT_MODE_CBC, self::$iv);
		
		@mcrypt_generic_init($td, self::$key, self::$iv);
		$encrypted = @mcrypt_generic($td, $str);

		@mcrypt_generic_deinit($td);
		@mcrypt_module_close($td);

		return bin2hex($encrypted);
	}
	static function hex2bin($hexdata) {
		$bindata = '';

		for ($i = 0; $i < strlen($hexdata); $i += 2) {
		    $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
		}

		return $bindata;
    }
}
?>