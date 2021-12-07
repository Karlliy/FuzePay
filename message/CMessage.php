<?php 
class CMessage {
	
	public static  $DB_Table			= "message";
	var $PageItems			= 100000;
	var $GetAdmHtmlPath		= "../adm_html/message/";
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
            	if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("MessageAdd", CSession::getVar("Purview")) != false)) {
            		self::Added();
            	}
    			break;
    		case "Deletion":
				$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("MessageDel", CSession::getVar("Purview")) != false)) {
            		self::Deletion();
            	}
    			break;
			default:
				if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && array_search("MessageLayout", CSession::getVar("Purview")) != false)) {
					self::Manage();
				}
				break;
		}
	}
	
	static function VerifyData()
    {
        /*if (strlen(trim($_POST['FirmSno'])) < 2)
        {
            throw new exception("請輸入收件者特店代號！");
        }*/        
        
        /*CDbShell::query("SELECT * FROM Firm WHERE Sno = '".$_POST['FirmSno']."'");
		if (CDbShell::num_rows() == 0) {
			throw new exception("收件者特店代號不存在！");
		}
        if (strlen(trim($_POST["Invoice"])) < 2 ) {
        	throw new exception("請選擇發票資料");
        }*/
        
        if (strlen(trim($_POST["Message"])) < 2 ) {
        	throw new exception("請輸入發送訊息！");
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
    	$AdminLevel = CSession::getVar("AdminLevel");
        $Boss 		= CSession::getVar("Boss");
        
    	$nowitem = $_GET["ipage"] * $this->PageItems;
    	
    	//$Pages = self::AllPages();
    	$PageBar = self::showPageBar($_GET["ipage"], $Pages, "");
    	
    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    	$Keyword = (strlen(trim($_POST["Keyword"])) > 0) ? $_POST["Keyword"] : $_GET["Keyword"];
	    	if (strlen(trim($Keyword)) > 0 ) {
	    		if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    		else $this->SearchKeyword .= " AND ";
	    		$this->SearchKeyword .= " (M.Message like '%".$Keyword."%' OR ";
	    		
	    		if ($_POST["Tab"] == "Receiver") {
	    			$this->SearchKeyword .= " M.ToMember like '%".$Keyword."%')";
	    		} else {
	    			$this->SearchKeyword .= " M.FromMember like '%".$Keyword."%')";
	    		}
	    	}
	    	
	    	if (strlen($this->SearchKeyword) == 0) $this->SearchKeyword .= " WHERE ";
	    	else $this->SearchKeyword .= " AND ";
	    	
	    	if ($_POST["Tab"] == "Receiver") {
	    		if (CSession::getVar("FirmSno") == "-1") {
	    			$this->SearchKeyword .= " M.FromHide = 0 AND M.FromMemberSno = ". -1;
	    		}else {
	    			$this->SearchKeyword .= " M.FromHide = 0 AND M.FromMemberSno = ". CSession::getVar("FirmSno");
	    		}
	    	}else {
	    		if (CSession::getVar("FirmSno") == "-1") {
	    			$this->SearchKeyword .= " M.ToHide = 0 AND M.ToMemberSno = ". -1;
	    		}else {
	    			$this->SearchKeyword .= " M.ToHide = 0 AND M.ToMemberSno = ". CSession::getVar("FirmSno");
	    		}
	    	}
	    	/*if ($AdminLevel == 3) {
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
	    	}*/
	    	$RowIndex = 1;
	    	CDbShell::query("SELECT M.*, F.Name FROM ".self::$DB_Table. " AS M LEFT JOIN firm AS F ON F.Sno = M.ToMemberSno " . $this->SearchKeyword ." ORDER BY M.CreateDate DESC LIMIT ".$nowitem."," . $this->PageItems); 
	    	//$Layout[0] .= "SELECT M.*, F.Name FROM ".self::$DB_Table. " AS M LEFT JOIN firm AS F ON F.Sno = M.ToMemberSno " . $this->SearchKeyword ." ORDER BY M.CreateDate LIMIT ".$nowitem."," . $this->PageItems;
	    	while ($Row = CDbShell::fetch_array()) {
	    		
	    		$Row["DelLink"] = $_SERVER["PHP_SELF"]."?func=Deletion&Sno=".$Row["Sno"]."&Tab=".$_POST["Tab"];
	    		
	    		$Layout[0] .= "<tr>";
                $Layout[0] .= "<td class=\"nowrap\">". $RowIndex."</td>";
                $Layout[0] .= "<td class=\"nowrap\">".$Row["CreateDate"]."</td>";
                $Layout[0] .= "<td class=\"nowrap\">".(($_POST["Tab"] == "Receiver") ? $Row["ToMember"] : $Row["FromMember"])."</td>";
                $Layout[0] .= "<td class=\"text-left\">".$Row["Message"]."</td>";
                $Layout[0] .= "<td class=\"nowrap\">";
                if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 &&  is_numeric(array_search("MessageDel", CSession::getVar("Purview"))))) {
                	$Layout[0] .= "<a href=\"javascript:;\" class=\"btn btn-info btn-small MCaseFa\" data-confirm onclick=\"new jBox('', {content: document.location='".$Row["DelLink"] ."', color: 'green', attributes: {y: 'bottom'}})\"><i class=\"icon-remove\"></i> 刪除</a></td>";
                }
                $Layout[0] .= "</tr>";
                $RowIndex++;
	    	}
	    	
	    	CDbShell::query("UPDATE ".self::$DB_Table. " SET IsRead = 1 WHERE FromMemberSno = ". CSession::getVar("FirmSno") ." AND IsRead = 0"); 
	    	
	    	
	    	CDbShell::DB_close();
				
			echo json_encode($Layout);
			exit;
		}
    	include($this->GetAdmHtmlPath . "Manage.html");
    }
    
	function Added() {
		
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				self::VerifyData();
				
				CDbShell::query("SELECT * FROM Firm WHERE Sno = '".$_POST['FirmSno']."'");
				$Row = CDbShell::fetch_array(); 
				$_FromMemberSno = $Row["Sno"];
				$_FromMember = $Row["Name"];
				
				if (CSession::getVar("FirmSno") == "-1") {
					$_ToMember = "總管理者";
				}else {
					CDbShell::query("SELECT * FROM Firm WHERE Sno = '".CSession::getVar("FirmSno") ."'");
					$Row = CDbShell::fetch_array(); 
					$_ToMember = $Row["Name"];
					
					$_FromMember = "總管理者";					
					$_FromMemberSno = -1;
				}
				
				$field = array("ToMemberSno", "ToMember", "FromMemberSno", "FromMember", "Message");
				$value = array(CSession::getVar("FirmSno"), $_ToMember, $_FromMemberSno, $_FromMember, $_POST['Message']);
				CDbShell::insert(self::$DB_Table, $field, $value);
				CDbShell::DB_close();
				
				JSModule::BoxCloseJSMessage("訊息發送成功。");
			}else {
				/*$oFCKeditor = new ckeditor();
				$oFCKeditor->BasePath = '../ckeditor/';
				$oFCKeditor->Width = '100%';
				$oFCKeditor->Height = '1000px';
				$oFCKeditor->replace("Detail");*/
				
				if (CSession::getVar("FirmSno") == "-1") {
					CDbShell::query("SELECT * FROM Firm ORDER BY Sno DESC");
					while ($Row = CDbShell::fetch_array()) {
						$Row["Name"];
						$Row["Sno"];
						
						$FirmHtml[] = $Row;
					} 
				}else {
					$Row["Name"] = "總管理者";
					$Row["FromSno"] = -1;
					$FirmHtml[] = $Row;
				}

				include($this->GetAdmHtmlPath . "AddedNode.html");
			}
		} catch(Exception $e) {
		   	JSModule::ErrorJSMessage($e->getMessage());
		} 
		/*finally {
		   	CDbShell::DB_close();
		}*/
	}
		
	function Deletion() {
		
		//CDbShell::connect();
		if ($_GET["Tab"] == "Send") {
			$field = array("ToHide");
			$value = array("1");
		}else if ($_GET["Tab"] == "Receiver") {
			$field = array("FromHide");
			$value = array("1");
		}
		CDbShell::update(self::$DB_Table, $field, $value, "	Sno = '". $_GET["Sno"] ."'");
		
		CDbShell::DB_close();
		
		JSModule::Message("郵件刪除成功。", $_SERVER["PHP_SELF"]);
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
	
	function NotReadCount() {
		@CDbShell::connect();
		CDbShell::query("SELECT Count(Sno) AS Number  FROM ".self::$DB_Table. " WHERE FromHide = 0 AND IsRead = 0 AND FromMemberSno = ". CSession::getVar("FirmSno") .""); 
	    $Row = CDbShell::fetch_array();
	    
	    return $Row["Number"];
	}
	
	public function	__destruct() {

	}
	
}

/*class Cryptographic {
	static $iv = "M#yC!ash";
	static $key = "6BAE2CF100974F9D88AE";

	static function encrypt($str) {

		$td = mcrypt_module_open(MCRYPT_TRIPLEDES, '', MCRYPT_MODE_CBC, self::$iv);
		
		mcrypt_generic_init($td, self::$key, self::$iv);
		$encrypted = mcrypt_generic($td, $str);

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return bin2hex($encrypted);
	}
	static function hex2bin($hexdata) {
		$bindata = '';

		for ($i = 0; $i < strlen($hexdata); $i += 2) {
		    $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
		}

		return $bindata;
    }
}*/
?>