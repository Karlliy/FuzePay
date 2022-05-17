<?php 
class CLedger {
	
	var $DB_Table			= "ledger";
	var $SecDB_Table		= "firm";
	var $PageItems			= 10000;
	var $GetAdmHtmlPath		= "../adm_html/ledger/";
	var $AdminSno			= -1;
	var $SearchKeyword		= "";
	var $SearchKeyword2		= "";
	
	public function	__construct () {
		ini_set('max_execution_time','300'); //max_execution_time','0' <- unlimited time
		ini_set('memory_limit','512M');

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
            	if ($AdminLevel == "1" || $AdminLevel == "2") {
    				$this->Added();
    			}
    			break;
    		case "Modify":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($AdminLevel == "1" || $AdminLevel == "2") {
    				$this->Modify();
    			}
    			break;
    		case "SetRemark":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	//if ($AdminLevel == "1" || $AdminLevel == "2") {
    				self::SetRemark();
    			//}
    			break;
    		case "Deletion":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($AdminLevel == "1" || $AdminLevel == "2") {
    				self::Deletion();
    			}
    			break;
    		case "Detail":
    			self::Detail();
    			break;
			case "Detail2":
				self::Detail2();
				break;
    		case "reconcile":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	
            	if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("ReconcileLayout", CSession::getVar("Purview")) != false)) {
    				self::Reconcile();
    			}
    			break;
    		case "delay":
    			if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("DdelayLayout", CSession::getVar("Purview")) != false)) {
    				self::Delay();
    			}
    			break;
    		case "delaydetail":
    			if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("DdelayLayout", CSession::getVar("Purview")) != false)) {
    				self::DelayDetail();
    			}
    			break;
    		case "funding":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0 || (CSession::getVar("IsChild") == 1 && array_search("FundingLayout", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2)  {
    				self::Funding();
    			}
    			break;
    		case "setfunding":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0  || (CSession::getVar("IsChild") == 1 && array_search("FundingLayout", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				self::SetFunding();
    			}
    			break;
    		case "setaudit":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0  || (CSession::getVar("IsChild") == 1 && array_search("FundingLayout", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				self::SetAudit();
    			}
    			break;
    		case "invoice":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0  || (CSession::getVar("IsChild") == 1 && array_search("InvoiceLayout", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				self::Invoice();
    			}
    			break;
    		case "InvoiceDetail":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0  || (CSession::getVar("IsChild") == 1 &&  array_search("InvoiceLayout", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				self::InvoiceDetail();
    			}
    			break;
    		case "Refund":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("LedgerLayout", CSession::getVar("Purview")) != false)) {
    				self::Refund();
    			}
    			break;
			case "RefundProcess":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0 || (CSession::getVar("IsChild") == 1 && array_search("LedgerLayout", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				self::RefundProcess();
    			}
    			break;
    		case "InterfaSwitc":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if (CSession::getVar("IsChild") == 0) {
    				self::InterfaSwitc();
    			}
    			break;
    		case "Holiday":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if (CSession::getVar("Boss") == 1) {
    				self::Holiday();
    			}
    			break;
    		case "Resend":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("LedgerLayout", CSession::getVar("Purview")) != false)) {
    				self::Resend();
    			}
    			break;
    		case "Alter":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	//if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("AlterLayout", CSession::getVar("Purview")) != false)) {
    			if ($AdminLevel == 1) {
    				self::Alter();
    			}
    			break;
    		case "Increase":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	//if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("IncreaseLayout", CSession::getVar("Purview")) != false)) {
    			if ($AdminLevel == 1) {
    				self::Increase();
    			}
    			break;
			default:
				if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("LedgerLayout", CSession::getVar("Purview")) != false))  {
					$this->Manage();
				}
				break;
		}
	}
	
	static function VerifyData()
    {
    	/*if (self::CheckDateTime(trim($_POST['ReleaseTime'])) == false)
        {
            throw new exception("請輸入正確宣導日期!");
        }*/
        
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
        
        if (strlen(trim($_POST["Invoice"])) < 2 ) {
        	throw new exception("請選擇發票資料");
        }
        
        if (trim($_POST["Invoice"]) == "三聯式，不同商店資料" && strlen(trim($_POST["OtherInvoice"])) < 2 ) {
        	throw new exception("發票選擇「不同商店資料」時，請輸入發票抬頭!");
        }
        
        if (strlen(trim($_POST['ResponsiblePerson'])) < 2)
        {
            throw new exception("請輸入負責人!");
        }
        
        if (strlen(trim($_POST['RegisterAddress'])) < 6)
        {
            throw new exception("請輸入登記地址!");
        }
        
        if (strlen(trim($_POST['TEL'])) < 10)
        {
            throw new exception("請輸入電話!");
        }
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

		ini_set('max_execution_time','300'); //max_execution_time','0' <- unlimited time
		ini_set('memory_limit','512M');
		
    	$AdminLevel = CSession::getVar("AdminLevel");
        $Boss 		= CSession::getVar("Boss");
            	
        $SuccessCount = 0;
		$SuccessAmount = 0;
		
		$BlockCount = 0;
		$BlockAmount = 0;
    	$nowitem = $_GET["ipage"] * $this->PageItems;
    	
    	if ($_GET["attr"] == "Refund") {
    		if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
    		else $this->SearchKeyword .= " AND ";
    		
    		$this->SearchKeyword .= " Chief.State = -2";
    	}else {
	    	
	    	//$Pages = self::AllPages();
	    	$PageBar = self::showPageBar($_GET["ipage"], $Pages, "");
	    	$Keyword = (strlen(trim($_POST["Keyword"])) > 0) ? $_POST["Keyword"] : $_GET["Keyword"];
	    	//print_r($_POST["PaymentCode"]);
	    	//exit;
	    	if (self::CheckDateTime($_POST["StartTime"]) && self::CheckDateTime($_POST["EndTime"])) {
				header("Cache-Control:private");
				$firstDate  = new DateTime($_POST["StartTime"]." 00:00:00");
				$secondDate = new DateTime($_POST["EndTime"]." 23:59:59");
				$intvl = $firstDate->diff($secondDate);

				if ($intvl->days > 31) {
					JSModule::Message("查詢日期最多不能超過30天");
					exit;
				}

				if (is_array($_POST["Condition"]) && Count($_POST["Condition"]) > 0) {
					foreach($_POST["Condition"] as $key => $val) {
						if(trim($_POST[$val."Keyword"]) == "") {
							JSModule::Message("勾選搜尋條件需填入搜尋關鍵字");
							exit;
						}
					}
				}

				if ($intvl->days > 6 && (@Count($_POST["Condition"]) == 0 && @Count($_POST["PaymentCode"]) == 0 && @Count($_POST['State']) == 0)) {
					JSModule::Message("查詢7天以上請至少增加搜尋條件(例如：查7-11或繳款帳號/代碼)");
					exit;
				}

	    		//$this->SearchKeyword = " WHERE ((Chief.PaymentDate >= '".$_POST["StartTime"]." 00:00:00' AND Chief.PaymentDate <= '".$_POST["EndTime"]." 23:59:59') OR (Chief.ClosingTotal = '0' AND Chief.TransactionDate >= '".$_POST["StartTime"]." 00:00:00' AND Chief.TransactionDate <= '".$_POST["EndTime"]." 23:59:59') OR (Chief.TransactionDate < '1991-01-01 00:00:00' AND Chief.CreationDate >= '".$_POST["StartTime"]." 00:00:00' AND Chief.CreationDate <= '".$_POST["EndTime"]." 23:59:59')) ";
	    		//$this->SearchKeyword = " WHERE ((Chief.PaymentDate BETWEEN '".$_POST["StartTime"]." 00:00:00' AND '".$_POST["EndTime"]." 23:59:59') OR (Chief.TransactionDate BETWEEN '".$_POST["StartTime"]." 00:00:00' AND'".$_POST["EndTime"]." 23:59:59') OR (Chief.ClosingTotal = '0' AND Chief.CreationDate BETWEEN '".$_POST["StartTime"]." 00:00:00' AND '".$_POST["EndTime"]." 23:59:59')) ";
				if ($_POST['QueryDate'] == "CreationDate") {
					$this->SearchKeyword = " WHERE ((Chief.PaymentDate BETWEEN '".$_POST["StartTime"]." 00:00:00' AND '".$_POST["EndTime"]." 23:59:59') OR (Chief.ClosingTotal = '0' AND (Chief.TransactionDate BETWEEN '".$_POST["StartTime"]." 00:00:00' AND'".$_POST["EndTime"]." 23:59:59' OR Chief.CreationDate BETWEEN '".$_POST["StartTime"]." 00:00:00' AND '".$_POST["EndTime"]." 23:59:59'))) ";
				}else {
					$this->SearchKeyword = " WHERE (Chief.ExpectedRecordedDate BETWEEN '".$_POST["StartTime"]." 00:00:00' AND '".$_POST["EndTime"]." 23:59:59') ";
				}
			}
	    	
	    	if (is_array($_POST["PaymentCode"]) && Count($_POST["PaymentCode"]) > 0) {
	    		if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    		else $this->SearchKeyword .= " AND ";
	    		
	    		$i = 1;
	    		$this->SearchKeyword .= "(";
	    		foreach($_POST["PaymentCode"] as $key => $val) {
	    		//while (list ($key, $val) = each ($_POST["PaymentCode"])) {
	    			$this->SearchKeyword .= " Chief.PaymentType = ". $val;
	    			
	    			if ($i < Count($_POST["PaymentCode"])) $this->SearchKeyword .= " OR ";
	    			$i++;
	    		}
	    		$this->SearchKeyword .= ")";
	    	}
	    	//print_r($_POST["Condition"]);
	    	
	    	if (is_array($_POST["Condition"]) && Count($_POST["Condition"]) > 0) {
	    		if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    		else $this->SearchKeyword .= " AND ";
	    		
	    		$this->SearchKeyword .= "(";
	    		$i = 1;
	    		foreach($_POST["Condition"] as $key => $val) {
	    		//while (list ($key, $val) = each ($_POST["Condition"])) {
	    			if ($val == "FirmCode" ) {
	    				$this->SearchKeyword .= "Sec.".$val ." like '".$_POST[$val."Keyword"]."%'";
	    			}elseif ($val == "Total") {
	    				$this->SearchKeyword .= "Chief.".$val ." = '".$_POST[$val."Keyword"]."'";
	    			}else {
	    				$this->SearchKeyword .= "Chief.".$val ." like '".$_POST[$val."Keyword"]."%'";
	    			}
	    			
	    			//if ($i < Count($_POST["Condition"])) $this->SearchKeyword .= " OR ";
	    			if ($i < Count($_POST["Condition"])) $this->SearchKeyword .= " AND ";
	    			$i++;
	    		}
	    		
	    		$this->SearchKeyword .= ")";
	    	}
			
			
			if (is_array($_POST["State"]) && count($_POST['State']) > 0) {
				if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
				else $this->SearchKeyword .= " AND (";

				$SearchState = "";
				foreach ((array) $_POST['State'] as $key => $value) {

					if ($value == "Success") {
						if (strlen($SearchState) != 0) $SearchState .= " OR ";
						$SearchState .= " ((Chief.State = 0 OR Chief.State = 1) AND Chief.ClosingTotal > 0)";
					}else if ($value == "Refund") {
						if (strlen($SearchState) != 0) $SearchState .= " OR ";
						//$SearchState .= " ((Chief.State = -2 OR Chief.State = -3) AND Chief.ClosingTotal > 0)";
						$SearchState .= " ((Chief.State = -2 OR Chief.State = -3) )";
					}else if ($value == "Block") {
						if (strlen($SearchState) != 0) $SearchState .= " OR ";
						$SearchState .= " Chief.State = -4";
					}else if ($value == "Block2") {
						if (strlen($SearchState) != 0) $SearchState .= " OR ";
						$SearchState .= " Chief.State = -5";
					}
				}			
				$this->SearchKeyword .= $SearchState." ) ";
			}
			

	    	/*if ($_POST["Success"] == "Success") {
	    		if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    		else $this->SearchKeyword .= " AND ";
	    		
	    		$this->SearchKeyword .= " (Chief.State = 0 OR Chief.State = 1) AND Chief.ClosingTotal > 0";
	    	}
	    	
	    	if ($_POST["Refund"] == "Refund") {
	    		if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    		else $this->SearchKeyword .= " AND ";
	    		
	    		$this->SearchKeyword .= " Chief.State <= -2 AND Chief.ClosingTotal > 0";
			}
			
			if ($_POST["Block"] == "Block") {
	    		if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    		else $this->SearchKeyword .= " AND ";
	    		
	    		$this->SearchKeyword .= " Chief.State <= -4";
	    	}*/
	    	
	    	if (strlen(trim($this->SearchKeyword)) == 0) $this->SearchKeyword = " WHERE Chief.CreationDate BETWEEN '". date('Y-m-d') . " 00:00:00' AND '". date('Y-m-d') . " 23:59:59'";
	    }
    	if ($AdminLevel == 3) {
    		if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
    		else $this->SearchKeyword .= " AND ";
    		
    		$_ChildSno .= CSession::getVar("FirmSno")."";
    		$_ChildSno .= ",";
    	
    		CDbShell::query("SELECT * FROM Firm WHERE ParentSno = ".CSession::getVar("FirmSno"));
    		if (CDbShell::num_rows() > 0) {
	    		while ($Row = CDbShell::fetch_array()) { 
	    			$_ChildSno .= $Row["Sno"];
	    			$_ChildSno .= ",";
	    		}
	    	}
	    	
    		$_ChildSno = substr_replace($_ChildSno,'',-1);
    		$this->SearchKeyword .= " Chief.FirmSno IN (".$_ChildSno.")";
    	}
    	
    	/*$fp = fopen('../Log/SQL/SQL_LOG_'.date("Ymdhi").'.txt', 'a');
		fwrite($fp, " ---------------- SQL開始 ---------------- \n\r");
		fwrite($fp, "SQL => SELECT Chief.Sno,
    							Chief.AuthorizeNumber, 
    							Chief.OrderID, 
    							Chief.PaymentCode,
    							Chief.PaymentName,
    							Chief.MerProductID,
    							Chief.MerUserID,
    							Chief.MerTradeID,
    							Chief.CardNumber,
    							Chief.ClosingTotal,
    							Chief.ClosingTotal,
    							Chief.AuthCode,
    							Chief.ResultCode,
    							Chief.RefundDate,
    							Chief.State,
    							Chief.IP,
    							Chief.ValidDate,
    							Chief.PaymentDate,
    							Chief.TransactionDate,
    							Chief.CreationDate,
    							Sec.FirmCode 
    	FROM $this->DB_Table AS Chief LEFT JOIN Firm AS Sec ON Chief.FirmSno =  Sec.Sno". $this->SearchKeyword ." ORDER BY Chief.Sno DESC LIMIT ".$nowitem."," . $this->PageItems ."\n\r");
		fclose($fp);*/
		
    	/*CDbShell::query("SELECT Chief.Sno,
    							Chief.AuthorizeNumber, 
    							Chief.OrderID, 
    							Chief.PaymentCode,
    							Chief.PaymentName,
    							Chief.MerProductID,
    							Chief.MerUserID,
    							Chief.MerTradeID,
    							Chief.CardNumber
    							Chief.ClosingTotal,
    							Chief.ClosingTotal,
    							Chief.AuthCode,
    							Chief.ResultCode,
    							Chief.RefundDate,
    							Chief.State,
    							Chief.IP,
    							Chief.ValidDate,
    							Chief.PaymentDate,
    							Chief.TransactionDate,
    							Chief.CreationDate,
    							Sec.FirmCode 
    	FROM $this->DB_Table AS Chief LEFT JOIN Firm AS Sec ON Chief.FirmSno =  Sec.Sno". $this->SearchKeyword ." ORDER BY Chief.Sno DESC LIMIT ".$nowitem."," . $this->PageItems); */
    	
    	$Sequence = "Chief.CreationDate";
    	
    	if (isset($_POST['Sequence'])) {
    		$Sequence = "Chief.".$_POST['Sequence'];
    	}
    	CDbShell::query("SELECT Chief.*, Sec.FirmCode, Sms.Mobile FROM $this->DB_Table AS Chief LEFT JOIN Firm AS Sec ON Chief.FirmSno =  Sec.Sno LEFT JOIN smscheck AS Sms ON Chief.CashFlowID = Sms.CashFlowID ". $this->SearchKeyword ." ORDER BY ".$Sequence." DESC ");
    	//echo "SELECT Chief.*, Sec.FirmCode FROM $this->DB_Table AS Chief LEFT JOIN Firm AS Sec ON Chief.FirmSno =  Sec.Sno". $this->SearchKeyword ." ORDER BY Chief.Sno DESC LIMIT ".$nowitem."," . $this->PageItems;
    	if ($_GET["attr"] == "Export") {
    		//print_r($_POST);
    		//echo "select Chief.*,Count(Chief.Sno) AS Num, SUM(Chief.ClosingTotal) AS TotalAmount, SUM(Chief.Fee) AS TotalFee, Sec.Sno AS FirmSno, Sec.Name, Sec.FirmCode, F.FundingID, F.Transfer, F.Other from $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno ". $this->SearchKeyword ."  GROUP BY Chief.FirmSno, Chief.FundingSno order by Chief.FundingSno DESC limit ".$nowitem."," . $this->PageItems;
    			
    		if (CDbShell::num_rows() == 0) {
    			
    			//JSModule::Message("無資料可以匯出！");
    			echo "<script language=javascript>";
    			echo "alert(\"無資料可以匯出！\");";
				echo "window.open('','_self','');window.close();";
				echo "</script>";
    			exit;
    		}
    		include_once("../PHPExcel.php"); 
			include_once("../PHPExcel/IOFactory.php");
			include_once("../PHPExcel/Reader/Excel5.php");
			
			$objPHPExcel = new PHPExcel(); 
			$objPHPExcel->setActiveSheetIndex(0); 
			
			//橫向列印
			$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
			//列印紙張設定
			$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4); 
			$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
			
			//$objPHPExcel->getActiveSheet()->setShowGridlines(true);
			
			$objPHPExcel->getActiveSheet()->mergeCells("A1:P1");
			$objPHPExcel->getActiveSheet()->getCell("A1")->setValue("交易明細報表"); 
			$objPHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
			$row_index = 3;
			
			$objPHPExcel->getActiveSheet()->getCell("A2")->setValue("訂單編號\n授權單號\n交易編號");
			$objPHPExcel->getActiveSheet()->getStyle("A2")->getAlignment()->setWrapText(true); 
			$objPHPExcel->getActiveSheet()->getCell("B2")->setValue("交易日期");
			$objPHPExcel->getActiveSheet()->getCell("C2")->setValue("付款管道");
			$objPHPExcel->getActiveSheet()->getCell("D2")->setValue("特店代號");
			$objPHPExcel->getActiveSheet()->getCell("E2")->setValue("特店商品代號"); 
			$objPHPExcel->getActiveSheet()->getCell("F2")->setValue("消費者ID"); 
			$objPHPExcel->getActiveSheet()->getCell("G2")->setValue("特店訂單編號\n卡號\n繳款帳號/代碼");
			$objPHPExcel->getActiveSheet()->getStyle("G2")->getAlignment()->setWrapText(true); 
			$objPHPExcel->getActiveSheet()->getCell("H2")->setValue("倒數"); 
			$objPHPExcel->getActiveSheet()->getCell("I2")->setValue("交易金額"); 
			$objPHPExcel->getActiveSheet()->getCell("J2")->setValue("結帳金額"); 
			$objPHPExcel->getActiveSheet()->getCell("K2")->setValue("手續費"); 
			$objPHPExcel->getActiveSheet()->getCell("L2")->setValue("授權碼"); 
			$objPHPExcel->getActiveSheet()->getCell("M2")->setValue("回應碼"); 
			$objPHPExcel->getActiveSheet()->getCell("N2")->setValue("交易信息"); 
			$objPHPExcel->getActiveSheet()->getCell("O2")->setValue("退款申請"); 
			$objPHPExcel->getActiveSheet()->getCell("P2")->setValue("追蹤碼"); 
			$objPHPExcel->getActiveSheet()->getCell("Q2")->setValue("eci"); 
			
			$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(45);
			
			while ($Row = CDbShell::fetch_array()) {
				
				if ($Row["PaymentDate"] != "0000-00-00 00:00:00" && $Row["State"] != -1) {
	    			$Row["LedgerDate"] = $Row["PaymentDate"];
	    		}else if($Row["TransactionDate"] > "1991-01-01" && $Row["TransactionDate"] != $Row["PaymentDate"] && $Row["State"] != -1) {
	    			$Row["LedgerDate"] = $Row["TransactionDate"];
	    		}else {
	    			$Row["LedgerDate"] = $Row["CreationDate"];
	    		}
	    		//$Row["LedgerDate"] = $Row["CreationDate"];
	    		
	    		if ($AdminLevel == 3) {
	    			switch($Row["PaymentType"]) {
	    				case 1:
	    					$Row["PaymentCode"] = "虛擬帳號";
	    					break;
	    				case 2:
	    					$Row["PaymentCode"] = "超商代碼";
	    					break;
	    				case 3:
	    					$Row["PaymentCode"] = "7-11";
	    					break;
	    				case 4:
	    					$Row["PaymentCode"] = "全家";
	    					break;
	    				case 5:
	    					$Row["PaymentCode"] = "OK超商";
	    					break;
	    				case 7:
	    					$Row["PaymentCode"] = "信用卡";
	    					break;
	    			}
	    				
	    		}else {
	    			$Row["PaymentCode"] = (strlen($Row["PaymentCode"]) == 0) ? $Row["PaymentName"] : $Row["PaymentCode"];
	    		}
	    		switch($Row["State"]) {
					case "-5":
	    				$Row["StateStr"] = "警察圈存";
	    				break;
					case "-4":
	    				$Row["StateStr"] = "銀行圈存";
	    				break;
	    			case "-3":
	    				$Row["StateStr"] = "已退款";
	    				break;
	    			case "-2":
	    				$Row["StateStr"] = "退款處理中";
	    				break;
	    			case "-1":
	    				$Row["StateStr"] = "未完成交易";
	    				break;
	    			case "0":
	    			case "1":
	    				$Row["StateStr"] = $Row["ResultMesg"];
	    				break;
	    			case "2":
	    				$Row["StateStr"] = "未入款[".$Row["ResultMesg"]."]";
	    				break;
	    		}
	    		if ($Row["PaymentType"] == "1") {
	    			/*if (($Row["State"] == "0" || $Row["State"] == "-2") && $Row["ClosingTotal"] > 0) {
	    				if (strlen($Row["AuthCode"]) > 10) $Row["AuthCode"] = "已授權";
	    			}else {
	    				$Row["AuthCode"] = "授權失敗";
	    			}*/
	    		}else {
	    			$Row["AuthCode"] = "";
	    		}
	    		
	    		if ($Row["PaymentType"] == "2" || $Row["PaymentType"] == "5") {
	    			$Row["Reciprocal"] = "";
	    		}else {
		    		$days = floor((strtotime($Row["ValidDate"]) - strtotime(Date('Y-m-d H:i:s')))/3600/24) ."天 " . floor(((strtotime($Row["ValidDate"]) - strtotime(Date('Y-m-d H:i:s'))) % (3600*24))/3600).":". floor(((strtotime($Row["ValidDate"]) - strtotime(Date('Y-m-d H:i:s'))) % 3600)/(60));
		    		if ($days > 0) $Row["Reciprocal"] = $days;
		    		else $Row["Reciprocal"] = 0;
	    		}
	    		
	    		if (($Row["PaymentCode"] == "Credit_CreditCard" || $Row["PaymentCode"] == "CITI" || $Row["PaymentCode"] == "Gomypay Credit") && (date("Y-m-d", strtotime($Row["TransactionDate"])) == date("Y-m-d") )) {
	    			$Row["ClosingTotal"] = 0;
	    		}
	    		
	    		if ($AdminLevel == 3 && $Row["State"] == -3 && $Row["PaymentType"] == 1) {
	    			$Row["Refund"] = "已退款".$Row["RefundDate"]."";
	    		}
	    		
	    		if ($Row["State"] != -1 && $Row["State"] != -3 && $Row["State"] != -4 && $Row["State"] != -5 && $Row["ClosingTotal"] > 0) {
		    		$SuccessCount++;
		    		$SuccessAmount += $Row["ClosingTotal"];
		    		$FeeAmount += floatval($Row["Fee"]);
		    		$Row["_Fee"] = $Row["Fee"];
				}else if ($Row["State"] == -4 || $Row["State"] == -5){
					$BlockCount++;
					$BlockAmount += $Row["ClosingTotal"];
					$FeeAmount += floatval($Row["Fee"]);
		    		$Row["_Fee"] = $Row["Fee"];
				}else {
	    			$Row["_Fee"] = 0;
	    		}
	    		
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($Row["CashFlowID"]."\n".$Row["AuthorizeNumber"]."\n".$Row["OrderID"] , PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getStyle("A{$row_index}")->getAlignment()->setWrapText(true); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["LedgerDate"] , PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($Row["PaymentCode"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit($Row["FirmCode"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit($Row["MerProductID"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("F{$row_index}")->setValueExplicit($Row["MerUserID"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("G{$row_index}")->setValueExplicit($Row["MerTradeID"]."\n".$Row["CardNumber"]."\n".$Row["VatmAccount"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getStyle("G{$row_index}")->getAlignment()->setWrapText(true); 
				$objPHPExcel->getActiveSheet()->getCell("H{$row_index}")->setValueExplicit($Row["Reciprocal"] , PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("I{$row_index}")->setValueExplicit($Row["Total"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getCell("J{$row_index}")->setValueExplicit($Row["ClosingTotal"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getCell("K{$row_index}")->setValueExplicit($Row["_Fee"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getCell("L{$row_index}")->setValueExplicit($Row["AuthCode"] , PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("M{$row_index}")->setValueExplicit($Row["ResultCode"] , PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("N{$row_index}")->setValueExplicit($Row["StateStr"] , PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("O{$row_index}")->setValueExplicit($Row["Refund"] , PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("P{$row_index}")->setValueExplicit($Row["IP"] , PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("Q{$row_index}")->setValueExplicit("" , PHPExcel_Cell_DataType::TYPE_STRING);
				
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(45);
				$row_index++;
			}
			
			$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(30);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(25);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(10);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(25);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(35);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(10);
		    $objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(15);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(20);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("N")->setWidth(25);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("O")->setWidth(25);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("P")->setWidth(25);
	    	$objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setWidth(5);
	    		
			$objPHPExcel->getActiveSheet(0)->getStyle('A1:Q'.($row_index -1))->getBorders()->getAllborders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			
			$objPHPExcel->getActiveSheet()->setTitle("交易明細報表"); 
				
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet 
			$objPHPExcel->setActiveSheetIndex(0); 
			header('Content-Type: application/vnd.ms-excel;charset=utf-8');
			header('Content-Disposition: attachment;filename="ledger'.date('Y-m-d').'.xls"');
			header('Cache-Control: max-age=0');
			ob_end_clean();  //解決亂碼 輸出前先放上ob_end_clean();
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
			//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
			$objWriter->save('php://output');
		
    	}else {
	        $PageIndex  = $_POST["CurrentPage"];
	        
	        if($PageIndex == "")
	          $PageIndex = 1;
	          
	        $SIndex     = ($PageIndex - 1) * 50;
	        $EIndex     = ($PageIndex + 0) * 50;

	        $index      = 0;
	        $realindex  = 0;
                  
	    	while ($Row = CDbShell::fetch_array()) {
	    		if (CSession::getVar("FirmSno") == "93") $Row["Mobile"] = "******".substr($Row["Mobile"], -3);

	    		if ($Row["State"] != -1 && $Row["State"] != -3 && $Row["State"] != -4 && $Row["State"] != -5 && $Row["ClosingTotal"] > 0) {
		    		$SuccessCount++;
		    		$SuccessAmount += $Row["ClosingTotal"];
		    		$FeeAmount += floatval($Row["Fee"]);
		    		$Row["_Fee"] = $Row["Fee"];
	    		}else if ($Row["State"] == -4 || $Row["State"] == -5){
					$BlockCount++;
					$BlockAmount += $Row["ClosingTotal"];
					$FeeAmount += floatval($Row["Fee"]);
					$Row["_Fee"] = $Row["Fee"];
				}else {
	    			$Row["_Fee"] = 0;
	    		}
         
	    		if ($Row["State"] == -3 ) {
		    		$RefundCount++;
		    		if ($Row["ClosingTotal"] != 0) {
						$RefundAmount += $Row["ClosingTotal"];
					}else {
						$RefundAmount += $Row["Total"];
					}
	    		}
	    		                    
				if($index < $SIndex || $index >= $EIndex) {
					$index++;    
					continue ;    
				}
				else
				{
					$Row["DelLink"] = $_SERVER["PHP_SELF"]."?func=Deletion&Sno=".$Row["Sno"];
					
					if($Row["TransactionDate"] > "1991-01-01"  && $Row["State"] != -1) {
						$Row["LedgerDate"] = $Row["TransactionDate"];
					}else {
						$Row["LedgerDate"] = $Row["CreationDate"];
					}

					if ($AdminLevel == 3) {
						switch($Row["PaymentType"]) {
							case 1:
								$Row["PaymentName"] = "虛擬帳號";
								break;
							case 2:
								$Row["PaymentName"] = "超商代碼";
								break;
							case 3:
								$Row["PaymentName"] = "7-11";
								break;
							case 4:
								$Row["PaymentName"] = "全家";
								break;
							case 5:
								$Row["PaymentName"] = "OK超商";
								break;
							case 7:
								$Row["PaymentName"] = "信用卡";
								break;						
						}
							
					}else {
						$Row["PaymentCode"] = (strlen($Row["PaymentCode"]) == 0) ? $Row["PaymentName"] : $Row["PaymentCode"];
					}
					switch($Row["State"]) {
						case "-5":
							$Row["StateStr"] = "警察圈存";
							break;
						case "-4":
							$Row["StateStr"] = "銀行圈存";
							break;
						case "-3":
							$Row["StateStr"] = "已退款";
							break;
						case "-2":
							$Row["StateStr"] = "退款處理中";
							break;
						case "-1":
							$Row["StateStr"] = "<span style='color:#09C'>未完成交易</span>";
							break;
						case "0":
						case "1":
							$Row["StateStr"] = $Row["ResultMesg"];
							break;
						case "2":
							$Row["StateStr"] = "<span style='color:#FF9900'>未入款[".$Row["ResultMesg"]."]</span>";
							break;
					}
					if ($Row["PaymentType"] == "1") {
					}else {
						$Row["AuthCode"] = "";
					}
					if ($Row["PaymentType"] == "2" || $Row["PaymentType"] == "5") {
						$Row["Reciprocal"] = "";
					}else {
						$days = floor((strtotime($Row["ValidDate"]) - strtotime(Date('Y-m-d H:i:s')))/3600/24) ."天 " . floor(((strtotime($Row["ValidDate"]) - strtotime(Date('Y-m-d H:i:s'))) % (3600*24))/3600).":". floor(((strtotime($Row["ValidDate"]) - strtotime(Date('Y-m-d H:i:s'))) % 3600)/(60));
						if ($days > 0) $Row["Reciprocal"] = $days;
						else $Row["Reciprocal"] = 0;
					}
					
					if (($Row["PaymentCode"] == "Credit_CreditCard" || $Row["PaymentCode"] == "CITI" || $Row["PaymentCode"] == "Gomypay Credit") && (date("Y-m-d", strtotime($Row["TransactionDate"])) == date("Y-m-d") )) {
						$Row["ClosingTotal"] = 0;
					}
					
					if ($Row["State"] == "-2") $Row["background"] = "background-color:#FFEEFA;";
					if ($Row["State"] == "-3") $Row["background"] = "background-color:#EEE;";
					if ($Row["State"] == "-4" || $Row["State"] == "-5") $Row["background"] = "background-color:#FFAAD5;";
					
					if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("LedgerRefund", CSession::getVar("Purview")) != false)) {
						if ($AdminLevel == 3 &&  $Row["ClosingTotal"] != 0 && ($Row["State"] == 0 || $Row["State"] == 1 ) && ($Row["PaymentType"] == 1 || $Row["PaymentType"] == 2)) {
							$Row["Refund"] = "<a href=\"javascript:;\" class=\"btn btn-info btn-small MCaseFa\" data-confirm='您確定要退款?' onclick=\"new jBox('', {content: Refund(".$Row["Sno"]."), color: 'green', attributes: {y: 'bottom'}})\">申請退款</a>";
						}elseif ($AdminLevel <= 2 && $Row["State"] == -2 && ($Row["PaymentType"] == 1 || $Row["PaymentType"] == 2)) {
							$Row["Refund"] = "<a href=\"javascript:;\" class=\"btn btn-info btn-small MCaseFa\" data-confirm='您確定要進行退款處理?' onclick=\"new jBox('', {content: RefundProcess(".$Row["Sno"].", -3), color: 'green', attributes: {y: 'bottom'}})\">處理確認</a><br /><a href=\"javascript:;\" class=\"btn btn-info btn-small MCaseFa\" data-confirm='您確定要進行取消退款?' onclick=\"new jBox('', {content: RefundProcess(".$Row["Sno"].", 0), color: 'green', attributes: {y: 'bottom'}})\">取消退款</a>";
						}elseif ($AdminLevel <= 2 && $Row["State"] == -3 && ($Row["PaymentType"] == 1 || $Row["PaymentType"] == 2)) {
							$Row["Refund"] = "已退款".$Row["RefundDate"]."<br /><a href=\"javascript:;\" class=\"btn btn-info btn-small MCaseFa\" data-confirm='您確定要進行取消退款?' onclick=\"new jBox('', {content: RefundProcess(".$Row["Sno"].", 0), color: 'green', attributes: {y: 'bottom'}})\">取消退款</a>";
						}elseif ($AdminLevel == 3 && $Row["State"] == -3 && ($Row["PaymentType"] == 1 || $Row["PaymentType"] == 2)) {
							$Row["Refund"] = "已退款".$Row["RefundDate"]."";
						}
					}
						    		
					$Html[] = $Row;
					$index++;          
				}          		
	    	}
	    }
      
    	CDbShell::DB_close();
    	include($this->GetAdmHtmlPath . "Manage.html");
    }
	function Added() {
		
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				self::VerifyData();
				
				$field = array("Name", "PublicName","Industry","OtherIndustry","FirmCode","TaxID","Invoice","OtherInvoice","ResponsiblePerson","RegisterAddress","BusinessAddress","Address","TEL","FAX","Window","BusinessTEL","BusinessMobile","BusinessMail","Business1","Business2","Web","WebUrl","BankAccount");
				$value = array($_POST['Name'], $_POST['PublicName'], $_POST['Industry'],$_POST['OtherIndustry'], $_POST['FirmCode'], $_POST['TaxID'], $_POST['Invoice'], $_POST['OtherInvoice'], $_POST['ResponsiblePerson'], $_POST['RegisterAddress']
							 , $_POST['BusinessAddress'], $_POST['Address'], $_POST['TEL'], $_POST['FAX'], $_POST['Window'], $_POST['BusinessTEL'], $_POST['BusinessMobile'], $_POST['BusinessMail'], $_POST['Business1']
							 , $_POST['Business2'], $_POST['Web'], $_POST['WebUrl'], $_POST['BankAccount']);
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
	
	function Modify() {
		
		CDbShell::query("select * from $this->DB_Table where Sno = ". $_GET["Sno"]);
    	$Row = CDbShell::fetch_array();
    	
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				self::VerifyData();
				
				$field = array("Name", "PublicName","Industry","OtherIndustry","FirmCode","TaxID","Invoice","OtherInvoice","ResponsiblePerson","RegisterAddress","BusinessAddress","Address","TEL","FAX","Window","BusinessTEL","BusinessMobile","BusinessMail","Business1","Business2","Web","WebUrl","BankAccount");
				$value = array($_POST['Name'], $_POST['PublicName'], $_POST['Industry'],$_POST['OtherIndustry'], $_POST['FirmCode'], $_POST['TaxID'], $_POST['Invoice'], $_POST['OtherInvoice'], $_POST['ResponsiblePerson'], $_POST['RegisterAddress']
							 , $_POST['BusinessAddress'], $_POST['Address'], $_POST['TEL'], $_POST['FAX'], $_POST['Window'], $_POST['BusinessTEL'], $_POST['BusinessMobile'], $_POST['BusinessMail'], $_POST['Business1']
							 , $_POST['Business2'], $_POST['Web'], $_POST['WebUrl'], $_POST['BankAccount']);
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
	
	function SetRemark() {
		
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				try {
					/*if (CSession::getVar("AdminLevel") == 3)
					{
						throw new exception("廠商不能更改備註!");
					}*/
					if (strlen(trim($_POST['Remark'])) < 2)
			        {
			            throw new exception("請輸入備註!");
			        }
					$field = array("Remark");
					$value = array($_POST['Remark']);
					CDbShell::update($this->DB_Table, $field, $value, "Sno = ". $_POST["Sno"]);
					CDbShell::DB_close();
					
					JSModule::BoxCloseJSMessage("備註修改成功。");
				} catch(Exception $e) {
				   JSModule::ErrorJSMessage($e->getMessage());
				} 
			}else {
				/*$oFCKeditor = new ckeditor();
				$oFCKeditor->BasePath = '../ckeditor/';
				$oFCKeditor->Width = '100%';
				$oFCKeditor->Height = '1000px';
				$oFCKeditor->replace("Detail");*/
				CDbShell::query("SELECT * FROM $this->DB_Table WHERE Sno = ". $_GET["Sno"]);
    			$Row = CDbShell::fetch_array();
    	
				include($this->GetAdmHtmlPath . "SetRemark.html");
			}
		} catch(Exception $e) {
		   	JSModule::ErrorJSMessage($e->getMessage());
		} 
		/*finally {
		   	CDbShell::DB_close();
		}*/
	}
	
	function Refund() {
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				
				CDbShell::query("SELECT * FROM $this->DB_Table WHERE FirmSno = ".CSession::getVar("FirmSno")." AND Sno = ". $_POST["Sno"]);
				if (CDbShell::num_rows() != 1) {
					throw new exception("此為分店單，需由分店帳號進行退款!");
				}
    			$Row = CDbShell::fetch_array();
    			
    			if ($Row["ClosingTotal"] == 0 || $Row["State"] <= -1) {
    				throw new exception("此交易明細可能未完成交易或已經在退款流程中。");
    			}
    			
    			$field = array("State");
				$value = array("-2");
				CDbShell::update($this->DB_Table, $field, $value, "Sno = ". $_POST["Sno"]);
				CDbShell::DB_close();
    	
				JSModule::JSMessage("申請退款成功，等待審核確認。");
			}
		} catch(Exception $e) {
		   	JSModule::JSMessage($e->getMessage());
		} 
	}
		
	function RefundProcess() {
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				
				CDbShell::query("SELECT * FROM $this->DB_Table WHERE Sno = ". $_POST["Sno"]);
				if (CDbShell::num_rows() != 1) {
					throw new exception("交易明細不存在!");
				}
    			if ($_POST["State"] == -3) {
	    			$field = array("RefundDate", "State");
					$value = array(date("Y-m-d H:i:s"), $_POST["State"]);
					CDbShell::update($this->DB_Table, $field, $value, "Sno = ". $_POST["Sno"]);
					CDbShell::DB_close();
	    	
					JSModule::JSMessage("退款成功。");
				}else if ($_POST["State"] == 0) {
	    			$field = array("State");
					$value = array($_POST["State"]);
					CDbShell::update($this->DB_Table, $field, $value, "Sno = ". $_POST["Sno"]);
					CDbShell::DB_close();
	    	
					JSModule::JSMessage("取消退款成功。");
				}
			}
		} catch(Exception $e) {
		   	JSModule::ErrorJSMessage($e->getMessage());
		} 
	}
	function Detail() {
		
		$AdminLevel = CSession::getVar("AdminLevel");
		
		try {
	    	
			if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_GET["attr"] != "Export") {
				if (!is_numeric($_POST['Transfer'])) {
					throw new exception("轉帳費用請輸入正確金額!");
					exit;
				}
				if (!is_numeric($_POST['Other'])) {
					throw new exception("其他費用請輸入正確金額!");
					exit;
				}
				/*if (!is_numeric($_POST['RealFunding'])) {
					throw new exception("實際撥款金額請輸入正確金額!");
					exit;
				}*/
				CDbShell::query("SELECT F.*, Chief.ExpectedRecordedDate, Sec.FirmCode, Sec.PublicName, Sec.Bank, Sec.Branch, Sec.BankAccount, Sec.AccountName, Count(Chief.Sno) AS Num, SUM(IF(Chief.State != -3 , Chief.ClosingTotal, 0)) AS TotalAmount, SUM(IF(Chief.State != -3 , Chief.Fee, 0)) AS TotalFee, Chief.Period FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_POST["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno");
				//echo "SELECT F.FundingID FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
		    	$PRow = CDbShell::fetch_array(); 
	    	
				$RealFunding = (floatval($PRow["TotalAmount"]) - round(floatval($PRow["TotalFee"])) - floatval($_POST['Transfer']) - floatval($_POST['Other']));
				
				$field = array("Transfer", "Other","RealFunding", "Remark");
				$value = array($_POST['Transfer'], $_POST['Other'], $RealFunding , $_POST['Remark']);
				CDbShell::update("Funding", $field, $value, "Sno = ". $_POST["Sno"]);
				echo "alert(\"設定儲存 成功。\");";
				exit;
			}
			
	    	//print_r($Row);
	    	
	    	CDbShell::query("SELECT F.*
			, Chief.FirmSno
			, Chief.ExpectedRecordedDate
			, Sec.FirmCode
			, Sec.PublicName
			, Sec.Bank
			, Sec.Branch
			, Sec.BankAccount
			, Sec.AccountName
			, Count(Chief.Sno) AS Num
			, SUM(IF(Chief.State != -3 && Chief.State != -4 && Chief.State != -5, Chief.ClosingTotal, 0)) AS TotalAmount
			, SUM(IF(Chief.State = -3 , 1, 0)) AS DeductNum
			, SUM(IF(Chief.State = -3, Chief.ClosingTotal, 0)) AS DeductAmount
			, SUM(IF(Chief.State != -3 && Chief.State != -1, Chief.Fee, 0)) AS TotalFee
			, Chief.Period 
				FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno");
			//echo "SELECT F.FundingID FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
	    	$Row = CDbShell::fetch_array(); 
	    	
	    	if ($AdminLevel == 3 ) {
	    		CDbShell::query("SELECT ParentSno FROM Firm WHERE Sno = ".$Row["FirmSno"]);
	    		$ParRow = CDbShell::fetch_array();
	    		if ($ParRow["ParentSno"] != 0 && $Row["FirmSno"] != CSession::getVar("FirmSno")) {
		    		if ($ParRow["ParentSno"] != CSession::getVar("FirmSno")) {
		    			echo $ParRow["ParentSno"]. "||".CSession::getVar("FirmSno")."您沒有權限觀看";
		    			exit;
	    			}
		    	}else {	    		
		    		if ($Row["FirmSno"] != CSession::getVar("FirmSno")) {
		    			echo $ParRow["Sno"]. "||".CSession::getVar("FirmSno")." | 您沒有權限觀看";
		    			exit;
	    			}
	    		}
	    		if ($Row["Audit"] != 1) {
		    		echo "此撥款單尚未審核";
		    		exit;
		    	}
	    	}
	    	
	    	CDbShell::query("SELECT * FROM $this->DB_Table AS Chief WHERE (Chief.State = 0 OR Chief.State = 1) AND Chief.FundingSno = ". $_GET["Sno"] ." ORDER BY TransactionDate DESC, Sno DESC");
			//echo "SELECT F.FundingID FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
	    	while ($Row2 = CDbShell::fetch_array()) {
	    		if ($Row2["PaymentDate"] > "2000-01-01 00:00:00") $Row2["TransactionDate"] = $Row2["PaymentDate"];
	    		$Html[] = $Row2;
	    	}
	    	
			CDbShell::query("SELECT * FROM $this->DB_Table AS Chief WHERE Chief.State = -3 AND Chief.FundingSno = ". $_GET["Sno"] ." ORDER BY TransactionDate DESC, Sno DESC");
			//echo "SELECT F.FundingID FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
	    	while ($Row2 = CDbShell::fetch_array()) {
	    		$RefundHtml[] = $Row2;
	    	}

	    	CDbShell::query("SELECT * FROM $this->DB_Table AS Chief WHERE (Chief.State = -4 OR Chief.State = -5) AND Chief.FundingSno = ". $_GET["Sno"] ." ORDER BY TransactionDate DESC, Sno DESC");
			//echo "SELECT F.FundingID FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
	    	while ($Row2 = CDbShell::fetch_array()) {
	    		$DeductHtml[] = $Row2;
	    	}
	    	
        	if ($_GET["attr"] == "Export") {
        		
        		include_once("../PHPExcel.php"); 
				include_once("../PHPExcel/IOFactory.php");
				include_once("../PHPExcel/Reader/Excel5.php");
				
				$objPHPExcel = new PHPExcel(); 
				$objPHPExcel->setActiveSheetIndex(0); 
				
				//橫向列印
				$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
				//列印紙張設定
				$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4); 
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
				
				//$objPHPExcel->getActiveSheet()->setShowGridlines(true);
				
				//$objPHPExcel->getActiveSheet()->mergeCells("A1:B1");
				$objPHPExcel->getActiveSheet()->getCell("A1")->setValue("撥款編號"); 
				//$objPHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->getCell("B1")->setValueExplicit($Row["FundingID"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->mergeCells("D1:E1");
				$objPHPExcel->getActiveSheet()->getCell("C1")->setValue("撥款日期"); 
				//$objPHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->getCell("D1")->setValueExplicit($Row["ExpectedRecordedDate"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
				
				$objPHPExcel->getActiveSheet()->getCell("A2")->setValue("商店代號"); 
				$objPHPExcel->getActiveSheet()->getCell("B2")->setValueExplicit($Row["FirmCode"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C2")->setValue("交易筆數"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D2:E2");
				$objPHPExcel->getActiveSheet()->getCell("D2")->setValueExplicit($Row["Num"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
				
				$objPHPExcel->getActiveSheet()->getCell("A3")->setValue("商店名稱"); 
				$objPHPExcel->getActiveSheet()->getCell("B3")->setValueExplicit($Row["PublicName"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C3")->setValue("計算時間"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D3:E3");
				$objPHPExcel->getActiveSheet()->getCell("D3")->setValueExplicit($Row["Period"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(25);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A4:E4");
				$objCommentRichText = $objPHPExcel->getActiveSheet()->getCell("A4")->setValue("交易成功明細"); 
				//$objCommentRichText->getFont()->setBold( true);
				$objPHPExcel->getActiveSheet()->getRowDimension(4)->setRowHeight(25);
				$row_index = 6;
				
				$objPHPExcel->getActiveSheet()->getCell("A5")->setValue("訂單編號");
				$objPHPExcel->getActiveSheet()->getCell("B5")->setValue("交易方式");
				$objPHPExcel->getActiveSheet()->getCell("C5")->setValue("交易日期");
				$objPHPExcel->getActiveSheet()->getCell("D5")->setValue("交易金額");
				$objPHPExcel->getActiveSheet()->getCell("E5")->setValue("手續費"); 
				
				$objPHPExcel->getActiveSheet()->getRowDimension(5)->setRowHeight(25);
				
				foreach ((array)$Html as $key=> $value){
					$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($value["OrderID"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getStyle("A{$row_index}")->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($value["PaymentName"] . $value["PaymentCode"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($value["TransactionDate"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit($value["ClosingTotal"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit(round($value["Fee"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
					
					$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
					$row_index++;
				}
				
				//$row_index++;
				$objPHPExcel->getActiveSheet()->mergeCells("A{$row_index}:E{$row_index}");
				$objCommentRichText = $objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("退款明細"); 
				//$objCommentRichText->getFont()->setBold( true);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("訂單編號");
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValue("交易方式");
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("交易日期");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValue("交易金額");
				$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValue("手續費"); 				
				$objPHPExcel->getActiveSheet()->getRowDimension(5)->setRowHeight(25);
				
				foreach ((array)$RefundHtml as $key=> $value){
					$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($value["OrderID"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getStyle("A{$row_index}")->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($value["PaymentName"] . $value["PaymentCode"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($value["TransactionDate"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit($value["ClosingTotal"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit(round($value["Fee"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
					
					$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
					$row_index++;
				}

				$objPHPExcel->getActiveSheet()->mergeCells("A{$row_index}:E{$row_index}");
				$objCommentRichText = $objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("圈存明細"); 
				//$objCommentRichText->getFont()->setBold( true);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("訂單編號");
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValue("交易方式");
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("交易日期");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValue("交易金額");
				$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValue("手續費"); 				
				$objPHPExcel->getActiveSheet()->getRowDimension(5)->setRowHeight(25);
				
				foreach ((array)$DeductHtml as $key=> $value){
					$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($value["OrderID"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getStyle("A{$row_index}")->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($value["PaymentName"] . $value["PaymentCode"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($value["TransactionDate"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit($value["ClosingTotal"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit(round($value["Fee"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
					
					$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
					$row_index++;
				}
				
				
				$objPHPExcel->getActiveSheet()->mergeCells("A{$row_index}:E{$row_index}");
				$objCommentRichText = $objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("商家撥款帳戶"); 
				//$objCommentRichText->getFont()->setBold( true);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue(""); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit("", PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("應撥金額"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D{$row_index}:E{$row_index}");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit(round($Row["TotalAmount"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("銀行"); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["Bank"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("手續費用"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D{$row_index}:E{$row_index}");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit(round($Row["TotalFee"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("分行"); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["Branch"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("轉帳費用"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D{$row_index}:E{$row_index}");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit(round($Row["Transfer"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("匯款帳戶"); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["BankAccount"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("其他費用"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D{$row_index}:E{$row_index}");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit(round($Row["Other"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("匯款戶名"); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["AccountName"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("實際撥款金額"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D{$row_index}:E{$row_index}");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit((($Row["RealFunding"] == 0) ? round(floatval($Row["TotalAmount"]) - floatval($Row["TotalFee"])  - floatval($Row["Transfer"])  - floatval($Row["Other"])) :  round($Row["RealFunding"])), PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("備註"); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["Remark"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(25);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(10);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
		    		
				$objPHPExcel->getActiveSheet(0)->getStyle('A1:E'.($row_index -1))->getBorders()->getAllborders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				$objPHPExcel->getActiveSheet()->setTitle("撥款明細"); 
					
				// Set active sheet index to the first sheet, so Excel opens this as the first sheet 
				$objPHPExcel->setActiveSheetIndex(0); 
				header('Content-Type: application/vnd.ms-excel;charset=utf-8');
				header('Content-Disposition: attachment;filename="fundingdetail'.date('Y-m-d').'.xls"');
				header('Cache-Control: max-age=0');
				ob_end_clean();  //解決亂碼 輸出前先放上ob_end_clean();
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
				//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
				$objWriter->save('php://output');
        	}else {
				include($this->GetAdmHtmlPath . "DetailNode.html");
			}
		} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
		/*finally {
		   CDbShell::DB_close();
		}*/
	}
	function Detail2() {
		
		$AdminLevel = CSession::getVar("AdminLevel");
		
		try {
	    	
			
	    	//print_r($Row);
	    	
	    	CDbShell::query("SELECT F.*
			, Chief.FirmSno
			, Chief.ExpectedRecordedDate
			, Sec.FirmCode
			, Sec.PublicName
			, Sec.Bank
			, Sec.Branch
			, Sec.BankAccount
			, Sec.AccountName
			, SUM(IF(Chief.State != -1 , 1, 0)) AS Num
			, SUM(IF(Chief.State != -3 && Chief.State != -4 && Chief.State != -5 && Chief.State != -1, Chief.ClosingTotal, 0)) AS TotalAmount
			, SUM(IF(Chief.State = -3 , 1, 0)) AS DeductNum
			, SUM(IF(Chief.State = -3, Chief.ClosingTotal, 0)) AS DeductAmount
			, SUM(IF(Chief.State != -3 && Chief.State != -1, Chief.Fee, 0)) AS TotalFee
			, Chief.Period 
				FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno LEFT JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FirmSno = ". $_GET["Sno"]." AND Chief.Period = '".$_GET["Period"]."' AND Chief.ExpectedRecordedDate = '".$_GET["ExpectedRecordedDate"]."' GROUP BY Chief.FirmSno, Chief.Period");
			//echo "SELECT F.FundingID FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
	    	$Row = CDbShell::fetch_array(); 
	    	
	    	if ($AdminLevel == 3 ) {
	    		CDbShell::query("SELECT ParentSno FROM Firm WHERE Sno = ".$Row["FirmSno"]);
	    		$ParRow = CDbShell::fetch_array();
	    		if ($ParRow["ParentSno"] != 0 && $Row["FirmSno"] != CSession::getVar("FirmSno")) {
		    		if ($ParRow["ParentSno"] != CSession::getVar("FirmSno")) {
		    			echo $ParRow["ParentSno"]. "||".CSession::getVar("FirmSno")."您沒有權限觀看";
		    			exit;
	    			}
		    	}else {	    		
		    		if ($Row["FirmSno"] != CSession::getVar("FirmSno")) {
		    			echo $ParRow["Sno"]. "||".CSession::getVar("FirmSno")." | 您沒有權限觀看";
		    			exit;
	    			}
	    		}
	    		/*if ($Row["Audit"] != 1) {
		    		echo "此撥款單尚未審核";
		    		exit;
		    	}*/
	    	}
	    	
	    	CDbShell::query("SELECT * FROM $this->DB_Table AS Chief WHERE (Chief.State = 0 OR Chief.State = 1) AND Chief.FirmSno = ". $_GET["Sno"]." AND Period = '".$_GET["Period"]."' AND Chief.ExpectedRecordedDate = '".$_GET["ExpectedRecordedDate"]."' ORDER BY TransactionDate DESC, Sno DESC");
			//echo "SELECT F.FundingID FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
	    	while ($Row2 = CDbShell::fetch_array()) {
	    		if ($Row2["PaymentDate"] > "2000-01-01 00:00:00") $Row2["TransactionDate"] = $Row2["PaymentDate"];
	    		$Html[] = $Row2;
	    	}
	    	
			CDbShell::query("SELECT * FROM $this->DB_Table AS Chief WHERE Chief.State = -3 AND Chief.FirmSno = ". $_GET["Sno"]." AND Period = '".$_GET["Period"]."' AND Chief.ExpectedRecordedDate = '".$_GET["ExpectedRecordedDate"]."' ORDER BY TransactionDate DESC, Sno DESC");
			//echo "SELECT F.FundingID FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
	    	while ($Row2 = CDbShell::fetch_array()) {
	    		$RefundHtml[] = $Row2;
	    	}

	    	CDbShell::query("SELECT * FROM $this->DB_Table AS Chief WHERE (Chief.State = -4 OR Chief.State = -5) AND Chief.FirmSno = ". $_GET["Sno"]." AND Period = '".$_GET["Period"]."' AND Chief.ExpectedRecordedDate = '".$_GET["ExpectedRecordedDate"]."' ORDER BY TransactionDate DESC, Sno DESC");
			//echo "SELECT F.FundingID FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
	    	while ($Row2 = CDbShell::fetch_array()) {
	    		$DeductHtml[] = $Row2;
	    	}
	    	
        	if ($_GET["attr"] == "Export") {
        		
        		include_once("../PHPExcel.php"); 
				include_once("../PHPExcel/IOFactory.php");
				include_once("../PHPExcel/Reader/Excel5.php");
				
				$objPHPExcel = new PHPExcel(); 
				$objPHPExcel->setActiveSheetIndex(0); 
				
				//橫向列印
				$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
				//列印紙張設定
				$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4); 
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
				
				//$objPHPExcel->getActiveSheet()->setShowGridlines(true);
				
				//$objPHPExcel->getActiveSheet()->mergeCells("A1:B1");
				$objPHPExcel->getActiveSheet()->getCell("A1")->setValue("撥款編號"); 
				//$objPHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->getCell("B1")->setValueExplicit($Row["FundingID"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->mergeCells("D1:E1");
				$objPHPExcel->getActiveSheet()->getCell("C1")->setValue("撥款日期"); 
				//$objPHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->getCell("D1")->setValueExplicit($Row["ExpectedRecordedDate"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
				
				$objPHPExcel->getActiveSheet()->getCell("A2")->setValue("商店代號"); 
				$objPHPExcel->getActiveSheet()->getCell("B2")->setValueExplicit($Row["FirmCode"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C2")->setValue("交易筆數"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D2:E2");
				$objPHPExcel->getActiveSheet()->getCell("D2")->setValueExplicit($Row["Num"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
				
				$objPHPExcel->getActiveSheet()->getCell("A3")->setValue("商店名稱"); 
				$objPHPExcel->getActiveSheet()->getCell("B3")->setValueExplicit($Row["PublicName"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C3")->setValue("計算時間"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D3:E3");
				$objPHPExcel->getActiveSheet()->getCell("D3")->setValueExplicit($Row["Period"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(25);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A4:E4");
				$objCommentRichText = $objPHPExcel->getActiveSheet()->getCell("A4")->setValue("交易成功明細"); 
				//$objCommentRichText->getFont()->setBold( true);
				$objPHPExcel->getActiveSheet()->getRowDimension(4)->setRowHeight(25);
				$row_index = 6;
				
				$objPHPExcel->getActiveSheet()->getCell("A5")->setValue("訂單編號");
				$objPHPExcel->getActiveSheet()->getCell("B5")->setValue("交易方式");
				$objPHPExcel->getActiveSheet()->getCell("C5")->setValue("交易日期");
				$objPHPExcel->getActiveSheet()->getCell("D5")->setValue("交易金額");
				$objPHPExcel->getActiveSheet()->getCell("E5")->setValue("手續費"); 
				
				$objPHPExcel->getActiveSheet()->getRowDimension(5)->setRowHeight(25);
				
				foreach ((array)$Html as $key=> $value){
					$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($value["OrderID"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getStyle("A{$row_index}")->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($value["PaymentName"] . $value["PaymentCode"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($value["TransactionDate"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit($value["ClosingTotal"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit(round($value["Fee"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
					
					$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
					$row_index++;
				}
				
				//$row_index++;
				
				$objPHPExcel->getActiveSheet()->mergeCells("A{$row_index}:E{$row_index}");
				$objCommentRichText = $objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("退款明細"); 
				//$objCommentRichText->getFont()->setBold( true);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("訂單編號");
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValue("交易方式");
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("交易日期");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValue("交易金額");
				$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValue("手續費"); 				
				$objPHPExcel->getActiveSheet()->getRowDimension(5)->setRowHeight(25);
				
				foreach ((array)$RefundHtml as $key=> $value){
					$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($value["OrderID"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getStyle("A{$row_index}")->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($value["PaymentName"] . $value["PaymentCode"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($value["TransactionDate"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit($value["ClosingTotal"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit(round($value["Fee"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
					
					$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
					$row_index++;
				}

				$objPHPExcel->getActiveSheet()->mergeCells("A{$row_index}:E{$row_index}");
				$objCommentRichText = $objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("圈存明細"); 
				//$objCommentRichText->getFont()->setBold( true);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("訂單編號");
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValue("交易方式");
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("交易日期");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValue("交易金額");
				$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValue("手續費"); 				
				$objPHPExcel->getActiveSheet()->getRowDimension(5)->setRowHeight(25);
				
				foreach ((array)$DeductHtml as $key=> $value){
					$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($value["OrderID"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getStyle("A{$row_index}")->getAlignment()->setWrapText(true); 
					$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($value["PaymentName"] . $value["PaymentCode"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($value["TransactionDate"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit($value["ClosingTotal"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit(round($value["Fee"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
					
					$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
					$row_index++;
				}
				
				
				$objPHPExcel->getActiveSheet()->mergeCells("A{$row_index}:E{$row_index}");
				$objCommentRichText = $objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("商家撥款帳戶"); 
				//$objCommentRichText->getFont()->setBold( true);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue(""); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit("", PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("應撥金額"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D{$row_index}:E{$row_index}");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit(round($Row["TotalAmount"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("銀行"); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["Bank"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("手續費用"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D{$row_index}:E{$row_index}");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit(round($Row["TotalFee"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("分行"); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["Branch"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("轉帳費用"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D{$row_index}:E{$row_index}");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit(round($Row["Transfer"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("匯款帳戶"); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["BankAccount"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("其他費用"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D{$row_index}:E{$row_index}");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit(round($Row["Other"]), PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("匯款戶名"); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["AccountName"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValue("實際撥款金額"); 
				$objPHPExcel->getActiveSheet()->mergeCells("D{$row_index}:E{$row_index}");
				$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit((($Row["RealFunding"] == 0) ? round(floatval($Row["TotalAmount"]) - floatval($Row["TotalFee"])  - floatval($Row["Transfer"])  - floatval($Row["Other"])) :  round($Row["RealFunding"])), PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				
				$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValue("備註"); 
				$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["Remark"], PHPExcel_Cell_DataType::TYPE_STRING);
				$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
				
				$row_index++;
				$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(20);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(25);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(20);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(10);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
		    		
				$objPHPExcel->getActiveSheet(0)->getStyle('A1:E'.($row_index -1))->getBorders()->getAllborders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				$objPHPExcel->getActiveSheet()->setTitle("撥款明細"); 
					
				// Set active sheet index to the first sheet, so Excel opens this as the first sheet 
				$objPHPExcel->setActiveSheetIndex(0); 
				header('Content-Type: application/vnd.ms-excel;charset=utf-8');
				header('Content-Disposition: attachment;filename="fundingdetail'.date('Y-m-d').'.xls"');
				header('Cache-Control: max-age=0');
				ob_end_clean();  //解決亂碼 輸出前先放上ob_end_clean();
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
				//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
				$objWriter->save('php://output');
        	}else {
				include($this->GetAdmHtmlPath . "DetailNode2.html");
			}
		} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
		/*finally {
		   CDbShell::DB_close();
		}*/
	}
	
	function Reconcile() {
		
		$AdminLevel = CSession::getVar("AdminLevel");
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$nowitem = $_GET["ipage"] * $this->PageItems;
			$_arrange = " ORDER BY Chief.ExpectedRecordedDate, Chief.FirmSno DESC";
			if (self::CheckDateTime($_POST["StartTime"]) && self::CheckDateTime($_POST["EndTime"])) {
				switch ($_POST["DateType"]) {
					case 1:
						$this->SearchKeyword = " WHERE (Chief.ExpectedRecordedDate BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]."')";
						$_arrange = " ORDER BY Chief.ExpectedRecordedDate, Chief.FirmSno DESC, Chief.Period ";
						break;
					case 2:
						$this->SearchKeyword = " WHERE (Chief.ClosingDate BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]."')";
						$_arrange = " ORDER BY Chief.Period, Chief.FirmSno DESC";
						break;
				}
			}else {
				if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    		else $this->SearchKeyword .= " AND ";

				$this->SearchKeyword = " WHERE Chief.ExpectedRecordedDate >= '".Date("Y-m-d")."'";
				$_arrange = " ORDER BY Chief.ExpectedRecordedDate, Chief.FirmSno DESC, Chief.Period ";
			}
			
			if ($AdminLevel == 3) {
	    		if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    		else $this->SearchKeyword .= " AND ";
	    		
	    		$_ChildSno .= CSession::getVar("FirmSno")."";
	    		$_ChildSno .= ",";
	    	
	    		CDbShell::query("SELECT * FROM Firm WHERE ParentSno = ".CSession::getVar("FirmSno"));
	    		if (CDbShell::num_rows() > 0) {
		    		while ($Row = CDbShell::fetch_array()) { 
		    			$_ChildSno .= $Row["Sno"];
		    			$_ChildSno .= ",";
		    		}
		    	}
		    	
	    		$_ChildSno = substr_replace($_ChildSno,'',-1);
	    		$this->SearchKeyword .= " Chief.FirmSno IN (".$_ChildSno.")";
	    	}else {
				if (trim($_POST["Firm"]) != "") {
					if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
					else $this->SearchKeyword .= " AND ";
					$this->SearchKeyword .= " Chief.FirmSno = '".$_POST["Firm"]."'";
				}
			}
	    	
	    	if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    	else $this->SearchKeyword .= " AND ";
    		$this->SearchKeyword .= " Chief.ClosingTotal > 0 AND Chief.State != -3 AND Chief.State != -1";
    		
    		CDbShell::query("SELECT 
			Chief.Period
			, Chief.ExpectedRecordedDate
			, Chief.ActualRecordedDate
			, MAX(Chief.State)
			, Chief.FundingSno
			, SUM(IF(Chief.State = 0 OR Chief.State = 1, 1, 0)) AS Num
			, SUM(IF(Chief.State = 0 OR Chief.State = 1, Chief.ClosingTotal, 0)) AS TotalAmount
			, SUM(IF(Chief.State = -4 OR Chief.State = -5, 1, 0)) AS DeductNum
			, SUM(IF(Chief.State = -4 OR Chief.State = -5, Chief.ClosingTotal, 0)) AS DeductAmount
			, SUM(IF(Chief.State != -3, Chief.Fee, 0)) AS TotalFee
			, Sec.Sno AS FirmSno
			, Sec.Name
			, Sec.FirmCode
			, F.FundingID
			, F.Transfer
			, F.Other FROM $this->DB_Table AS Chief 
			INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno 
			LEFT JOIN Funding AS F ON Chief.FundingSno = F.Sno ". 
				$this->SearchKeyword ." 
				GROUP BY Chief.FirmSno, Chief.Period, Chief.ExpectedRecordedDate". $_arrange); 
	    
			//CDbShell::query("SELECT Chief.Period, Chief.ExpectedRecordedDate, Chief.ActualRecordedDate, MAX(Chief.State), Chief.FundingSno, SUM(IF(Chief.State != -3 , 1, 0)) AS Num, SUM(IF(Chief.State != -3 , Chief.ClosingTotal, 0)) AS TotalAmount, SUM(IF(Chief.State = -3 , 1, 0)) AS DeductNum, SUM(IF(Chief.State = -3, Chief.ClosingTotal, 0)) AS DeductAmount, SUM(Chief.Fee) AS TotalFee, Sec.Sno AS FirmSno, Sec.Name, Sec.FirmCode, F.FundingID, F.Transfer, F.Other FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno ". $this->SearchKeyword ."  GROUP BY Chief.FirmSno, Chief.FundingSno ORDER BY Chief.FundingSno DESC LIMIT ".$nowitem."," . $this->PageItems); 
	    	//echo "select Chief.*,Count(Chief.Sno) AS Num, SUM(Chief.ClosingTotal) AS TotalAmount, SUM(Chief.Fee) AS TotalFee, Sec.Name, Sec.FirmCode, F.FundingID from $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno ". $this->SearchKeyword ." GROUP BY Chief.FirmSno, Chief.FundingSno order by Chief.FundingSno DESC limit ".$nowitem."," . $this->PageItems;
			if ($_GET["attr"] == "Export") {
	    		//print_r($_POST);
	    		//echo "select Chief.*,Count(Chief.Sno) AS Num, SUM(Chief.ClosingTotal) AS TotalAmount, SUM(Chief.Fee) AS TotalFee, Sec.Sno AS FirmSno, Sec.Name, Sec.FirmCode, F.FundingID, F.Transfer, F.Other from $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno ". $this->SearchKeyword ."  GROUP BY Chief.FirmSno, Chief.FundingSno order by Chief.FundingSno DESC limit ".$nowitem."," . $this->PageItems;
	    			
	    		if (CDbShell::num_rows() == 0) {
	    			
	    			//JSModule::Message("無資料可以匯出！");
	    			echo "<script language=javascript>";
	    			echo "alert(\"無資料可以匯出！\");";
					echo "window.open('','_self','');window.close();";
					echo "</script>";
	    			exit;
	    		}
	    		include_once("../PHPExcel.php"); 
				include_once("../PHPExcel/IOFactory.php");
				include_once("../PHPExcel/Reader/Excel5.php");
				
				$objPHPExcel = new PHPExcel(); 
				$objPHPExcel->setActiveSheetIndex(0); 
				
				//橫向列印
				$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
				//列印紙張設定
				$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4); 
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
				
				//$objPHPExcel->getActiveSheet()->setShowGridlines(true);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A1:M1");
				$objPHPExcel->getActiveSheet()->getCell("A1")->setValue("撥款對帳報表  日期：". $_POST["StartTime"] ." 至 ". $_POST["EndTime"]); 
				$objPHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
				$row_index = 3;
				
				$objPHPExcel->getActiveSheet()->getCell("A2")->setValue("撥款編號");
				$objPHPExcel->getActiveSheet()->getCell("B2")->setValue("特店代號");
				$objPHPExcel->getActiveSheet()->getCell("C2")->setValue("特店名稱");
				$objPHPExcel->getActiveSheet()->getCell("D2")->setValue("結帳區間");
				$objPHPExcel->getActiveSheet()->getCell("E2")->setValue("撥款日期");
				$objPHPExcel->getActiveSheet()->getCell("F2")->setValue("交易筆數");
				$objPHPExcel->getActiveSheet()->getCell("G2")->setValue("交易金額"); 
				$objPHPExcel->getActiveSheet()->getCell("H2")->setValue("圈存筆數"); 
				$objPHPExcel->getActiveSheet()->getCell("I2")->setValue("圈存金額");
				$objPHPExcel->getActiveSheet()->getCell("J2")->setValue("手續費"); 
				$objPHPExcel->getActiveSheet()->getCell("K2")->setValue("匯費"); 
				$objPHPExcel->getActiveSheet()->getCell("L2")->setValue("其他扣款"); 
				$objPHPExcel->getActiveSheet()->getCell("M2")->setValue("實撥金額"); 
				
				$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
				
				while ($Row = CDbShell::fetch_array()) {
					$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($Row["FundingID"] , PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["FirmCode"] , PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($Row["Name"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit($Row["Period"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit($Row["ExpectedRecordedDate"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("F{$row_index}")->setValueExplicit($Row["Num"], PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("G{$row_index}")->setValueExplicit($Row["TotalAmount"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("H{$row_index}")->setValueExplicit($Row["DeductNum"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("I{$row_index}")->setValueExplicit($Row["DeductAmount"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("J{$row_index}")->setValueExplicit(round($Row["TotalFee"]) , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("K{$row_index}")->setValueExplicit($Row["Transfer"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("L{$row_index}")->setValueExplicit($Row["Other"] , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					$objPHPExcel->getActiveSheet()->getCell("M{$row_index}")->setValueExplicit(round(floatval($Row["TotalAmount"]) - floatval($Row["TotalFee"])) , PHPExcel_Cell_DataType::TYPE_NUMERIC);
					
					$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
					$row_index++;
				}
				
				$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(10);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(25);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(15);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(25);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(15);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
				$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setWidth(15);
				$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setWidth(15);
				$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setWidth(15);
				$objPHPExcel->getActiveSheet()->getColumnDimension("M")->setWidth(15);

				$objPHPExcel->getActiveSheet(0)->getStyle('A1:M'.($row_index -1))->getBorders()->getAllborders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				$objPHPExcel->getActiveSheet()->setTitle("撥款對帳報表"); 
					
				// Set active sheet index to the first sheet, so Excel opens this as the first sheet 
				$objPHPExcel->setActiveSheetIndex(0); 
				header('Content-Type: application/vnd.ms-excel;charset=utf-8');
				header('Content-Disposition: attachment;filename="reconcile'.date('Y-m-d').'.xls"');
				header('Cache-Control: max-age=0');
				ob_end_clean();  //解決亂碼 輸出前先放上ob_end_clean();
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
				//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
				$objWriter->save('php://output');
			
	    	}else {
				$i = 1;
				while ($Row = CDbShell::fetch_array()) {
					$Layout .= "<tr>";
				
					$Layout .= "<td class=\"nowrap\">". $Row["FundingID"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". $Row["FirmCode"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". $Row["Name"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". $Row["Period"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". $Row["ExpectedRecordedDate"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". $Row["Num"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". $Row["TotalAmount"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". $Row["DeductNum"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". $Row["DeductAmount"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". round($Row["TotalFee"]) ."</td>";
					$Layout .= "<td class=\"nowrap\">". $Row["Transfer"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". $Row["Other"] ."</td>";
					$Layout .= "<td class=\"nowrap\">". round(floatval($Row["TotalAmount"]) - floatval($Row["TotalFee"])) ."</td>";
					$Layout .= "<td class=\"nowrap\"><a href=\"javascript:;\" class=\"btn btn-info btn-small\" id=\"EditJBox\" onclick=\"javascript:jBox2('".$_SERVER[" PHP_SELF "] ."?func=Detail2&Sno=".$Row["FirmSno"]."&Period=".$Row["Period"]."&ExpectedRecordedDate=".$Row["ExpectedRecordedDate"]."');\"><i class=\"icon-edit\"></i> 明細</a></td>";
						
					$Layout .= "</tr>";
					$i++;
				}
				
				echo $Layout;
				exit;
			}
		}
		CDbShell::query("SELECT * FROM Firm ORDER BY Sno DESC");
		while ($Row = CDbShell::fetch_array()) {
			$Firm[] = $Row;
		}
		include($this->GetAdmHtmlPath . "ReconcileManage.html");
	}
	
	function Delay() {
		$AdminLevel = CSession::getVar("AdminLevel");
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$nowitem = $_GET["ipage"] * $this->PageItems;
			
			if (strlen(trim($_POST["Keyword"])) > 0) {
				$this->SearchKeyword .= " AND (Sec.FirmCode = '".$_POST["Keyword"]."')";
			}
			
			/*if (self::CheckDateTime($_POST["StartTime"]) && self::CheckDateTime($_POST["EndTime"])) {
				switch ($_POST["DateType"]) {
					case 1:
						$this->SearchKeyword = " AND (FundingDate BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]."')";
						break;
					case 2:
						$this->SearchKeyword = " AND (ExpectedRecordedDate BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]."')";
						break;
					case 3:
						$this->SearchKeyword = " AND (ActualRecordedDate BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]."')";
						break;
				}
			}*/
			
			if (self::CheckDateTime($_POST["StartTime"]) && self::CheckDateTime($_POST["EndTime"])) {
				$this->SearchKeyword .= " AND (Chief.ExpectedRecordedDate BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]."')";
			}
			
			if ($_POST["State"] != "") {
				//if (strlen($this->SearchKeyword) > 0) $this->SearchKeyword .= " AND ";
				//else $this->SearchKeyword = " WHERE ";
					
				$this->SearchKeyword .= " AND Chief.State = " . $_POST["State"];
			}

			if (strlen($this->SearchKeyword) == 0) {
				$this->SearchKeyword .= " AND Chief.ExpectedRecordedDate >= '".Date("Y-m-d")."'";
			}
			
			if ($AdminLevel == 3) {
	    		//if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    		//else $this->SearchKeyword .= " AND ";
	    		$this->SearchKeyword .= " AND ";
	    		
	    		$_ChildSno .= CSession::getVar("FirmSno")."";
	    		$_ChildSno .= ",";
	    	
	    		CDbShell::query("SELECT * FROM Firm WHERE ParentSno = ".CSession::getVar("FirmSno"));
	    		if (CDbShell::num_rows() > 0) {
		    		while ($Row = CDbShell::fetch_array()) { 
		    			$_ChildSno .= $Row["Sno"];
		    			$_ChildSno .= ",";
		    		}
		    	}
		    	
	    		$_ChildSno = substr_replace($_ChildSno,'',-1);
	    		$this->SearchKeyword .= " Chief.FirmSno IN (".$_ChildSno.")";
	    	}
			
			CDbShell::query("SELECT Chief.Sno, Chief.FirmSno, Sec.FirmCode, Sec.Name ,Count(Chief.Sno) AS Num, SUM(Chief.ClosingTotal) AS Total, Chief.ExpectedRecordedDate, Chief.Period, Chief.State FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno WHERE (Chief.ClosingTotal > 0 AND (Chief.State = 0 OR Chief.State = 1) ".$this->SearchKeyword." ) GROUP BY Chief.FirmSno, Chief.Period, Chief.ExpectedRecordedDate ORDER BY Chief.Period DESC, Chief.FirmSno"); 
	    	//echo "SELECT Chief.Sno, Chief.FirmSno, Sec.FirmCode, Sec.Name ,Count(Chief.Sno) AS Num, SUM(Chief.ClosingTotal) AS Total, Chief.ExpectedRecordedDate, Chief.Period, Chief.State FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno WHERE (Chief.ClosingTotal > 0 AND Chief.TransactionDate <= '".date('Y-m-d H:i:s', strtotime(date('Y-m-d'). " -1 day"))."' AND Chief.State >= 0 ".$this->SearchKeyword." ) GROUP BY Chief.FirmSno, Chief.ExpectedRecordedDate ORDER BY Chief.ExpectedRecordedDate DESC";
	    	//exit;
	    	$i = 1;
	    	while ($Row = CDbShell::fetch_array()) {
	    		$Layout .= "<tr>";
	    	
	    		$Layout .= "<td class=\"nowrap\">". $Row["FirmCode"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["Name"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["Period"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["Num"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["Total"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". $Row["ExpectedRecordedDate"] ."</td>";
	    		$Layout .= "<td class=\"nowrap\">". (($Row["State"] == 0) ? "未撥款" : (($Row["State"] == 1) ? "已撥款" : "")) ."</td>";
	    		$Layout .= "<td class=\"nowrap\"><a href=\"javascript:;\" class=\"btn btn-info btn-small\" id=\"EditJBox\" onclick=\"javascript:jBox2('".$_SERVER[" PHP_SELF "] ."?func=delaydetail&FirmSno=".$Row["FirmSno"]."&Period=".$Row["Period"]."&ExpectedRecordedDate=".$Row["ExpectedRecordedDate"]."');\"><i class=\"icon-edit\"></i> 明細</a></td>";
	    		
	    		$Layout .= "</tr>";
	    		$i++;
	    	}
	    	
	    	echo $Layout;
	    	exit;
		}
		include($this->GetAdmHtmlPath . "DelayManage.html");
	}
	
	function DelayDetail() {
		try {
			
			$AdminLevel = CSession::getVar("AdminLevel");
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				
				if ($AdminLevel <= 2) {
					
					if ($_POST['ChExpectedRecordedDate'] <= Date("Y-m-d")) {
						echo "alert(\"預計撥款日期  不可以小於今日！\");";
						exit;
					}
					
					$field = array("ExpectedRecordedDate");
					$value = array($_POST['ChExpectedRecordedDate']);
					CDbShell::update($this->DB_Table, $field, $value, "Sno = ". $_POST["Sno"]);
					echo "alert(\"預計撥款日期  更改成功。\");";
					exit;
				}
			}
			
			
			CDbShell::query("SELECT F.*, Chief.ExpectedRecordedDate, Sec.FirmCode, Sec.PublicName, Count(Chief.Sno) AS Num, SUM(Chief.ClosingTotal) AS TotalAmount, SUM(Chief.Fee) AS TotalFee, Chief.Period FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno LEFT JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FirmSno = ". $_GET["FirmSno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno");
			//echo "SELECT F.*, Chief.ExpectedRecordedDate, Sec.FirmCode, Sec.PublicName, Count(Chief.Sno) AS Num, SUM(Chief.ClosingTotal) AS TotalAmount, SUM(Chief.Fee) AS TotalFee, Chief.Period FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno LIFT JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FirmSno = ". $_GET["FirmSno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
	    	$Row = CDbShell::fetch_array(); 
	    	
	    	$Funding = (floatval($Row["TotalAmount"]) - floatval($Row["TotalFee"]));
	    	
	    	$TTotal = 0;
	    	$TFee = 0;
	    	CDbShell::query("SELECT * FROM $this->DB_Table WHERE ClosingTotal > 0 AND State >= 0 AND FirmSno = ". $_GET["FirmSno"] ." AND Period = '".$_GET["Period"]."' AND ExpectedRecordedDate = '".$_GET["ExpectedRecordedDate"]."' ORDER BY TransactionDate DESC, Sno DESC");
			//echo "SELECT F.FundingID FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno WHERE Chief.FundingSno = ". $_GET["Sno"] ." GROUP BY Chief.FirmSno, Chief.FundingSno";
	    	while ($Row2 = CDbShell::fetch_array()) {
	    		$TTotal +=  $Row2["ClosingTotal"];
	    		$TFee +=  $Row2["Fee"];
	    		$Html[] = $Row2;
	    	}
        	$TFee = round($TFee);
			include($this->GetAdmHtmlPath . "DelayDetailNode.html");
		} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
		/*finally {
		   CDbShell::DB_close();
		}*/
	}
	function Funding() {
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$nowitem = $_GET["ipage"] * $this->PageItems;
			
			if (self::CheckDateTime($_POST["StartTime"]) && self::CheckDateTime($_POST["EndTime"])) {
				switch ($_POST["DateType"]) {
					case 1:
						$this->SearchKeyword = " WHERE (Chief.FundingDate BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]."')";
						break;
					case 2:
						$this->SearchKeyword = " WHERE (Chief.ExpectedRecordedDate BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]."')";
						break;
					case 3:
						$this->SearchKeyword = " WHERE (Chief.ActualRecordedDate BETWEEN '".$_POST["StartTime"]."' AND '".$_POST["EndTime"]."')";
						break;
				}
			}
			
			if ($_POST["State"] != "-1" && $_POST["State"] != "") {
				//if (strlen($this->SearchKeyword) > 0) $this->SearchKeyword .= " AND ";
				//else $this->SearchKeyword = " WHERE ";
					
				$this->SearchKeyword2 .=  "HAVING MIN(Chief.State) = " . $_POST["State"];
			}
			
			if (strlen(trim($_POST["Keyword"])) > 0) {
				if (strlen($this->SearchKeyword) > 0) $this->SearchKeyword .= " AND ";
				else $this->SearchKeyword = " WHERE ";
				$this->SearchKeyword .= " (Sec.Name LIKE '%".$_POST["Keyword"]."%' OR Sec.FirmCode LIKE '%".$_POST["Keyword"]."%')";
			}
			
			if (strlen($this->SearchKeyword) == 0) {
				$this->SearchKeyword = " WHERE (Chief.ExpectedRecordedDate BETWEEN '". date('Y-m-d') . " 00:00:00' AND '". date('Y-m-d') . " 23:59:59')";
			}
			
			CDbShell::query("SELECT 
			  Chief.Period
			, Chief.ExpectedRecordedDate
			, Chief.ActualRecordedDate
			, IF(MAX(Chief.State) = 1, 1, 0) AS State
			, Chief.FundingSno
			, SUM(IF(Chief.State = 0 OR Chief.State = 1 , 1, 0)) AS Num
			, SUM(IF(Chief.State = 0 OR Chief.State = 1 , Chief.ClosingTotal, 0)) AS TotalAmount
			, SUM(IF(Chief.State = -3 , 1, 0)) AS DeductNum
			, SUM(IF(Chief.State = -3, Chief.ClosingTotal, 0)) AS DeductAmount
			, SUM(IF(Chief.State != -3, Chief.Fee, 0)) AS TotalFee
			, Sec.Sno AS FirmSno
			, Sec.Name
			, Sec.FirmCode
			, F.FundingID
			, F.Transfer
			, F.Other
			, F.Audit
				FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno ". $this->SearchKeyword ."  GROUP BY Chief.FirmSno, Chief.FundingSno ".$this->SearchKeyword2." ORDER BY Chief.Period, Chief.ExpectedRecordedDate DESC"); 
	    	//echo "SELECT Chief.Period, Chief.ExpectedRecordedDate, Chief.ActualRecordedDate, IF(Chief.State = 1, 1, 0) AS State, Chief.FundingSno, SUM(IF(Chief.State = 0 OR Chief.State = 1 , 1, 0)) AS Num, SUM(IF(Chief.State = 0 OR Chief.State = 1 , Chief.ClosingTotal, 0)) AS TotalAmount, SUM(IF(Chief.State = -3 , 1, 0)) AS DeductNum, SUM(IF(Chief.State = -3, Chief.ClosingTotal, 0)) AS DeductAmount, SUM(IF(Chief.State = 0 OR Chief.State = 1, Chief.Fee, 0)) AS TotalFee, Sec.Sno AS FirmSno, Sec.Name, Sec.FirmCode, F.FundingID, F.Transfer, F.Other, F.Audit FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno ". $this->SearchKeyword ."  GROUP BY Chief.FirmSno, Chief.FundingSno ORDER BY Chief.ExpectedRecordedDate DESC LIMIT ".$nowitem."," . $this->PageItems;
	    	//exit;
	    	if ($_GET["attr"] == "Export") {
	    		//print_r($_POST);
	    		//echo "select Chief.*,Count(Chief.Sno) AS Num, SUM(Chief.ClosingTotal) AS TotalAmount, SUM(Chief.Fee) AS TotalFee, Sec.Sno AS FirmSno, Sec.Name, Sec.FirmCode, F.FundingID, F.Transfer, F.Other from $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno ". $this->SearchKeyword ."  GROUP BY Chief.FirmSno, Chief.FundingSno order by Chief.FundingSno DESC limit ".$nowitem."," . $this->PageItems;
	    			
	    		if (CDbShell::num_rows() == 0) {
	    			
	    			//JSModule::Message("無資料可以匯出！");
	    			echo "<script language=javascript>";
	    			echo "alert(\"無資料可以匯出！\");";
					echo "window.open('','_self','');window.close();";
					echo "</script>";
	    			exit;
	    		}
	    		include_once("../PHPExcel.php"); 
				include_once("../PHPExcel/IOFactory.php");
				include_once("../PHPExcel/Reader/Excel5.php");
				
				$objPHPExcel = new PHPExcel(); 
				$objPHPExcel->setActiveSheetIndex(0); 
				
				//橫向列印
				$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
				//列印紙張設定
				$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4); 
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
				
				//$objPHPExcel->getActiveSheet()->setShowGridlines(true);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A1:I1");
				$objPHPExcel->getActiveSheet()->getCell("A1")->setValue("撥款管理報表  日期：". $_POST["StartTime"] ." 至 ". $_POST["EndTime"]); 
				$objPHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
				$row_index = 3;
				
				$objPHPExcel->getActiveSheet()->getCell("A2")->setValue("特店代號");
				$objPHPExcel->getActiveSheet()->getCell("B2")->setValue("特店名稱");
				$objPHPExcel->getActiveSheet()->getCell("C2")->setValue("撥款編號");
				$objPHPExcel->getActiveSheet()->getCell("D2")->setValue("結帳區間");
				$objPHPExcel->getActiveSheet()->getCell("E2")->setValue("總金額"); 
				$objPHPExcel->getActiveSheet()->getCell("F2")->setValue("預計撥款日期"); 
				$objPHPExcel->getActiveSheet()->getCell("G2")->setValue("實際撥款日期");
				$objPHPExcel->getActiveSheet()->getCell("H2")->setValue("撥款狀態"); 
				$objPHPExcel->getActiveSheet()->getCell("I2")->setValue("備註"); 
				
				$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
				
				while ($Row = CDbShell::fetch_array()) {
					$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($Row["FirmCode"] , PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["Name"] , PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($Row["FundingID"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit($Row["Period"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit(number_format(round(floatval($Row["TotalAmount"]) - round(floatval($Row["TotalFee"])) - floatval($Row["Transfer"]) - floatval($Row["Other"])), 0), PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("F{$row_index}")->setValueExplicit($Row["ExpectedRecordedDate"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("G{$row_index}")->setValueExplicit($Row["ActualRecordedDate"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("H{$row_index}")->setValueExplicit((($Row["State"] == 0) ? "未撥款" : (($Row["State"] == 1) ? "已撥款" : "")) , PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("I{$row_index}")->setValueExplicit($Row["Remark"] , PHPExcel_Cell_DataType::TYPE_STRING);
					
					$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
					$row_index++;
				}
				
				$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(10);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(25);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(15);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(25);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(15);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(15);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(15);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setWidth(15);
			    	
				$objPHPExcel->getActiveSheet(0)->getStyle('A1:I'.($row_index -1))->getBorders()->getAllborders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				$objPHPExcel->getActiveSheet()->setTitle("撥款管理報表"); 
					
				// Set active sheet index to the first sheet, so Excel opens this as the first sheet 
				$objPHPExcel->setActiveSheetIndex(0); 
				header('Content-Type: application/vnd.ms-excel;charset=utf-8');
				header('Content-Disposition: attachment;filename="funding'.date('Y-m-d').'.xls"');
				header('Cache-Control: max-age=0');
				ob_end_clean();  //解決亂碼 輸出前先放上ob_end_clean();
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
				//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
				$objWriter->save('php://output');
			
	    	}else {
		    	$i = 1;
		    	while ($Row = CDbShell::fetch_array()) {
		    		
		    		$Layout .= "<tr>";
		    	
		    		$Layout .= "<td class=\"nowrap\">". $Row["FirmCode"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". $Row["Name"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". $Row["FundingID"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". $Row["Period"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". round(floatval($Row["TotalAmount"]) - floatval($Row["TotalFee"]) - floatval($Row["Transfer"]) - floatval($Row["Other"])) ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". $Row["ExpectedRecordedDate"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". $Row["ActualRecordedDate"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". (($Row["State"] == 0) ? "未撥款" : (($Row["State"] == 1) ? "已撥款" : "")) ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". $Row["Remark"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\"><a href=\"javascript:;\" class=\"btn btn-info btn-small\" id=\"EditJBox\" onclick=\"javascript:jBox2('".$_SERVER[" PHP_SELF "] ."?func=Detail&Sno=".$Row["FundingSno"]."');\"><i class=\"icon-edit\"></i> 明細</a>  ". (($Row["State"] == 0) ? "<a href=\"javascript:;\" class=\"btn btn-info btn-small MCaseFa\" data-confirm onclick=\"new jBox('', {content: Funding(".$Row["FirmSno"].", '".$Row["Period"]."', '".$Row["ExpectedRecordedDate"]."'), color: 'green', attributes: {y: 'bottom'}})\"> 撥 款 </a>" : "" );
		    		
		    		$Layout .= (($Row["Audit"] == 0) ? " <a href=\"javascript:;\" class=\"btn btn-info btn-small MCaseFa\" data-confirm='確定要變更成己審核?' onclick=\"new jBox('', {content: Audit(".$Row["FundingSno"].", '".$Row["Period"]."'), color: 'green', attributes: {y: 'bottom'}})\"> 完成審核 </a>" : "" );
		    		$Layout .= "</td>";
		    		$Layout .= "</tr>";
		    		$i++;
		    	}
		    	
		    	echo $Layout;
		    	exit;
	    	}
	    }
		include($this->GetAdmHtmlPath . "FundingManage.html");
	}
	
	function SetFunding() {
		CDbShell::query("SELECT Sno FROM $this->DB_Table WHERE FirmSno = ".$_POST["FirmSno"]." AND Period = '".$_POST["Period"]."' AND ExpectedRecordedDate = '".$_POST["ExpectedRecordedDate"]."' AND State = 0");
		//echo "select Sno from $this->DB_Table WHERE FirmSno = ".$_POST["FirmSno"]." AND Period = '".$_POST["Period"]."' AND State = 0";
		//exit;
		$FundingID = "";
		$LedgerSno = "";
		$x = 0;
		$num = CDbShell::num_rows();
		while ($Row = CDbShell::fetch_array()) {
			$LedgerSno .= $Row["Sno"];
			if ($x < $num - 1) $LedgerSno .= ",";
			$x++;
		}
		
		if ($num == 0) {			
			JSModule::ErrorJSMessage("此筆資料已撥款。");
			exit;
		}
		/*$field = array("LedgerSno");
		$value = array($LedgerSno);
		CDbShell::insert("funding", $field, $value);
		$FundingSno = CDbShell::insert_id();
			
		$FundingID = Date("Ymd").str_pad($FundingSno,5,'0',STR_PAD_LEFT);
			
		$field = array("FundingID");
		$value = array($FundingID);
		CDbShell::update("funding", $field, $value, "Sno = ". $FundingSno);*/
		
		//$field = array("ActualRecordedDate", "State", "FundingSno");
		//$value = array(Date("Y-m-d"), "1", $FundingSno);
		$field = array("ActualRecordedDate", "State");
		$value = array(Date("Y-m-d"), "1");
		CDbShell::update($this->DB_Table, $field, $value, "FirmSno = ".$_POST["FirmSno"]." AND Period = '".$_POST["Period"]."' AND ExpectedRecordedDate = '".$_POST["ExpectedRecordedDate"]."' AND State = 0");
		
		exit;
	}
	
	function SetAudit() {
		CDbShell::query("SELECT Sno FROM funding WHERE Sno = ".$_POST["FirmSno"]." AND Audit = 1");
		$FundingID = "";
		$LedgerSno = "";
		$x = 0;
		$num = CDbShell::num_rows();
		while ($Row = CDbShell::fetch_array()) {
			$LedgerSno .= $Row["Sno"];
			if ($x < $num - 1) $LedgerSno .= ",";
			$x++;
		}
		
		if ($num > 0) {			
			JSModule::ErrorJSMessage("此筆資料已審核過了。");
			exit;
		}
		/*$field = array("LedgerSno");
		$value = array($LedgerSno);
		CDbShell::insert("funding", $field, $value);
		$FundingSno = CDbShell::insert_id();
			
		$FundingID = Date("Ymd").str_pad($FundingSno,5,'0',STR_PAD_LEFT);
			
		$field = array("FundingID");
		$value = array($FundingID);
		CDbShell::update("funding", $field, $value, "Sno = ". $FundingSno);*/
		
		//$field = array("ActualRecordedDate", "State", "FundingSno");
		//$value = array(Date("Y-m-d"), "1", $FundingSno);
		$field = array("AuditDate", "Audit");
		$value = array(Date("Y-m-d H:i:s"), "1");
		CDbShell::update("funding", $field, $value, "Sno = ".$_POST["FirmSno"]);
		
		exit;
	}
	
	function Invoice() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$nowitem = $_GET["ipage"] * $this->PageItems;
			if (strlen(trim($_POST["Keyword"])) > 0) {
				$this->SearchKeyword = " WHERE (Sec.Name LIKE '%".$_POST["Keyword"]."%' OR Sec.FirmCode LIKE '%".$_POST["Keyword"]."%')";
			}
			
			if (strlen(trim($_POST["Year"])) > 0 && strlen(trim($_POST["Month"])) > 0) {
				if (strlen(trim($this->SearchKeyword)) == 0) $this->SearchKeyword .= " WHERE ";
				else $this->SearchKeyword .= " AND ";
				
				$this->SearchKeyword .= " Chief.PaymentDate BETWEEN '". $_POST["Year"]."-".$_POST["Month"]."-01" . " 00:00:00' AND '". date('Y-m-d',strtotime($_POST["Year"]."-".$_POST["Month"]."-01". " +1 month -1 day")) . " 23:59:59'";
				$Period = $_POST["Year"]."-".$_POST["Month"]."-01 ~ " .date('Y-m-d',strtotime($_POST["Year"]."-".$_POST["Month"]."-01". " +1 month -1 day"));
				
				$IYear = $_POST["Year"];
				$IMonth = $_POST["Month"];
			}
			
			if (strlen(trim($this->SearchKeyword)) == 0) {
				$this->SearchKeyword = " WHERE Chief.PaymentDate BETWEEN '". date('Y-m-01') . " 00:00:00' AND '". date('Y-m-d',strtotime(date('Y-m-01'). " +1 month -1 day")) . " 23:59:59'";
				
				$Period = date('Y-m-01') ." ~ ".date('Y-m-d',strtotime(date('Y-m-01'). " +1 month -1 day"));
								
				$IYear = date('Y');
				$IMonth = date('m');
			}
			
			CDbShell::query("SELECT Chief.FirmSno,
			 	SUM(IF(Chief.State != -3 AND Chief.State != -4 AND Chief.State != -5, 1, 0)) AS Num,
			  	SUM(IF(Chief.State != -3 AND Chief.State != -4 AND Chief.State != -5, Chief.ClosingTotal, 0)) AS TotalAmount,
				SUM(IF(Chief.State = -3 , 1, 0)) AS DeductNum, 
				SUM(IF(Chief.State = -3, Chief.ClosingTotal, 0)) AS DeductAmount, 
				SUM(IF(Chief.State != -3 , Chief.Fee, 0)) AS TotalFee, 
				Sec.Sno AS FirmSno, 
				Sec.Name, 
				Sec.FirmCode, 
				Sec.TaxID, 
				Sec.Name AS FirmName, 
				Sec.Invoice, 
				Sec.OtherInvoice 
				FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno ".
					$this->SearchKeyword ." AND Chief.State != -1 AND Chief.ClosingTotal > 0 
					GROUP BY Chief.FirmSno 
					ORDER BY Chief.Sno DESC 
					LIMIT ".$nowitem."," . $this->PageItems); 
			//echo "SELECT Chief.Period, SUM(IF(Chief.State != -3 , 1, 0)) AS Num, SUM(IF(Chief.State != -3 , Chief.ClosingTotal, 0)) AS TotalAmount, SUM(IF(Chief.State = -3 , 1, 0)) AS DeductNum, SUM(IF(Chief.State = -3, Chief.ClosingTotal, 0)) AS DeductAmount, SUM(IF(Chief.State != -3 , Chief.Fee, 0)) AS TotalFee, Sec.Sno AS FirmSno, Sec.Name, Sec.FirmCode, Sec.TaxID, Sec.Name AS FirmName, Sec.Invoice, Sec.OtherInvoice FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno ". $this->SearchKeyword ." GROUP BY Chief.FirmSno ORDER BY Chief.Sno DESC LIMIT ".$nowitem."," . $this->PageItems;
			
			if ($_GET["attr"] == "Export") {
	    		//print_r($_POST);
	    		//echo "select Chief.*,Count(Chief.Sno) AS Num, SUM(Chief.ClosingTotal) AS TotalAmount, SUM(Chief.Fee) AS TotalFee, Sec.Sno AS FirmSno, Sec.Name, Sec.FirmCode, F.FundingID, F.Transfer, F.Other from $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno INNER JOIN Funding AS F ON Chief.FundingSno = F.Sno ". $this->SearchKeyword ."  GROUP BY Chief.FirmSno, Chief.FundingSno order by Chief.FundingSno DESC limit ".$nowitem."," . $this->PageItems;
	    			
	    		if (CDbShell::num_rows() == 0) {
	    			
	    			//JSModule::Message("無資料可以匯出！");
	    			echo "<script language=javascript>";
	    			echo "alert(\"無資料可以匯出！\");";
					echo "window.open('','_self','');window.close();";
					echo "</script>";
	    			exit;
	    		}
	    		include_once("../PHPExcel.php"); 
				include_once("../PHPExcel/IOFactory.php");
				include_once("../PHPExcel/Reader/Excel5.php");
				
				$objPHPExcel = new PHPExcel(); 
				$objPHPExcel->setActiveSheetIndex(0); 
				
				//橫向列印
				$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
				//列印紙張設定
				$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4); 
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
				$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
				
				//$objPHPExcel->getActiveSheet()->setShowGridlines(true);
				
				$objPHPExcel->getActiveSheet()->mergeCells("A1:H1");
				$objPHPExcel->getActiveSheet()->getCell("A1")->setValue("發票管理報表"); 
				$objPHPExcel->getActiveSheet()->getStyle("A1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(25);
				$row_index = 3;
				
				$objPHPExcel->getActiveSheet()->getCell("A2")->setValue("特店代號");
				$objPHPExcel->getActiveSheet()->getCell("B2")->setValue("特店名稱");
				$objPHPExcel->getActiveSheet()->getCell("C2")->setValue("計算期間");
				$objPHPExcel->getActiveSheet()->getCell("D2")->setValue("請款金額");
				$objPHPExcel->getActiveSheet()->getCell("E2")->setValue("發票金額"); 
				$objPHPExcel->getActiveSheet()->getCell("F2")->setValue("發票格式"); 
				$objPHPExcel->getActiveSheet()->getCell("G2")->setValue("發票抬頭");
				$objPHPExcel->getActiveSheet()->getCell("H2")->setValue("統一編號"); 
				
				$objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(25);
				
				while ($Row = CDbShell::fetch_array()) {
					$objPHPExcel->getActiveSheet()->getCell("A{$row_index}")->setValueExplicit($Row["FirmCode"] , PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("B{$row_index}")->setValueExplicit($Row["Name"] , PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("C{$row_index}")->setValueExplicit($Period, PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("D{$row_index}")->setValueExplicit(round(floatval($Row["TotalAmount"]) - floatval($Row["TotalFee"]) - floatval($Row["Transfer"]) - floatval($Row["Other"])), PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("E{$row_index}")->setValueExplicit(round(floatval($Row["TotalFee"]) + floatval($Row["Transfer"]) + floatval($Row["Other"])), PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("F{$row_index}")->setValueExplicit($Row["Invoice"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("G{$row_index}")->setValueExplicit($Row["FirmName"], PHPExcel_Cell_DataType::TYPE_STRING);
					$objPHPExcel->getActiveSheet()->getCell("H{$row_index}")->setValueExplicit((($Row["Invoice"] == "三聯式，不同商店資料") ? $Row["OtherInvoice"] : $Row["TaxID"]) , PHPExcel_Cell_DataType::TYPE_STRING);
					
					$objPHPExcel->getActiveSheet()->getRowDimension($row_index)->setRowHeight(25);
					$row_index++;
				}
				
				$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(10);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(25);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(35);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(10);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setWidth(10);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setWidth(25);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setWidth(35);
		    	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setWidth(15);
			    	
				$objPHPExcel->getActiveSheet(0)->getStyle('A1:H'.($row_index -1))->getBorders()->getAllborders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				$objPHPExcel->getActiveSheet()->setTitle("發票管理報表"); 
					
				// Set active sheet index to the first sheet, so Excel opens this as the first sheet 
				$objPHPExcel->setActiveSheetIndex(0); 
				header('Content-Type: application/vnd.ms-excel;charset=utf-8');
				header('Content-Disposition: attachment;filename="invoice'.date('Y-m-d').'.xls"');
				header('Cache-Control: max-age=0');
				ob_end_clean();  //解決亂碼 輸出前先放上ob_end_clean();
				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
				//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
				$objWriter->save('php://output');
			
	    	}else {
				while ($Row = CDbShell::fetch_array()) {
				    		
		    		$Layout .= "<tr>";
		    	
		    		$Layout .= "<td class=\"nowrap\">". $Row["FirmCode"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". $Row["Name"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". $Period ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". round(floatval($Row["TotalAmount"]) - floatval($Row["TotalFee"]) - floatval($Row["Transfer"]) - floatval($Row["Other"])) ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". round(floatval($Row["TotalFee"]) + floatval($Row["Transfer"]) + floatval($Row["Other"]))."</td>";
		    		$Layout .= "<td class=\"nowrap\">". $Row["Invoice"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". $Row["FirmName"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\">". (($Row["Invoice"] == "三聯式，不同商店資料") ? $Row["OtherInvoice"] : $Row["TaxID"]) ."</td>";
		    		$Layout .= "<td class=\"nowrap\"><a href=\"javascript:;\" class=\"btn btn-info btn-small\" id=\"EditJBox\" onclick=\"javascript:jBox2('".$_SERVER[" PHP_SELF "] ."?func=InvoiceDetail&Sno=".$Row["FirmSno"]."&IYear=".$IYear."&IMonth=".$IMonth."');\"><i class=\"icon-edit\"></i> 明細</a></td>";
		    		
		    		$Layout .= "</tr>";
		    		$i++;
		    	}
		    	
		    	echo $Layout;
		    	exit;
	    	}
    	}else {
			include($this->GetAdmHtmlPath . "InvoiceManage.html");
		}
	}
	
	function InvoiceDetail() {
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			CDbShell::query("SELECT Sno FROM InvoiceRemark WHERE FirmSno = ".$_POST["FirmSno"]." AND InvoiceDate = '".$_POST["InvoiceDate"]."'");
			if (CDbShell::num_rows() == 0) {
				$field = array("FirmSno", "InvoiceDate", "Remark");
				$value = array($_POST['FirmSno'], $_POST['InvoiceDate'], $_POST['Remark']);
				CDbShell::insert("invoiceremark", $field, $value);
				CDbShell::DB_close();
			}else {
				$field = array("Remark");
				$value = array($_POST['Remark']);
				CDbShell::update("invoiceremark", $field, $value, "FirmSno = ".$_POST["FirmSno"]." AND InvoiceDate = '".$_POST["InvoiceDate"]."'");
				CDbShell::DB_close();
			}
			
			echo "alert('儲存成功！')";
		}else {
			$StartDate = $_GET["IYear"]."-".$_GET["IMonth"]."-01 00:00:00";
			$EndDate = date('Y-m-d',strtotime($_GET["IYear"]."-".$_GET["IMonth"]."-01". " +1 month -1 day")) . " 23:59:59";
			
			$InvoiceDate = $_GET["IYear"]."-".$_GET["IMonth"];
			CDbShell::query("SELECT * FROM InvoiceRemark WHERE FirmSno = ".$_GET["Sno"]." AND InvoiceDate = '".$InvoiceDate."'");
			$IRRow = CDbShell::fetch_array();
			
			CDbShell::query("SELECT Chief.*, Sec.FirmCode, Sec.PublicName FROM $this->DB_Table AS Chief INNER JOIN $this->SecDB_Table AS Sec ON Chief.FirmSno = Sec.Sno WHERE Chief.ClosingTotal > 0 AND Chief.State != -1 AND Chief.State != -3 AND Chief.FirmSno = ". $_GET["Sno"] ." AND Chief.PaymentDate BETWEEN '". $StartDate . "' AND '".$EndDate. "' ORDER BY Chief.PaymentDate , Chief.Sno ");
			//echo "SELECT * FROM $this->DB_Table WHERE ClosingTotal > 0 AND State != -1 AND State != -3 AND FirmSno = ". $_GET["Sno"] ." AND PaymentDate BETWEEN '". $StartDate . "' AND '".$EndDate. "' ORDER BY PaymentDate , Sno ";
	    	while ($Row = CDbShell::fetch_array()) {
	    		$TTotal +=  $Row["ClosingTotal"];
	    		$TFee +=  $Row["Fee"];
	    		$Html[] = $Row;
	    	}
			include($this->GetAdmHtmlPath . "InvoiceDetail.html");
		}
	}
	static function GetRefundNumber() {
		$AdminLevel = CSession::getVar("AdminLevel");
        $Boss 		= CSession::getVar("Boss");
        
		if ($AdminLevel == 3) {
    		//if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
    		//else $this->SearchKeyword .= " AND ";
    		$SearchKeyword .= " AND ";
    		
    		$_ChildSno .= CSession::getVar("FirmSno")."";
    		$_ChildSno .= ",";
    	
    		CDbShell::query("SELECT * FROM Firm WHERE ParentSno = ".CSession::getVar("FirmSno"));
    		if (CDbShell::num_rows() > 0) {
	    		while ($Row = CDbShell::fetch_array()) { 
	    			$_ChildSno .= $Row["Sno"];
	    			$_ChildSno .= ",";
	    		}
	    	}
	    	
    		$_ChildSno = substr_replace($_ChildSno,'',-1);
    		$SearchKeyword .= " FirmSno IN (".$_ChildSno.")";
    	}
		//$db = new CDbShell();  
		//echo "select Main.*, admin.Department, admin.Name from ".self::$DB_Table." AS Main left join admin on Main.AdminSno = admin.Sno order by Main.ReleaseTime desc, Main.Sno desc limit 0, 5";
		CDbShell::query("SELECT Count(Sno) AS Number FROM ledger WHERE State = -2 ". $SearchKeyword); 
    	$Row = CDbShell::fetch_array();
    	$RefundNumber = $Row["Number"];
    	
    	return $RefundNumber;
	
	}
	
	function InterfaSwitc() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			CDbShell::query("UPDATE InterfaSwitc SET Switc = ".$_POST["Switc"]." WHERE InterfaName = '上海銀行虛擬帳戶'"); 
			
			JSModule::Message("儲存成功。", "admin.php?func=InterfaSwitc");
		}else {
			CDbShell::query("SELECT Switc FROM InterfaSwitc WHERE InterfaName = '上海銀行虛擬帳戶'"); 
    		$Row = CDbShell::fetch_array();
    	
			include($this->GetAdmHtmlPath . "InterfaSwitc.html");
		}
	}
	
	function Holiday () {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			try {
				if ($_POST["operate"] == "AddHoliday") {
					if (!self::CheckDateTime($_POST["Holiday"])) {
						throw new exception("請輸入正確日期!");
					}
					
					$field = array("Date");
					$value = array($_POST['Holiday']);
					CDbShell::insert("holiday", $field, $value);
					CDbShell::DB_close();
					
					JSModule::JSMessage("新增成功。", "admin.php?func=Holiday");
					exit;
				}
				
				if ($_POST["operate"] == "DelHoliday") {
					CDbShell::query("DELETE FROM holiday WHERE Sno = '".$_POST["Sno"]."'"); 
					JSModule::JSMessage("刪除成功。", "admin.php?func=Holiday");
					exit;
				}
				
				CDbShell::query("SELECT * FROM holiday WHERE Date >= '".Date("Y-m-01")."' ORDER BY Date"); 
				while ($Row = CDbShell::fetch_array()) {			    		
		    		$Layout .= "<tr>";	    	
		    		$Layout .= "<td class=\"nowrap\">". $Row["Date"] ."</td>";
		    		$Layout .= "<td class=\"nowrap\"><input type=\"button\" name=\"DelHolidayButton\" id=\"DelHolidayButton\" class=\"btn btn-info\" data-confirm data-sno=\"".$Row["Sno"] ."\" value=\" 刪 除 \" /></td>";
		    		$Layout .= "<tr>";
	    		}
				echo $Layout;
				exit;
			
			} catch(Exception $e) {
			   JSModule::ErrorJSMessage($e->getMessage());
			   exit;
			} 
		}
		include($this->GetAdmHtmlPath . "HolidayManage.html");
	}
	
	
	function Resend() {
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				CDbShell::query("SELECT * FROM $this->DB_Table WHERE Sno = ". $_POST["Sno"]);
				if (CDbShell::num_rows() != 1) {
					throw new exception("交易明細不存在!");
				}
				
				CDbShell::query("SELECT F.*, L.MerTradeID, L.MerProductID, L.MerUserID, L.Total, L.ClosingTotal, L.PaymentDate, L.ResultMesg, L.State, L.CardNumber, L.NotifyURL FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.Sno = '".$_POST["Sno"]."'"); 
				$FirmRow = CDbShell::fetch_array();
				$SuccessURL = $FirmRow["SuccessURL"];
				$FailURL = $FirmRow["FailURL"];
				$NotifyURL = $FirmRow["NotifyURL"];
				$Validate = "";	
				if ($FirmRow["ClosingTotal"] > 0 && $FirmRow["State"] != -1) {	//交易成功
					$Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=1&MerTradeID=".$FirmRow["MerTradeID"]."&MerUserID=".$FirmRow["MerUserID"]."");
	
					$SendPOST = array(
						"RtnCode"			=> 	1,
						"RtnMessage"		=> 	$FirmRow["ResultMesg"],
						"MerTradeID"		=> 	$FirmRow["MerTradeID"],
						"MerProductID"		=> 	$FirmRow["MerProductID"],
						"MerUserID"			=> 	$FirmRow["MerUserID"],
						"PayInfo"			=> 	$FirmRow["CardNumber"],
						"Amount"			=> 	$FirmRow["ClosingTotal"],
						"PaymentDate"		=> 	$FirmRow["PaymentDate"],
						"Validate"			=> 	$Validate
					);
					
					self::SockPost($SuccessURL, $SendPOST);
					if ($NotifyURL != "") {
						self::SockPost($NotifyURL, $SendPOST);
					}
				}else {
					$Validate = MD5("ValidateKey=".$FirmRow["ValidateKey"]."&RtnCode=0&MerTradeID=".$FirmRow["MerTradeID"]."&MerUserID=".$FirmRow["MerUserID"]."");
	
					$SendPOST = array(						
						"RtnCode"			=> 	0,
						"RtnMessage"		=> 	$FirmRow["ResultMesg"],
						"MerTradeID"		=> 	$FirmRow["MerTradeID"],
						"MerProductID"		=> 	$FirmRow["MerProductID"],
						"MerUserID"			=> 	$FirmRow["MerUserID"],
						"Amount"			=> 	$FirmRow["Total"],
						"PaymentDate"		=> 	$FirmRow["PaymentDate"],
						"Validate"			=> 	$Validate
					);
					
					self::SockPost($FailURL, $SendPOST);
				}
				
				JSModule::jBoxMessage("交易結果重發成功。");
			}
		} catch(Exception $e) {
		   	JSModule::ErrorJSMessage($e->getMessage());
		} 
	}
	
	function SockPost($URL, $Query){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_POST, 1);
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
		
		$fp = fopen('../Log/Resend/Resend_LOG_'.date("YmdHi").'.txt', 'a');
		fwrite($fp, " ---------------- Success SendPOST ---------------- \n\r");
		fwrite($fp, "Log Time =>".date("Y/m/d H:i:s")."\n\r");
		fwrite($fp, "SuccessURL =>".$URL."\n\r");
		fwrite($fp, "SendPOST =>".Count($Query)."\n\r");
		foreach($Query as $key => $val)
		//while (list ($key, $val) = each ($Query)) 
		{
			fwrite($fp, "key =>".$key."  val=>".$val."\n\r");
		};
		fwrite($fp, "strReturn => ".$strReturn."\n\r");
		fwrite($fp, "curlerror => ".$curlerror."\n\r");
		fclose($fp);
		
		return $strReturn;
	    
	}
	function Alter() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			try {
				if (!is_numeric($_POST["Total"])) {
					throw new exception("請輸入正確的交易金額!");
				}
				if (!is_numeric($_POST["ClosingTotal"])) {
					throw new exception("請輸入正確的結帳金額!");
				}
				if ($_POST["State"] == 0) { 
					if (floatval($_POST["ClosingTotal"]) <= 0) {
						throw new exception("交易成功時請輸入正確的結帳金額!");
					}
					if (($_POST["PaymentDate"] < $_POST["CreationDate"]) || self::CheckDateTime($_POST["PaymentDate"]) == false) {
						throw new exception("交易成功時請輸入正確的付款日期!");
					}
				}
				
				CDbShell::query("SELECT * FROM paymentflow WHERE Type = '".$_POST["PaymentType"]."'"); 
				$PFRow = CDbShell::fetch_array();
				$_PaymentName = $PFRow["Kind"];
				
				if ($_POST["State"] == 0) {
					$_ResultMesg = "交易成功";
				}else {
					$_ResultMesg = "";
				}
				
				switch ($_POST["State"]) {
					case 0:
						$StateStr = "交易成功";
						break;
					case 1:
						$StateStr = "交易成功";
						break;
					case 2:
						$StateStr = "未入款";
						break;
					case -1:
						$StateStr = "未完成交易";
						break;
					case -2:
						$StateStr = "退款處理中";
						break;
					case -3:
						$StateStr = "己退款";
						break;
					case -4:
						$StateStr = "銀行圈存";
						break;
					case -5:
						$StateStr = "警察圈存";
						break;
				}
				CDbShell::query("SELECT L.*, F.FirmCode, F.Points, F.OBPoints, F.UPoints, F.QPoints, F.QQPoints, F.Country FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.Sno = '".$_POST["Sno"]."'"); 
				$Row = CDbShell::fetch_array();
				
				if ($Row["State"] != 1 && $_POST["State"] == 0) {
					//if ($Row["Country"] == "TW") {
						$_OPoints = $Row["Points"];
					/*}else {
						switch ($_POST["PaymentType"]) {
							case "8":
								$_OPoints = $Row["Points"];
								break;
							case "9":
								$_OPoints = $Row["OBPoints"];
								break;
							case "14":
								$_OPoints = $Row["QQPoints"];
								break;
							case "15":
								$_OPoints = $Row["UPoints"];
								break;
							case "16":
								$_OPoints = $Row["QPoints"];
								break;
						}
					}*/
					$field = array("FirmSno", "PaymentType", "BeforePoints", "ChangePoints","AfterPoints","ChangeEvent","Note");
		    		$value = array($Row["FirmSno"], 0, $_OPoints, floatval($_POST["ClosingTotal"]), (floatval($_OPoints) + floatval($_POST["ClosingTotal"])), 4, "修改訂單「".$Row["CashFlowID"]."」為成功交易");
					CDbShell::insert("pointchangerecord", $field, $value);
					//if ($Row["Country"] == "TW") {
						CDbShell::query("UPDATE firm SET Points = Points + ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
					/*}else {
						switch ($_POST["PaymentType"]) {
							case "8":
								CDbShell::query("UPDATE firm SET Points = Points + ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
								break;
							case "9":
								CDbShell::query("UPDATE firm SET OBPoints = OBPoints + ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
								break;
							case "14":
								CDbShell::query("UPDATE firm SET QQPoints = QQPoints + ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
								break;
							case "15":
								CDbShell::query("UPDATE firm SET UPoints = UPoints + ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
								break;
							case "16":
								CDbShell::query("UPDATE firm SET QPoints = QPoints + ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
								break;
						}
					}*/
				}
				
				if (($Row["State"] == 1 || $Row["State"] == 0 ) && $_POST["State"] != 1) {
					//if ($Row["Country"] == "TW") {
						$_OPoints = $Row["Points"];
					/*}else {
						switch ($Row["PaymentType"]) {
							case "8":
								$_OPoints = $Row["Points"];
								break;
							case "9":
								$_OPoints = $Row["OBPoints"];
								break;
							case "14":
								$_OPoints = $Row["QQPoints"];
								break;
							case "15":
								$_OPoints = $Row["UPoints"];
								break;
							case "16":
								$_OPoints = $Row["QPoints"];
								break;
						}
					}*/
					$field = array("FirmSno", "PaymentType", "BeforePoints", "ChangePoints","AfterPoints","ChangeEvent","Note");
		    		$value = array($Row["FirmSno"], 0, $_OPoints, (floatval($_POST["ClosingTotal"]) * -1), (floatval($_OPoints) - floatval($_POST["ClosingTotal"])), 4, "修改訂單「".$Row["CashFlowID"]."」為".$StateStr);
					CDbShell::insert("pointchangerecord", $field, $value);
					
					//if ($Row["Country"] == "TW") {
						CDbShell::query("UPDATE firm SET Points = Points - ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
					/*}else {
						switch ($Row["PaymentType"]) {
							case "8":
								CDbShell::query("UPDATE firm SET Points = Points - ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
								break;
							case "9":
								CDbShell::query("UPDATE firm SET OBPoints = OBPoints - ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
								break;
							case "14":
								CDbShell::query("UPDATE firm SET QQPoints = QQPoints - ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
								break;
							case "15":
								CDbShell::query("UPDATE firm SET UPoints = UPoints - ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
								break;
							case "16":
								CDbShell::query("UPDATE firm SET QPoints = QPoints - ".floatval($_POST["ClosingTotal"])." WHERE Sno = ".$Row["FirmSno"]);
								break;
						}
					}*/
				}
			
				$field = array("AuthorizeNumber", "OrderID", "CreationDate", "TransactionDate", "PaymentDate", "PaymentType", "PaymentName", "MerProductID", "MerUserID", "MerTradeID", "CardNumber", "VatmAccount", "Total", "ClosingTotal", "ResultMesg", "State", "Remark");
				$value = array($_POST["AuthorizeNumber"], $_POST["OrderID"], $_POST["CreationDate"], $_POST["PaymentDate"], $_POST["PaymentDate"], $_POST["PaymentType"], $_PaymentName, $_POST["MerProductID"], $_POST["MerUserID"], $_POST["MerTradeID"], $_POST["CardNumber"], $_POST["VatmAccount"], $_POST["Total"], $_POST["ClosingTotal"], $_ResultMesg, $_POST["State"], $_POST["Remark"]);
				CDbShell::update("ledger", $field, $value, "Sno = ". $_POST["Sno"]);
				
				if ($_POST["State"] == -4 || $_POST["State"] == -5) {
					$field = array("BlockDate");
					$value = array(Date("Y-m-d H:i:s"));
					CDbShell::update("ledger", $field, $value, "Sno = ". $_POST["Sno"]);
				}
				
				//if ($_POST["State"] == "1") {
					CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$Row["FirmSno"]." AND PF.Type = '".$Row["PaymentType"]."' AND FC.Enable = 1 LIMIT 1"); 
					$FCRow = CDbShell::fetch_array();
					
					$_PaymentDate = date("Y-m-d H:i:s", strtotime($_POST["PaymentDate"]));
					switch ($FCRow["Closing"]) {
						case "Day":
							//$ExpectedRecordedDate = date('Y-m-d', strtotime($_PaymentDate ." +".$FCRow["Day"]." day"));
							$Period = date("Y-m-d", strtotime($_PaymentDate));
							$ClosingDate = date("Y-m-d", strtotime($_PaymentDate));
							break;
						case "Week":
							$TodayWeek = Date('w');
							if ($TodayWeek == 0) $TodayWeek = 7;
							//$ExpectedRecordedDate = date("Y-m-d", strtotime($_PaymentDate ."+".(7- $TodayWeek + $FCRow["Day"]). " day"));
							$Period = date('Y-m-d', strtotime($_PaymentDate. " -".($TodayWeek - 1)." day")) . " ~ " . date('Y-m-d',strtotime($_PaymentDate. " +".(7 - $TodayWeek)." day"));
							$ClosingDate = date('Y-m-d', strtotime($_PaymentDate. " +".(7- $TodayWeek)." day"));
							break;
						case "Month":
							//$ExpectedRecordedDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month +".$FCRow["Day"]." day"));
							$Period = date("Y-m-01", strtotime($_PaymentDate)) . " ~ " . date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
							$ClosingDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
							break;
					}
					$ExpectedRecordedDate = CommonElement::CountHoliday($ClosingDate, $FCRow["Day"], true);
					
					if ($Row["ExpireDatetime"] == "") $_ExpireDatetime = "0000-00-00 00:00:00";
					
					$Fee = 0;
					if (floatval($FCRow["FeeRatio"]) > 0){
						$Fee = floatval($_POST["ClosingTotal"]) * floatval($FCRow["FeeRatio"] / 100);
					}
					if ($FCRow["FixedFee"] != 0) {
						$Fee = $Fee + $FCRow["FixedFee"];
					} 

					if (is_numeric($FCRow["MinFee"]) && $FCRow["MinFee"] > 0) {
            
						if ($Fee < floatval($FCRow["MinFee"]))
							$Fee = $FCRow["MinFee"];
					}
					
					if (is_numeric($FCRow["MaxFee"]) && $FCRow["MaxFee"] > 0) {
						
						if ($Fee > floatval($FCRow["MaxFee"]))
							$Fee = $FCRow["MaxFee"];
					}
		
					/*if ($FCRow["FixedFee"] != 0) {
						$Fee = $FCRow["FixedFee"];
					}else {
						$Fee = floatval($_POST["ClosingTotal"]) * floatval($FCRow["FeeRatio"] / 100);
						
						if (is_numeric($FCRow["MinFee"]) && $FCRow["MinFee"] > 0) {
							
							if ($Fee < floatval($FCRow["MinFee"])) $Fee = $FCRow["MinFee"];
						}
						
						if (is_numeric($FCRow["MaxFee"]) && $FCRow["MaxFee"] > 0) {
							
							if ($Fee > floatval($FCRow["MaxFee"])) $Fee = $FCRow["MaxFee"];
						}
					}*/
					
					$field = array("Period", "ClosingDate", "ExpectedRecordedDate", "Fee");
					$value = array($Period, $ClosingDate, $ExpectedRecordedDate, $Fee);
					CDbShell::update("ledger", $field, $value, "Sno = ". $_POST["Sno"]);
				//}
				
				$fp = fopen('../Log/AlterOrder/AlterOrder_LOG_'.date("YmdHi").'.txt', 'a');
				fwrite($fp, " ---------------- Alter Order LOG ---------------- \n\r");
				fwrite($fp, "Log Time =>".date("Y/m/d H:i:s")."\n\r");
				fwrite($fp, "Sno =>".$_POST["Sno"]."\n\r");
				fwrite($fp, "CashFlowID =>".$Row["CashFlowID"]."\n\r");
				fwrite($fp, "StateStr =>".$StateStr."\n\r");
				fclose($fp);
				$js .= "alert(\"成功編輯訂單!\");";							
				$js .= "parent.location.reload();";
		        $js .= "parent.$.jBox.close('user');";	
				echo $js;
				exit;
			
			} catch(Exception $e) {
			   	JSModule::ErrorJSMessage($e->getMessage());
			} 
			
		}else {
			CDbShell::query("SELECT L.*, F.FirmCode FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.Sno = '".$_GET["Sno"]."'"); 
			$Row = CDbShell::fetch_array();
			
			CDbShell::query("SELECT * FROM paymentflow GROUP BY Type ORDER BY Type"); 
			while ($PFRow = CDbShell::fetch_array()) {
				$PFHtml[] = $PFRow;
			}
			
			include($this->GetAdmHtmlPath . "AlterLedger.html");
		}
	}
	
	function Increase() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			try {
				
				/*if (strtotime($_POST["CreationDate"]) < strtotime(Date("Y-m-d 00:00:00"))) {
					throw new exception("交易日期不可小於今日!");
				}
				
				if ($_POST["PaymentDate"] != "0000-00-00 00:00:00") {
					if (strtotime($_POST["PaymentDate"]) < strtotime($_POST["CreationDate"])) {
						throw new exception("付款日期不可小於交易日期!");
					}
				}*/
				
				if (!is_numeric($_POST["Total"])) {
					throw new exception("請輸入正確的交易金額!");
				}
				if (!is_numeric($_POST["ClosingTotal"])) {
					throw new exception("請輸入正確的結帳金額!");
				}
				CDbShell::query("SELECT * FROM paymentflow WHERE Type = '".$_POST["PaymentType"]."'"); 
				$PFRow = CDbShell::fetch_array();
				$_PaymentName = $PFRow["Kind"];
				
				if ($_POST["State"] == 0) {
					$_ResultMesg = "交易成功";
				}else {
					$_ResultMesg = "";
				}
				
				CDbShell::query("SELECT L.*, F.FirmCode FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.Sno = '".$_POST["Sno"]."'"); 
				$Row = CDbShell::fetch_array();
				
				CDbShell::query("SELECT FC.* FROM FirmCommission AS FC INNER JOIN PaymentFlow AS PF ON FC.PaymentFlowSno = PF.Sno WHERE FC.FirmSno = ".$Row["FirmSno"]." AND PF.Type = '".$Row["PaymentType"]."' AND FC.Enable = 1 LIMIT 1"); 
				$FCRow = CDbShell::fetch_array();
				
				$_PaymentDate = date("Y-m-d H:i:s", strtotime($_POST["PaymentDate"]));
				switch ($FCRow["Closing"]) {
					case "Day":
						//$ExpectedRecordedDate = date('Y-m-d', strtotime($_PaymentDate ." +".$FCRow["Day"]." day"));
						$Period = date("Y-m-d", strtotime($_PaymentDate));
						$ClosingDate = date("Y-m-d", strtotime($_PaymentDate));
						break;
					case "Week":
						$TodayWeek = Date('w');
						if ($TodayWeek == 0) $TodayWeek = 7;
						//$ExpectedRecordedDate = date("Y-m-d", strtotime($_PaymentDate ."+".(7- $TodayWeek + $FCRow["Day"]). " day"));
						$Period = date('Y-m-d', strtotime($_PaymentDate. " -".($TodayWeek - 1)." day")) . " ~ " . date('Y-m-d',strtotime($_PaymentDate. " +".(7 - $TodayWeek)." day"));
						$ClosingDate = date('Y-m-d', strtotime($_PaymentDate. " +".(7- $TodayWeek)." day"));
						break;
					case "Month":
						//$ExpectedRecordedDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month +".$FCRow["Day"]." day"));
						$Period = date("Y-m-01", strtotime($_PaymentDate)) . " ~ " . date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
						$ClosingDate = date('Y-m-d', strtotime(date("Y-m-01", strtotime($_PaymentDate)) ." +1 month -1 day"));
						break;
				}
				$ExpectedRecordedDate = CommonElement::CountHoliday($ClosingDate, $FCRow["Day"], true);
			
				if ($Row["ExpireDatetime"] == "") $_ExpireDatetime = "0000-00-00 00:00:00";

				$Fee = 0;
				if (floatval($FCRow["FeeRatio"]) > 0){
					$Fee = floatval($_POST["ClosingTotal"]) * floatval($FCRow["FeeRatio"] / 100);
				}
				if ($FCRow["FixedFee"] != 0) {
					$Fee = $Fee + $FCRow["FixedFee"];
				} 

				if (is_numeric($FCRow["MinFee"]) && $FCRow["MinFee"] > 0) {
		
					if ($Fee < floatval($FCRow["MinFee"]))
						$Fee = $FCRow["MinFee"];
				}
				
				if (is_numeric($FCRow["MaxFee"]) && $FCRow["MaxFee"] > 0) {
					
					if ($Fee > floatval($FCRow["MaxFee"]))
						$Fee = $FCRow["MaxFee"];
				}
			
				$field = array("FirmSno", "CashFlowID", "AuthorizeNumber", "OrderID", "CreationDate", "PaymentDate", "PaymentType", "PaymentName", "PaymentCode", "MerProductID", "MerUserID", "MerTradeID", "CardNumber", "VatmAccount", "Total", "ClosingTotal", "Fee", "TransactionDate", "ValidDate", "Period", "ClosingDate", "ExpectedRecordedDate", "ActualRecordedDate", "IP", "ResultMesg", "State", "Remark", "ExpireDatetime");
				$value = array($Row["FirmSno"], $Row["CashFlowID"], $_POST["AuthorizeNumber"], $_POST["OrderID"], $_POST["CreationDate"], $_POST["PaymentDate"], $_POST["PaymentType"], $_PaymentName, $Row["PaymentCode"], $_POST["MerProductID"], $_POST["MerUserID"], $_POST["MerTradeID"], $_POST["CardNumber"], $_POST["VatmAccount"], $_POST["Total"], $_POST["ClosingTotal"], $Fee, $Row["TransactionDate"], $Row["ValidDate"], $Period, $ClosingDate, $ExpectedRecordedDate, $Row["ActualRecordedDate"], $Row["IP"], $_ResultMesg, $_POST["State"], $_POST["Remark"], $_ExpireDatetime);
				CDbShell::insert("ledger", $field, $value);
				
				/*JSModule::BoxCloseJSMessage("成功編輯訂單!","");*/
				$js .= "alert(\"補單成功!\");";							
				$js .= "parent.location.reload();";
		        $js .= "parent.$.jBox.close('user');";	
				echo $js;
				exit;
			
			} catch(Exception $e) {
			   	JSModule::ErrorJSMessage($e->getMessage());
			} 
			
		}else {
			CDbShell::query("SELECT L.*, F.FirmCode FROM Ledger AS L INNER JOIN Firm AS F ON L.FirmSno = F.Sno WHERE L.Sno = '".$_GET["Sno"]."'"); 
			$Row = CDbShell::fetch_array();
			
			CDbShell::query("SELECT * FROM paymentflow GROUP BY Type ORDER BY Type"); 
			while ($PFRow = CDbShell::fetch_array()) {
				$PFHtml[] = $PFRow;
			}
			
			include($this->GetAdmHtmlPath . "IncreaseLedger.html");
		}
	}
	
	public function	__destruct() {

	}
	
}
?>