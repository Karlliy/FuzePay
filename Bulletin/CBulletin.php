<?php 
class CBulletin {
	
	var $DB_Table			= "bulletin";
	var $PageItems			= 10;
	var $GetAdmHtmlPath		= "../adm_html/bulletin/";
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
            	//echo CSession::getVar("Purview");
            	if ((CSession::getVar("IsChild") == 0 && CSession::getVar("AdminLevel") <= 2) || (CSession::getVar("IsChild") == 1 && array_search("BulletinAdd", CSession::getVar("Purview")) != false)) {
    				$this->Added();
    			}
    			break;
    		case "Modify":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0 && CSession::getVar("AdminLevel") <= 2) || (CSession::getVar("IsChild") == 1 && array_search("BulletinEdit", CSession::getVar("Purview")) != false)) {
    				$this->Modify();
    			}
    			break;
    		case "Deletion":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0 && CSession::getVar("AdminLevel") <= 2) || (CSession::getVar("IsChild") == 1 && array_search("BulletinDel", CSession::getVar("Purview")) != false)) {
    				self::Deletion();
    			}
    			break;
    		case "Detail":
    			self::Detail();
    			break;
			default:
				//self::message("123");
				$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
				if ((CSession::getVar("IsChild") == 0) || (CSession::getVar("IsChild") == 1 && !is_null(array_search("BulletinLayout", CSession::getVar("Purview"))))) {
					$this->Manage();
				}
				break;
		}
	}
	
	static function VerifyData()
    {
    	if (self::CheckDateTime(trim($_POST['ReleaseTime'])) == false)
        {
            throw new exception("請輸入正確日期!");
        }
        
        if (strlen(trim($_POST['Heading'])) < 2)
        {
            throw new exception("請輸入最新消息標題!");
        }
        
        if (strlen(trim($_POST['Detail'])) < 6)
        {
            throw new exception("請輸入最新消息內容!");
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
    	$Keyword = (strlen(trim($_POST["Keyword"])) > 0) ? $_POST["Keyword"] : $_GET["Keyword"];
    	if (strlen(trim($Keyword)) > 0 ) {
    		$this->SearchKeyword = " where (Heading like '%".$Keyword."%')";
    	}
    	
    	CDbShell::query("select * from $this->DB_Table ". $this->SearchKeyword ." order by ReleaseTime desc, Sno desc limit ".$nowitem."," . $this->PageItems); 
    	//echo "select Main.*, admin.Department, admin.Name from $this->DB_Table AS Main left join admin on Main.AdminSno = admin.Sno ". $this->SearchKeyword ." order by Main.ReleaseTime desc, Main.Sno desc limit ".$nowitem."," . $this->PageItems;
    	while ($Row = CDbShell::fetch_array()) {
    		$Row["DelLink"] = $_SERVER["PHP_SELF"]."?func=Deletion&Sno=".$Row["Sno"];
    		$Html[] = $Row;
    	}
    	$RefundNumber = CLedger::GetRefundNumber();
    	if ($RefundNumber > 0) {
    		$$RefundUrl = "<a href='../ledger/admin.php?attr=Refund'>";
    	}
    	include($this->GetAdmHtmlPath . "Manage.html");
    }
	function Added() {
		
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				self::VerifyData();
				
				$field = array("AdminSno", "Heading","Detail","ReleaseTime");
				$value = array($this->AdminSno, $_POST['Heading'], Stripslashes($_POST['Detail']), $_POST['ReleaseTime']);
				CDbShell::insert($this->DB_Table, $field, $value);
				CDbShell::DB_close();
				
				JSModule::BoxCloseJSMessage("最新消息 新增成功。");
			}else {
				$oFCKeditor = new ckeditor();
				$oFCKeditor->BasePath = '../ckeditor/';
				$oFCKeditor->Width = '100%';
				$oFCKeditor->Height = '1000px';
				$oFCKeditor->replace("Detail");

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
				
				$field = array("Heading","Detail","ReleaseTime");
				$value = array($_POST['Heading'], Stripslashes($_POST['Detail']), $_POST['ReleaseTime']);
				CDbShell::update($this->DB_Table, $field, $value, "Sno = ". $_GET["Sno"]);
				CDbShell::DB_close();
				
				
				JSModule::BoxCloseJSMessage("最新消息 修改成功。");
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
		
		JSModule::Message("最新消息 刪除成功。", $_SERVER["PHP_SELF"]);
	}
	
	function Detail() {
		try {
			CDbShell::query("SELECT * FROM $this->DB_Table WHERE Sno = ". $_GET["Sno"]);
	    	$Row = CDbShell::fetch_array(); 
        
			include($this->GetAdmHtmlPath . "DetailNode.html");
		} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
		/*finally {
		   CDbShell::DB_close();
		}*/
	}
	
	static function ToHome() {
		$db = new CDbShell();  
		//echo "select Main.*, admin.Department, admin.Name from ".self::$DB_Table." AS Main left join admin on Main.AdminSno = admin.Sno order by Main.ReleaseTime desc, Main.Sno desc limit 0, 5";
		CDbShell::query("select Main.*, admin.Department, admin.Name from bulletin AS Main left join admin on Main.AdminSno = admin.Sno order by Main.ReleaseTime desc, Main.Sno desc limit 0, 5"); 
    	while ($Row = CDbShell::fetch_array()) {
    		$Row["DelLink"] = $_SERVER["PHP_SELF"]."?func=Deletion&Sno=".$Row["Sno"];
    		$Html[] = $Row;
    	}
    	
    	return $Html;
	
	}
	public function	__destruct() {

	}
	
}
?>