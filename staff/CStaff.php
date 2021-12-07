<?php 
class CStaff {
	
	var $DB_Table			= "staff";
	var $PageItems			= 10;
	var $GetAdmHtmlPath		= "../adm_html/staff/";
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
            	if ((CSession::getVar("IsChild") == 0 || (CSession::getVar("IsChild") == 1 && array_search("StaffAdd", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				$this->Added();
    			}
    			break;
    		case "Modify":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0 || (CSession::getVar("IsChild") == 1 && array_search("StaffEdit", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				$this->Modify();
    			}
    			break;
    		case "Deletion":
    			$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ((CSession::getVar("IsChild") == 0 || (CSession::getVar("IsChild") == 1 &&  array_search("StaffDel", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
    				self::Deletion();
    			}
    			break;
    		case "Detail":
    			self::Detail();
    			break;
			default:
				if ((CSession::getVar("IsChild") == 0 || (CSession::getVar("IsChild") == 1 && array_search("StaffLayout", CSession::getVar("Purview")) != false)) && CSession::getVar("AdminLevel") <= 2) {
					$this->Manage();
				}
				break;
		}
	}
	
	static function CheckIDNumber($id){
	    //建立字母分數陣列
    $city = array('A'=>1,'I'=>39,'O'=>48,'B'=>10,'C'=>19,'D'=>28,
                  'E'=>37,'F'=>46,'G'=>55,'H'=>64,'J'=>73,'K'=>82,
                  'L'=>2,'M'=>11,'N'=>20,'P'=>29,'Q'=>38,'R'=>47,
                  'S'=>56,'T'=>65,'U'=>74,'V'=>83,'W'=>21,'X'=>3,
                  'Y'=>12,'Z'=>30);
	//檢查身份字號
	    if (!preg_match("/[A-Z][1-2]\d{8}/",$id = strtoupper($id))){
	        return false;
	    } else {
	        //計算總分
	        $total = $city[$id[0]];
	        for($i=1;$i<=8;$i++){
	            $total += $id[$i] * (9 - $i);
	        }
	        //補上檢查碼(最後一碼)
	        $total += $id[9];
	        //檢查比對碼(餘數應為0);
	        return (($total%10 === 0 ));
	    }
	}

	static function VerifyData()
    {
    	/*if (self::CheckDateTime(trim($_POST['ReleaseTime'])) == false)
        {
            throw new exception("請輸入正確宣導日期!");
        }*/
        
        if (strlen(trim($_POST['Number'])) < 2)
        {
            throw new exception("請輸入員工編號!");
        }
        
        if (strlen(trim($_POST['Name'])) < 2)
        {
            throw new exception("請輸入姓名!");
        }
        
        if (self::CheckIDNumber($_POST['IdentityNumber']) == false)
        {
            throw new exception("請輸入正確身份證字號!");
        }
        
        if (self::CheckDateTime($_POST["Birthday"]) == false) {
        	throw new exception("請輸入正確出生日期");
        }
        
        if (strlen(trim($_POST['Address'])) < 6)
        {
            throw new exception("請輸入住家地址!");
        }
        
        if (strlen(trim($_POST['TEL'])) < 8)
        {
            throw new exception("請輸入住家電話!");
        }
        
        if (strlen(trim($_POST['JobTitle'])) < 2)
        {
            throw new exception("請輸入職稱!");
        }
        
        if (preg_match("/^[A-Za-z0-9|\-|\_]{4,20}$/",Trim($_POST["Account"])) == false) {
    		throw new exception("帳號長度最少要4-20個數字或字母!");
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
    		$this->SearchKeyword = " where (main.Name like '%".$Keyword."%' or main.Number like '%".$Keyword."%')";
    	}
    	
    	CDbShell::query("select main.*, dep.DepartmentName from $this->DB_Table as main left join Department as dep on main.DepartmentSno = dep.Sno ". $this->SearchKeyword ." order by main.Sno desc limit ".$nowitem."," . $this->PageItems); 
    	//echo "select Main.*, admin.Department, admin.Name from $this->DB_Table AS Main left join admin on Main.AdminSno = admin.Sno ". $this->SearchKeyword ." order by Main.ReleaseTime desc, Main.Sno desc limit ".$nowitem."," . $this->PageItems;
    	while ($Row = CDbShell::fetch_array()) {
    		$Row["DelLink"] = $_SERVER["PHP_SELF"]."?func=Deletion&Sno=".$Row["Sno"];
    		$Html[] = $Row;
    	}
    	include($this->GetAdmHtmlPath . "Manage.html");
    }
	function Added() {
		
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				self::VerifyData();
				
				$field = array("Number", "Name", "Mobile1","Mobile2","EMail","IdentityNumber","Birthday","Address","TEL","ContactPerson","Relation","ContactTEL","DepartmentSno","JobTitle","TakeOffice","LeaveOffice","Account");
				$value = array($_POST['Number'], $_POST['Name'], $_POST['Mobile1'], $_POST['Mobile2'],$_POST['EMail'], $_POST['IdentityNumber'], $_POST['Birthday'], $_POST['Address'], $_POST['TEL'], $_POST['ContactPerson']
							 , $_POST['Relation'], $_POST['ContactTEL'], $_POST['DepartmentSno'], $_POST['JobTitle'], $_POST['TakeOffice'], $_POST['LeaveOffice'], $_POST['Account']);
				CDbShell::insert($this->DB_Table, $field, $value);
				CDbShell::DB_close();
				
				JSModule::BoxCloseJSMessage("員工資料 新增成功。");
			}else {
				/*$oFCKeditor = new ckeditor();
				$oFCKeditor->BasePath = '../ckeditor/';
				$oFCKeditor->Width = '100%';
				$oFCKeditor->Height = '1000px';
				$oFCKeditor->replace("Detail");*/
				
				CDbShell::query("select * from Department order by Sno");
    			while ($Row = CDbShell::fetch_array()) {
    				$DepRow[] = $Row;
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
	
	function Modify() {
		
		CDbShell::query("select * from $this->DB_Table where Sno = ". $_GET["Sno"]);
    	$Row = CDbShell::fetch_array();
    	
		try {
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				self::VerifyData();
				
				$field = array("Number", "Name", "Mobile1","Mobile2","EMail","IdentityNumber","Birthday","Address","TEL","ContactPerson","Relation","ContactTEL","DepartmentSno","JobTitle","TakeOffice","LeaveOffice","Account");
				$value = array($_POST['Number'], $_POST['Name'], $_POST['Mobile1'], $_POST['Mobile2'],$_POST['EMail'], $_POST['IdentityNumber'], $_POST['Birthday'], $_POST['Address'], $_POST['TEL'], $_POST['ContactPerson']
							 , $_POST['Relation'], $_POST['ContactTEL'], $_POST['DepartmentSno'], $_POST['JobTitle'], $_POST['TakeOffice'], $_POST['LeaveOffice'], $_POST['Account']);
				CDbShell::update($this->DB_Table, $field, $value, "Sno = ". $_GET["Sno"]);
				CDbShell::DB_close();
				
				JSModule::BoxCloseJSMessage("員工資料 修改成功。");
			}
			else {
				
				$oFCKeditor = new ckeditor();
		        $oFCKeditor->BasePath = '../ckeditor/';
		        $oFCKeditor->Width = '100%';
		        $oFCKeditor->Height = '1000px';
		        $oFCKeditor->replace("Detail");
        		
        		CDbShell::query("select * from Department order by Sno");
    			while ($DRow = CDbShell::fetch_array()) {
    				$DepRow[] = $DRow;
    			}
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
		
		JSModule::Message("員工資料 刪除成功。", $_SERVER["PHP_SELF"]);
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