<?php 
class Cadmin
{
	var $GetAdmHtmlPath		= "../adm_html/admin/";
	var $PageItems			= 20;
	public function	__construct () {		
		$db = new CDbShell();
		$Session = new CSession;
		$JSModule = new JSModule();
		$this->AdminSno = $Session->GetVar("AdminSno");
		//echo "AdminSno".$this->AdminSno;
	}
	
	function is_logined()
    {
        $m_id = CSession::getVar("admin_login");
        $m_pw = CSession::getVar("admin_password");
        if (!$m_id || !$m_pw || $m_id == "" || $m_pw == "") return 0;
       	
        CDbShell::query("SELECT * FROM admin WHERE Account='".$m_id."' and Password='".md5($m_pw)."'");
        //return "select * from admin where Account='".$m_id."' and Password='".md5($m_pw)."'";
        if (CDbShell::num_rows())
        {
        	$row = CDbShell::fetch_array();
        	/*if ($row['LastChangePassword'] <= date("Y-m-d H:i:s")) {
        		return 1;
			}*/

			if ($row["ParentSno"] != 0) {
				CDbShell::query("SELECT * FROM admin WHERE Sno ='".$row["ParentSno"]."'");
				$fatherrow = CDbShell::fetch_array();

				$PermitIP = $fatherrow['PermitIP'];
			}else {
				$PermitIP = $row["PermitIP"];
			}

			if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   
				$myip = $_SERVER['REMOTE_ADDR'];   
			} else {   
				$myip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);   
				$myip = $myip[0];   
			}
			
			//echo strrpos($myip, '.');
			$FrontIP = substr($myip, 0, strrpos($myip, '.'));
			$SectionIP = substr($myip, strrpos($myip, '.') + 1);
			
			if ($PermitIP != "") {
				$_PermitIP = mb_split("##", $PermitIP);
				if (!is_numeric(array_search($myip, $_PermitIP))) {
					return 2;
					exit;
				}
			}
        	if ($row['Boss'] == 1) {
        		CSession::setVar("Boss", "1");
        		CSession::setVar("AdminLevel", "1");
        		CSession::setVar("AdminSno", $row["Sno"]);
        		CSession::setVar("AdminName", $row["Name"]);
        		CSession::setVar("FirmSno", "-1");
        		CSession::setVar("IsChild", 0);
        		CSession::setVar("Purview", " ");
        		CSession::setVar("Country", "Boss");
        		return 1;
        	}else {
        		$allow =  true;
				
				CDbShell::query("SELECT * FROM firm WHERE Sno ='".$row["FirmSno"]."'");
				$frow = CDbShell::fetch_array();
				
				if ($allow == true ) {
	        		CSession::setVar("Boss", "0");
	        		CSession::setVar("AdminLevel", $row["AdminLevel"]);
	        		CSession::setVar("AdminSno", $row["Sno"]);
	        		CSession::setVar("AdminName", $row["Name"]);
	        		CSession::setVar("FirmSno", $row["FirmSno"]);
	        		CSession::setVar("Country", $frow["Country"]);
	        		
	        		if ($row["ParentSno"] != 0) {
	        			CSession::setVar("IsChild", 1);
	        			CSession::setVar("Purview", json_decode($row["Purview"], true));
	        			
	        			if (trim($row["Purview"]) == "") {
			    			CSession::ClearVar("admin_login");
			        		CSession::ClearVar("admin_password");
			        		CSession::ClearAll();
			        		
			        		return 3;
			    		}
	        		}else {
	        			CSession::setVar("IsChild", 0);
	        		}	        		
	        		return 1;
        		}else {
        			CSession::clearAll();
        			
        			return 2;
        			
        		}
        		/*if (isset($_POST['recurit'])) $recurit = $_POST['recurit'];
        		else $recurit = $sess->getVar("Recurit");        	
        			
        		$db->query("select * from admin_command where Account='".$m_id."' and Recurit='".$recurit."'");        		
        		if ($db->num_rows())
        		{
        			$row2 = $db->fetch_array();
        			if ($row["AdminLevel"] == 0) {
        				$db->query("select * from during_date where Recurit = '".$recurit."' AND StartDate <='".$d->today()."' and EndStart >= '".$d->today()."'");
	        			if (!$db->num_rows()) {
	        				return 3;
	        			}
	        		}
        		
	        		$sess->setVar("AdminLevel", $row["AdminLevel"]);
		        	$sess->setVar("Recurit", $row2['Recurit']);
		        	$sess->setVar("CommandArray", $row2['CommandArray']);
		        	return 1;
		        }else {
		        	return 2;
		        }*/
	        }
        	        	
        }
        
        return 0;
    }
    
    function AdminList()
    {
    	$Keyword = (strlen(trim($_POST["Keyword"])) > 0) ? $_POST["Keyword"] : $_GET["Keyword"];
    	
    	$db = new CDbShell();
    	if (strlen(trim($Keyword)) > 0 ) {
    		$db->query("SELECT Count(admin.Sno) as num FROM admin INNER JOIN firm ON admin.FirmSno = firm.Sno WHERE admin.Boss = 0 AND firm.FirmCode = '".$Keyword ."'");
    	}else {
    		$db->query("SELECT Count(Sno) as num FROM admin WHERE Boss = 0 ");
    	}
    	$row = $db->fetch_array();
    	$num = $row["num"];
    	if (strlen(trim($Keyword)) > 0 ) {
    		$Result1 = CDbShell::query("SELECT admin.* FROM admin INNER JOIN firm ON admin.FirmSno = firm.Sno WHERE admin.Boss = 0 AND admin.ParentSno = 0 AND firm.FirmCode = '".$Keyword ."' ORDER BY admin.Sno DESC LIMIT 0," . $this->PageItems);
    	}else {
    		$Result1 = CDbShell::query("SELECT * FROM admin WHERE Boss = 0 AND ParentSno = 0 ORDER BY Sno DESC LIMIT 0," . $this->PageItems);
    	}
		while ($row = CDbShell::fetch_array($Result1))
    	{
    		if ($row["AdminLevel"] == 2) {
    			$row["Kind"] = "一般管理者";
    		}else if ($row["AdminLevel"] == 3) {
    			$Result2 = CDbShell::query("SELECT * FROM firm WHERE Sno = '". $row['FirmSno'] ."'");
		        $FRow = CDbShell::fetch_array($Result2);
		        $row["Kind"] = "廠商管理者"."(".$FRow["FirmCode"].")";
    		}
    		$row["DelLink"] = $_SERVER['PHP_SELF'] . "?func=DelAdmin&Sno=".$row["Sno"];
    		$Html[] = $row;
    	}
    	
    	include("../adm_html/admin/AdminList.html");
    }
    
    function GetAdminList()
    {
    	$nowitem = $_POST["index"] * $this->PageItems;
    	$i = $nowitem +1;
    	unset($Data);
    	$db = new CDbShell();
    	$Result1 = $db->query("SELECT * FROM admin WHERE Boss = 0 AND ParentSno = 0 ORDER BY Sno DESC LIMIT ".$nowitem."," . $this->PageItems);
		while ($row = $db->fetch_array($Result1))
    	{
    		if ($row["AdminLevel"] == 2) {
    			$row["Kind"] = "一般管理者";
    		}else if ($row["AdminLevel"] == 3) {
    			$Result2 = CDbShell::query("SELECT * FROM firm WHERE Sno = '". $row['FirmSno'] ."'");
		        $FRow = CDbShell::fetch_array($Result2);
		        $row["Kind"] = "廠商管理者"."(".$FRow["FirmCode"].")";
    		}
    		$row["DelLink"] = $_SERVER['PHP_SELF'] . "?func=DelAdmin&Sno=".$row["Sno"];
    		//$Data .= "<tbody id='Store_SysInfoList'>";
            $Data .= "<tr>";
            $Data .= "<td class='nowrap'>". $i."</td>";
            $Data .= "<td class='nowrap'>". $row['Kind'] ."</td>";
            $Data .= "<td class='nowrap'>". $row['Account']."</td>";
            $Data .= "<td class='nowrap'>". $row['Name']."</td>";
            $Data .= "<td class='nowrap'><a href='javascript:;' class='btn btn-info btn-small' id='CaseMatchMem' onclick=\"javascript:jBox2('".$_SERVER[' PHP_SELF ']."?func=ModifyAdmin&Sno=". $row['Sno']."');\"><i class='icon-edit'></i> 編修</a> <a href=\"javascript:;\" class=\"btn btn-info btn-small MCaseFa\" data-confirm='您確定要刪除' onclick=\"new jBox('Notice', {content: document.location='". $row['DelLink']."', color: 'green', attributes: {y: 'bottom'}})\"><i class=\"icon-remove\"></i> 刪除</a></td>";
            $Data .= "</tr>";
            //$Data .= "</tbody>";
    		$i++;
    	}
    	echo $Data;
    }
    
    static function VerifyAdminData()
    {
    	if ($_POST['AdminLevel'] == 3) {
	        if (strlen(trim($_POST['FirmCode'])) < 2)
	        {
	            throw new exception("請輸入特店代號!");
	        }
	        
	        CDbShell::query("SELECT * FROM firm WHERE FirmCode = '". $_POST['FirmCode'] ."'");
	        if (CDbShell::num_rows() == 0) {
	        	throw new exception("此特店代號不存在!");
	        }
	        
	        $Row = CDbShell::fetch_array();
	        
	        CDbShell::query("SELECT * FROM admin WHERE FirmSno = '". $Row['Sno'] ."'");
	        if (CDbShell::num_rows() != 0) {
	        	throw new exception("此廠商已經有建立管理帳號!");
	        }
        }
        
        if (preg_match("/^[A-Za-z0-9|\-|\_]{4,20}$/",Trim($_POST["Account"])) == false) {
    		throw new exception("帳號長度最少要4個數字或字母!");
    	}
    	
    	CDbShell::query("select Sno from admin where Account = '".Trim($_POST["Account"])."'");
		//CDbShell::fetch_array();
		if (CDbShell::num_rows() >= 1) {
			throw new exception("您輸入的帳號已經存在!");
		}
    	
        if (preg_match("/^[A-Za-z0-9]{4,20}$/",$_POST["Password"]) == false) {
    		throw new exception("新密碼長度最少要4個數字或字母!");
    	}
    	
    	if (preg_match("/".$_POST["Password"]."/",$_POST["Password2"]) == false) {
    		throw new exception("密碼與確認密碼不符合!");
    	}
    	
    	if (strlen(trim($_POST['Name'])) < 2)
        {
            throw new exception("請輸入姓名!");
        }
    }
    
    function AddedAdmin()
    {
    	$db = new CDbShell();
    	$sess = new CSession;
    	
    	try {		    
	    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    		self::VerifyAdminData();
	    		$_FirmSno = 0;
	    		if ($_POST['AdminLevel'] == 3) {
	    			CDbShell::query("SELECT * FROM firm WHERE FirmCode = '". $_POST['FirmCode'] ."'");
			        $Row = CDbShell::fetch_array();
			        $_FirmSno = $Row["Sno"];
	    		}
				$field = array("Account","Password","FirmSno","Name","AdminLevel");
				$value = array($_POST["Account"],md5($_POST["Password"]),$_FirmSno,$_POST["Name"],$_POST["AdminLevel"]);
		    	CDbShell::insert("admin", $field, $value);
		    	
		    	CDbShell::DB_close();				
				
				JSModule::BoxCloseJSMessage("管理者新增成功。", "?func=AdminList");
	    		
	    	}else {
	    		$Result1 = CDbShell::query("select * from franchisee order by Sno");
			    while ($Row = CDbShell::fetch_array($Result1))
			    { 
			    	if ($Row["AdminLevel"] == 2) {
		    			$Row["Kind"] = "一般管理者";
		    		}else if ($row["AdminLevel"] == 3) {
		    			$Result2 = CDbShell::query("SELECT * FROM firm WHERE Sno = '". $row['FirmSno'] ."'");
				        $FRow = CDbShell::fetch_array($Result2);
				        $Row["Kind"] = "廠商管理者";
				        $Row["FirmCode"] = $FRow["FirmCode"];
		    		}
    		
			    	$FranRow[] = $Row;
			    }
	    
		    	$db->query("select * from recurit order by Sno desc ");
				while ($row = $db->fetch_array())
		    	{
		    		$recuritlist[] = $row;
		    	}
		    	include("../adm_html/admin/AddedAdmin.html");
	    	}
    	} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
    }
    
    static function VerifyModifyAdminData()
    {
        
    	if ($_POST["IsChange"] == "1" || Trim($_POST["Password"]) != "") {
	        if (preg_match("/^[A-Za-z0-9]{4,20}$/",$_POST["Password"]) == false) {
	    		throw new exception("新密碼長度最少要4個數字或字母!");
	    	}
	    	
	    	if (strcmp($_POST["Password"], $_POST["Password2"]) != 0) {
	    		throw new exception("密碼與確認密碼不符合!");
	    	}
    	}
    	
    	if (strlen(trim($_POST['Name'])) < 2)
        {
            throw new exception("請輸入姓名!");
        }
    }
    
    function ModifyAdmin()
    {
    	try {
	    	$db = new CDbShell();
	    	$db2 = new CDbShell();
	    	$sess = new CSession;
	    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    		self::VerifyModifyAdminData();
		    	
		    	$field = array("Name", "PermitIP");
				$value = array($_POST["Name"], $_POST["PermitIP"]);
		    	CDbShell::update("admin", $field, $value, "Sno = ". $_GET["Sno"]);
		    	
		    	if ($_POST["IsChange"] == "1" || Trim($_POST["Password"]) != "") {
		    		
			    	$field = array("Password");
					$value = array(md5($_POST["Password"]));
			    	CDbShell::update("admin", $field, $value, "Sno = ". $_GET["Sno"]);
		    	}
		    	
	            if ($_POST["IsChange"] == "1"|| Trim($_POST["Password"]) != "") {
	            	JSModule::BoxCloseJSMessage("管理者 資料及變更密碼成功", "?func=AdminList");
	            }else {
	            	JSModule::BoxCloseJSMessage("管理者 修改成功。", "?func=AdminList");
	            }
	    	}else {
	    		
	    		CDbShell::query("select * from franchisee order by Sno");
			    while ($Row = CDbShell::fetch_array())
			    { 
			    	$FranRow[] = $Row;
			    }
			    
	    		$Result1 = CDbShell::query("select * from admin where Sno = '".$_GET["Sno"]."' ");
	    		$AdminRow = CDbShell::fetch_array($Result1);
	    		if ($AdminRow["AdminLevel"] == 2) {
	    			$AdminRow["Kind"] = "一般管理者";
	    		}else if ($AdminRow["AdminLevel"] == 3) {
	    			$Result2 = CDbShell::query("SELECT * FROM firm WHERE Sno = '". $AdminRow['FirmSno'] ."'");
			        $FRow = CDbShell::fetch_array($Result2);
			        $AdminRow["Kind"] = "廠商管理者";
			        $AdminRow["FirmCode"] = $FRow["FirmCode"];
	    		}
	    		
	    		$db->query("select * from admin_command where login = '".$html["login"]."' ");
	    		while ($row = $db->fetch_array())
		    	{
		    		$html['Recurit'] .= $row['Recurit']." : ";
		    		
		    		$SplitArray = mb_split("\,",$row['CommandArray']);
		    		//echo "aa".$row['CommandArray'];
		    		if (Count($SplitArray) == 1) {
		    			$db2->query("select * from institute where Recurit = '".$row["Recurit"]."' and InstituteCode = '".$row['CommandArray']."'");
		    			$row2 = $db2->fetch_array();
		    			$html['Recurit'] .= $row2["InstituteName"];
		    		}else {
			    		foreach ((array)$SplitArray as $key => $value) {
			    			$db2->query("select * from institute where Recurit = '".$row["Recurit"]."' and InstituteCode = '".$value."'");
			    			$row2 = $db2->fetch_array();
			    			$html['Recurit'] .= $row2["InstituteName"].",";
			    		}
			    	}
		    		
		    		$html['Recurit'] .= "<br />";
		    	}
		    	
	    		$db->query("select * from recurit order by Sno desc ");
				while ($row = $db->fetch_array())
		    	{
		    		$recuritlist[] = $row;
		    	}
		    	include("../adm_html/admin/ModifyAdmin.html");
	    	}
    	} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
    }
    
    function DelAdmin() {
    	CDbShell::query("delete from admin where Sno = ". $_GET["Sno"]);
		
		JSModule::Message("使用者帳號 刪除成功。", $_SERVER["PHP_SELF"]."?func=AdminList");
    }
    
    function ChildAdminList()
    {
    	$db = new CDbShell();
    	$db->query("SELECT COUNT(Sno) As num FROM admin WHERE Boss = 0 AND ParentSno = ".CSession::getVar("AdminSno"));
    	$row = $db->fetch_array();
    	$num = $row["num"];
    	
    	$Result1 = CDbShell::query("SELECT * FROM admin WHERE Boss = 0 AND ParentSno = ".CSession::getVar("AdminSno") ." ORDER BY Sno DESC LIMIT 0," . $this->PageItems);
		while ($row = CDbShell::fetch_array($Result1))
    	{
    		if ($row["AdminLevel"] == 2) {
    			$row["Kind"] = "一般管理者";
    		}else if ($row["AdminLevel"] == 3) {
    			$Result2 = CDbShell::query("SELECT * FROM firm WHERE Sno = '". $row['FirmSno'] ."'");
		        $FRow = CDbShell::fetch_array($Result2);
		        $row["Kind"] = "廠商管理者"."(".$FRow["FirmCode"].")";
    		}
    		$row["DelLink"] = $_SERVER['PHP_SELF'] . "?func=DelChildAdmin&Sno=".$row["Sno"];
    		$Html[] = $row;
    	}
    	
    	include("../adm_html/admin/ChildAdminList.html");
    }
    
    static function VerifyChildAdminData()
    {
        
        if (preg_match("/^[A-Za-z0-9|\-|\_]{4,20}$/",Trim($_POST["Account"])) == false) {
    		throw new exception("帳號長度最少要4個數字或字母!");
    	}
    	
    	CDbShell::query("select Sno from admin where Account = '".Trim($_POST["Account"])."'");
		//CDbShell::fetch_array();
		if (CDbShell::num_rows() >= 1) {
			throw new exception("您輸入的帳號已經存在!");
		}
    	
        if (preg_match("/^[A-Za-z0-9]{4,20}$/",$_POST["Password"]) == false) {
    		throw new exception("新密碼長度最少要4個數字或字母!");
    	}
    	
    	if (preg_match("/".$_POST["Password"]."/",$_POST["Password2"]) == false) {
    		throw new exception("密碼與確認密碼不符合!");
    	}
    	
    	if (strlen(trim($_POST['Name'])) < 2)
        {
            throw new exception("請輸入姓名!");
        }
    }
    
    function AddedChildAdmin()
    {
    	$db = new CDbShell();
    	$sess = new CSession;
    	
    	try {		    
	    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    		self::VerifyChildAdminData();	    		
	    		
	    		//$_Purview = json_encode($_POST['Purview']);
	    		
				$field = array("ParentSno", "Account","Password","FirmSno","Name","AdminLevel");
				$value = array(CSession::getVar("AdminSno"), $_POST["Account"], md5($_POST["Password"]), CSession::getVar("FirmSno"), $_POST["Name"], CSession::getVar("AdminLevel"));
		    	CDbShell::insert("admin", $field, $value);
		    	
		    	CDbShell::DB_close();				
				
				JSModule::BoxCloseJSMessage("子帳號新增成功。", "admin.php?func=ChildAdminList");
	    		
	    	}else {
		    	include("../adm_html/admin/AddedChildAdmin.html");
	    	}
    	} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
    }
    
    function ModifyChildAdmin()
    {
    	try {
	    	$db = new CDbShell();
	    	$db2 = new CDbShell();
	    	$sess = new CSession;
	    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    		self::VerifyModifyAdminData();
	    		
	    		//$_Purview = json_encode($_POST['Purview']);
		    	//echo $_Purview;
		    	//exit;
		    	$field = array("Name");
				$value = array($_POST["Name"]);
		    	CDbShell::update("admin", $field, $value, "Sno = ". $_GET["Sno"]);
		    	
		    	if ($_POST["IsChange"] == "1" || Trim($_POST["Password"]) != "") {
		    		
			    	$field = array("Password");
					$value = array(md5($_POST["Password"]));
			    	CDbShell::update("admin", $field, $value, "Sno = ". $_GET["Sno"]);
		    	}
		    	
	            if ($_POST["IsChange"] == "1"|| Trim($_POST["Password"]) != "") {
	            	JSModule::BoxCloseJSMessage("子帳號 資料及變更密碼成功", "?func=ChildAdminList");
	            }else {
	            	JSModule::BoxCloseJSMessage("子帳號 修改成功。", "?func=ChildAdminList");
	            }
	    	}else {
	    		
	    		$Result1 = CDbShell::query("SELECT * FROM admin WHERE Sno = '".$_GET["Sno"]."' ");
	    		$AdminRow = CDbShell::fetch_array($Result1);
	    		$Purview = json_decode($AdminRow["Purview"], true);
	    		//echo "a".is_numeric(array_search("BulletinLayout" , $Purview));
	    		//print_r($Purview);
		    	include("../adm_html/admin/ModifyChildAdmin.html");
	    	}
    	} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
    }
    
    function ChildPurview()
    {
    	try {
	    	$db = new CDbShell();
	    	$db2 = new CDbShell();
	    	$sess = new CSession;
	    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    		//self::VerifyModifyAdminData();
	    		
	    		$_Purview = json_encode($_POST['Purview']);
		    	//echo $_Purview;
		    	//exit;
		    	$field = array("Purview");
				$value = array($_Purview);
		    	CDbShell::update("admin", $field, $value, "Sno = ". $_GET["Sno"]);
		    	
		    	JSModule::BoxCloseJSMessage("子帳號權限設定成功。", "?func=ChildAdminList&attr=Purview");
	    	}else {
	    		
	    		$Result1 = CDbShell::query("SELECT * FROM admin WHERE Sno = '".$_GET["Sno"]."' ");
	    		$AdminRow = CDbShell::fetch_array($Result1);
	    		$Purview = json_decode($AdminRow["Purview"], true);
	    		//echo "SELECT * FROM admin WHERE Sno = '".$_GET["Sno"]."' ";
	    		//echo "a".is_numeric(array_search("BulletinLayout" , $Purview));
	    		//print_r($Purview);
	    		//echo "a".array_search("BulletinLayout" , $Purview);
		    	include("../adm_html/admin/ChildPurview.html");
	    	}
    	} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
    }
    
    function DelChildAdmin() {
    	CDbShell::query("DELETE FROM admin WHERE Sno = ". $_GET["Sno"]);
		
		JSModule::Message("子帳號 刪除成功。", $_SERVER["PHP_SELF"]."?func=ChildAdminList");
    }
    
    function LoginLogList()
    {
    	
    	CDbShell::query("select * from admin where Boss = 0 order by CONVERT(Name USING big5)");
			
		while ($Row = CDbShell::fetch_array()) {
			$Officer[] = $Row;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			try {
				if (self::CheckDateTime(trim($_POST['Sdate'])) == false)
		        {
		            throw new exception("請輸入正確查詢開始日期!");
		        }
		        
		        if (self::CheckDateTime(trim($_POST['Edate'])) == false)
		        {
		            throw new exception("請輸入正確查詢結束日期!");
		        }
		        
		    	/*$db = new CDbShell();
		    	$db->query("select count(Sno) as num from admin where Boss = 0 ");
		    	$row = $db->fetch_array();
		    	$num = $row["num"];*/
		    	
		    	CDbShell::query("select loginlog.*,admin.Name from loginlog left join admin on loginlog.AdminSno = admin.Sno where loginlog.AdminSno = ".$_POST["CaseOfficer"]." AND loginlog.LogintTime >= '".$_POST['Sdate']." 00:00:00' AND loginlog.LogintTime <= '".$_POST['Edate']." 23:23:59' order by LogintTime");
		    	while ($row = CDbShell::fetch_array())
		    	{
		    		//$row["DelLink"] = $_SERVER['PHP_SELF'] . "?func=DelAdmin&Sno=".$row["Sno"];
		    		$Html[] = $row;
		    	}
	    	} catch(Exception $e) {
			   JSModule::ErrorMessage($e->getMessage());
			} 
    	}
    	
    	include("../adm_html/admin/LoginLogList.html");
    }
    
    function Details() {
    	
    	$db = new CDbShell();
    	
    	$db->query("select * from house where Sno = '".$_GET["Sno"]."' ");
    	$html = $db->fetch_array();
    	
    	include("../adm_html/admin/view_details.html");
    }
    
    function InstituteList()
    {
    	$index = 0;
    	$db = new CDbShell();
    	$db->query("select * from institute where Recurit = '".$_GET["Recurit"]."' order by InstituteCode ");
    	$html .= "<table width=\"100%\" bgcolor=\"#C4DCF7\" cellspacing=\"1\" border=\"1\" bordercolordark=\"#FFFFFF\" cellpadding=\"3\" class=\"view_product\">";
		while ($row = $db->fetch_array()) {
			if ($index == 0 || ($index % 4) == 0) $html .= "<tr>";
			$html .= "<td width=\"25%\" valign=\"top\"><input type=checkbox name=\"ChkInstitute".$_GET["index"]."[]\" id=\"ChkInstitute".$_GET["index"]."[]\" value=\"".$row["InstituteName"]."\" />".$row["InstituteName"]."</td>";
			if (($index % 4) == 3) $html .= "</tr>";
			$index++;
		}
		
		$html .= "</table>";
		
		echo $html;
		exit;
    }
    
    function login()
    {
    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    		
        	!empty($_POST)     && CommonElement::Add_S($_POST);
    		if (Isset($_POST['loginid']) == false || strlen(trim($_POST['loginid'])) == 0) {
    			JSModule::ErrorJSMessage('請輸入登入帳號!');
	            exit;
    		}
    		if (Isset($_POST['password']) == false  || strlen(trim($_POST['password'])) == 0) {
    			JSModule::ErrorJSMessage('請輸入登入密碼!');
	            exit;
    		}
    		$sess = new CSession;
	    	if(strcmp($_POST['auth_num'], $sess->getVar("auth_num")) != 0){ 
	    		JSModule::ErrorJSMessage('驗證碼不正確!');
	            exit;
			}
			
			// $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
			// //127.0.0.1
			// $recaptcha_secret = '6LeAecIUAAAAAN_g_T2cjsERR2ehdPpabXu54zaJ';
			// //正式
			// //$recaptcha_secret = '6Ld8eMIUAAAAADio6pLw5e2KWzCqaKD0me2gAESi';
			// $recaptcha_response = $_POST['recaptcha_response'];
			
			// //Make and decode POST request:
			// $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
			// $recaptcha = json_decode($recaptcha);
			//if($recaptcha->success==true){
				//if($recaptcha->score >= 0.5) {
					CSession::setVar("admin_login", $_POST['loginid']);
					CSession::setVar("admin_password", $_POST['password']);
					
					!empty($_CSession)     && CommonElement::Add_S($_CSession);
					$d = new CToday();
					$db = new CDbShell();
					if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   
						$myip = $_SERVER['REMOTE_ADDR'];   
					} else {   
						$myip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);   
						$myip = $myip[2];   
					}
				
					$is_logined = $this -> is_logined();        	
					if ($is_logined == 1) {	        	
						$sess->setVar("Recurit", $_POST['recurit']);
						
						$db->query("update admin set LastLoginDate = '".date("Y-m-d H:i:s")."' where Account='".$_POST['loginid']."'");
						
						$_IP = $this->get_client_ip(0, true);
						$field = array("AdminSno", "LoginIP");
						$value = array(CSession::getVar("AdminSno"), $_IP);
						$db->insert("loginlog", $field, $value);
						
						/*$db->query("select * from deadline where Recurit = '".$recurit."' ");
						$row = $db->fetch_array();
						$sess->setVar("DeadlineDate", $row['DeadlineDate']);*/
						
						
						echo"document.location='./admin.php';";
						exit;
					}elseif ($is_logined == 2) {
						JSModule::ErrorJSMessage('您目前的IP位置不在允許登入，請洽總管理者！');
						/*echo"<script language=javascript>";
						echo "Javascript:alert('您目前的IP位置不在允許登入，請洽總管理者！');";
						echo"top.document.location='".$_SERVER['PHP_SELF']."';";
						echo"</script>";*/
						exit;				
					}else {
						JSModule::ErrorJSMessage('登入帳號密碼不正確!');
						exit;
					}
				/*} else {
					JSModule::ErrorJSMessage('可疑登入!');
				}  */
			 /*} else {
				JSModule::ErrorJSMessage('新重新整理再進行登入!');
	            exit;
			 }*/
	    
		}else {
			include("../adm_html/admin/login.html");
		}	
	}
	
	function get_client_ip($type = 0, $adv = false) {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if($adv){
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos    =   array_search('unknown',$arr);
                if(false !== $pos) unset($arr[$pos]);
                $ip     =   trim($arr[0]);
            }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip     =   $_SERVER['HTTP_CLIENT_IP'];
            }elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip     =   $_SERVER['REMOTE_ADDR'];
            }
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法驗證
        $long = sprintf("%u",ip2long($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
    
    function AdminInquiry() {
    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    		$db = new CDbShell();
    		$db2 = new CDbShell();
    		
    		$sdate = $_POST["sdate"]." 00:00:00";
    		$edate = $_POST["edate"]." 23:59:56";
    		$head['default_sdate'] = $_POST["sdate"];
    		$head['default_edate'] = $_POST["edate"];
	    	$db->query("select * from admin_log where RecordTime >= '".$sdate."' and RecordTime <= '".$edate."' order by RecordTime ");
			while ($row = $db->fetch_array())
	    	{
	    		if ($row["Login"] == "webadmin")	$row["Name"] = "<samp style=\"color:#FF0000\">總管理者</samp>";
	    		else {
		    		$db2->query("select Name from admin where login = '".$row["Login"]."'");
		    		$row2 = $db2->fetch_array();
		    		$row["Name"] = $row2["Name"];
	    		}
	    		$admin[] = $row;
	    	}
    	}else {
    		$d = new CToday(1);
    		$head['default_sdate'] = $d->Today(-7);
    		$head['default_edate'] = $d->Today();
    	}
    	include("../adm_html/admin/view_Inquiry.html");
	}
	
	function PermitIP()
    {
		$m_id = CSession::getVar("admin_login");
		$m_pw = CSession::getVar("admin_password");
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			CDbShell::query("UPDATE admin SET PermitIP = '".$_POST['PermitIP']."' WHERE Account='".$m_id."' and Password='".md5($m_pw)."'");
			$Message = 
			<<<EOF
			alert('更改完成');
EOF;
			echo $Message;
			exit;
		}
    	CDbShell::query("SELECT * FROM admin WHERE Account='".$m_id."' and Password='".md5($m_pw)."'");
        //return "select * from admin where Account='".$m_id."' and Password='".md5($m_pw)."'";
        $row = CDbShell::fetch_array();
    	
    	include("../adm_html/admin/PermitIP.html");
    }
    function AllowIP()
    {
    	CDbShell::query("select * from allowip order by Sno desc ");
		while ($Row = CDbShell::fetch_array())
    	{
    		$Html[] = $Row;
    	}
    	
    	include("../adm_html/admin/AllowIPList.html");
    }
    
    function AddedAllowIP()
    {
    	try {
	    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    		if (strlen(trim($_POST['IP'])) < 6) {
	    			throw new exception("請輸入IP位置!");
	    		}
	    		if (!is_numeric($_POST['MinIPSection']) || !is_numeric($_POST['MaxIPSection']) || $_POST['MinIPSection'] < 0 || $_POST['MinIPSection'] > 256 || $_POST['MaxIPSection'] < 0 || $_POST['MaxIPSection'] > 256) {
	    			throw new exception("請輸入IP區段!");
	    		}
	    		if (strcmp($_POST['auth_num'], CSession::getVar("auth_num")) != 0){ 
		    		throw new exception("驗證碼不正確!");
		    	}
		    	
		    	$field = array("IP", "MinIPSection", "MaxIPSection", "Name");
				$value = array($_POST["IP"], $_POST["MinIPSection"], $_POST["MaxIPSection"], $_POST["Name"]);
		    	CDbShell::insert("allowip", $field, $value);
	    		JSModule::BoxCloseJSMessage("增加允許IP完成!", $_SERVER['PHP_SELF']."?func=AllowIP");
	    		
	    	}else {
		    	
		    	include("../adm_html/admin/AddedAllowIP.html");
	    	}
    	} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
    }
    
    function ModifyAllowIP()
    {
    	try {
	    	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	    		if (strlen(trim($_POST['IP'])) < 6) {
	    			throw new exception("請輸入IP位置!");
	    		}
	    		if (!is_numeric($_POST['MinIPSection']) || !is_numeric($_POST['MaxIPSection']) || $_POST['MinIPSection'] < 0 || $_POST['MinIPSection'] > 256 || $_POST['MaxIPSection'] < 0 || $_POST['MaxIPSection'] > 256) {
	    			throw new exception("請輸入IP區段!");
	    		}
	    		if (strcmp($_POST['auth_num'], CSession::getVar("auth_num")) != 0){ 
		    		throw new exception("驗證碼不正確!");
		    	}
		    	
		    	$field = array("IP", "MinIPSection", "MaxIPSection", "Name");
				$value = array($_POST["IP"], $_POST["MinIPSection"], $_POST["MaxIPSection"], $_POST["Name"]);
		    	CDbShell::update("allowip", $field, $value, "Sno = ". $_GET["Sno"]);
		    	
	    		JSModule::BoxCloseJSMessage("編修允許IP完成!", $_SERVER['PHP_SELF']."?func=AllowIP");
	    	}else {
	    		CDbShell::query("select * from allowip where Sno = '".$_GET["Sno"]."' ");
	    		$Html = CDbShell::fetch_array();
		    	include("../adm_html/admin/ModifyAllowIP.html");
	    	}
    	} catch(Exception $e) {
		   JSModule::ErrorJSMessage($e->getMessage());
		} 
    }
    
    function CheckDateTime($date_time)
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
	
	function Home()
    {
    	/*if (!class_exists('CBulletin'))			include_once("../Bulletin/CBulletin.php");
    	if (!class_exists('CNews'))			include_once("../News/CNews.php");
		$Bulletin = new CBulletin();
    	$BulletinRow = CBulletin::ToHome();
    	
    	$News = new CNews();
    	$NewsRow1 = CNews::ToHome("0");
    	
    	$News = new CNews();
    	$NewsRow2 = CNews::ToHome("1");*/
    	
    	//include($this->GetAdmHtmlPath . "Home.html");
    	if (CSession::getVar("IsChild") == 1) {
    		
    		list($firstKey) = array_keys(CSession::getVar("Purview"));
    	}else {
    		$firstKey = "ledger/admin.php";
    	}
    	//echo $firstKey;
    	//exit;
    	
    	echo"<script language=javascript>";
	    echo"top.document.location='../".$firstKey."';";
	    echo"</script>";
	    exit;
    }
	function GetHouseData($factor, $Keyword = "") {
		$db = new CDbShell();
		if ($Keyword != "") {
			$factor = "where CaseName like '%".$Keyword."%'";
		}
		$db->query("select * from house ". $factor ." order by Sno  ");
		//echo "select * from house ". $factor ." order by Sno  ";
		//exit;
		while ($row = $db->fetch_array())
    	{
			$html[] = $row;		
	    }
	    
	    return $html;
	}
	function CaseFunctions_Admin() {
		$sess = new CSession;
		
		switch ($_GET['func']) {
			case "AdminList":
				$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
				if ($is_logined == 1 && $Boss == 1) {
            		return $this->AdminList();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}
            	break;
            case "GetAdminList":
				$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
				if ($is_logined == 1 && $Boss == 1) {
            		return $this->GetAdminList();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}
            	break;
            case "AddedAdmin":
            	$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($is_logined == 1 && $Boss == 1) {
            		return $this->AddedAdmin();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}
            	break;
            case "ModifyAdmin":
            	$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($is_logined == 1 && $Boss == 1) {
            		return $this->ModifyAdmin();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}
            	break;
            case "DelAdmin":
            	$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($is_logined == 1 && $Boss == 1) {
	            	$this->DelAdmin();
		        }else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    		
            	break;       
            case "ChildAdminList":
				$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($is_logined == 1) {
            		return $this->ChildAdminList();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    		
            	break;
            case "AddedChildAdmin":
				$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($is_logined == 1) {
            		return $this->AddedChildAdmin();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    		
            	break;
            case "ModifyChildAdmin":
				$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($is_logined == 1) {
            		return $this->ModifyChildAdmin();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    		
            	break;
            case "ChildPurview":
				$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($is_logined == 1) {
            		return $this->ChildPurview();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    		
            	break;
            case "DelChildAdmin":
				$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($is_logined == 1) {
            		return $this->DelChildAdmin();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    		
            	break;
            case "ChangePass":
            	$is_logined = $this -> is_logined();
            
            	if ($is_logined == 1) {
            		
            		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            			
				    	try {
					    	if (Trim($_POST["Password"]) == "") {
					    		throw new exception("請輸入原本密碼!");
					    	}
					    
					        $m_id = CSession::getVar("admin_login");
					        $m_pw = CSession::getVar("admin_password");
					        
					        CDbShell::query("select * from admin where Account='".$m_id."' and Password='".md5($m_pw)."'");
					        $Row = CDbShell::fetch_array();
	        				
			            	CDbShell::query("select * from admin where Sno = '".$Row["Sno"]."'");
			            	$Row2 = CDbShell::fetch_array();
			            	
			            	if (strcmp(md5(Trim($_POST["Password"])),Trim($Row2["Password"])) != 0) {
					    		throw new exception("原本密碼不正確!");
					    	}
					    	
					    	if (preg_match("/^[A-Za-z0-9]{4,20}$/",$_POST["NewPassword"]) == false) {
					    		throw new exception("新密碼長度最少要4個數字或字母!");
					    	}
					    	
					    	if (preg_match("/".$_POST["NewPassword"]."/",$_POST["NewPassword2"]) == false) {
					    		throw new exception("密碼與確認密碼不符合!");
					    	}
					    	
					    	/*if(strcmp($_POST['auth_num'],$sess->getVar("auth_num")) != 0){ 
					            throw new exception("驗證碼不正確!");
					    	}*/
					    	
					    	$field = array("Password", "LastChangePassword");
							$value = array(md5(Trim($_POST["NewPassword"])), date("Y-m-d H:i:s",mktime (date("H"),date("i"),date("s"),date("m") ,(date("d")+90) ,date("Y"))));
					    	CDbShell::update("admin", $field, $value, "Sno = ". $Row["Sno"]);
					    	
					    	JSModule::BoxCloseJSMessage("已變更成新密碼，請用新密碼再登入一次。", $_SERVER["PHP_SELF"]);
				    		
			            } catch(Exception $e) {
						   JSModule::ErrorJSMessage($e->getMessage());
						} 
            		}else {
            			include("../adm_html/admin/ChangePassword.html");
            		}
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    		
            	break;
            case "LoginLogList":
				$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
				if ($is_logined == 1 && $Boss == 1) {
            		return $this->LoginLogList();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}
            	break;
            case "Import":
            	$is_logined = $this -> is_logined();
            	//$Boss = $sess->getVar("Boss");
            	$AdminLevel = $sess->getVar("AdminLevel");
				if ($is_logined == 1 && $AdminLevel <= 2) {
            		$this->Import();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    		
            	break; 
            case "Inquiry":
            	$is_logined = $this -> is_logined();
            	//$Boss = $sess->getVar("Boss");
            	if ($is_logined == 1) {
            		$this->Inquiry();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    	
            	break;
            case "Details":
            	$is_logined = $this -> is_logined();
            	//$Boss = $sess->getVar("Boss");
            	if ($is_logined == 1) {
            		$this->Details();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    	
            	break;
            case "Remove":
            	$is_logined = $this -> is_logined();
            	$Boss = CSession::getVar("Boss");
            	if ($is_logined == 1 && $Boss == 1) {
	            	$db = new CDbShell();
	            	$db->query("TRUNCATE house");	            
	            	
			    	echo "<script language=javascript>";
			    	echo "Javascript:alert('所有資料清除成功!');";
			    	echo "top.document.location='./admin.php?func=Inquiry'";
					echo "</script>";
					exit;
				}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    
            	break;
            case "AdminInquiry":
        		$is_logined = $this -> is_logined();
            	$Boss = $sess->getVar("Boss");
            	if ($is_logined == 1 && $Boss == 1) {
        			$this->AdminInquiry();
        		}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    
				break;      
			case "AllowIP":
				$is_logined = $this -> is_logined();
				$Boss = CSession::getVar("Boss");
				if ($is_logined == 1 && $Boss == 1) {
					$this->AllowIP();
				}else {
					$sess->clearAll();
					echo"<script language=javascript>";
					echo"top.document.location='./admin.php';";
					echo"</script>";
					exit;
				}    
				break;
        	case "PermitIP":
        		$is_logined = $this -> is_logined();
            	$Boss = CSession::getVar("Boss");
            	if ($is_logined == 1 && CSession::getVar("AdminLevel") == 3) {
        			$this->PermitIP();
        		}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    
        		break;
        	case "AddedAllowIP":
        		$is_logined = $this -> is_logined();
            	$Boss = CSession::getVar("Boss");
            	if ($is_logined == 1 && $Boss == 1) {
        			$this->AddedAllowIP();
        		}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    
        		break;
        	case "ModifyAllowIP":
        		$is_logined = $this -> is_logined();
            	$Boss = CSession::getVar("Boss");
            	if ($is_logined == 1 && $Boss == 1) {
        			$this->ModifyAllowIP();
        		}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    
        		break;
        	case "DeleteAllowIP":
        		$is_logined = $this -> is_logined();
            	$Boss = CSession::getVar("Boss");
            	if ($is_logined == 1 && $Boss == 1) {
            		
	            	CDbShell::query("delete from allowip where Sno = '".$_GET["Sno"]."'");
	            	
	            	JSModule::Message("IP刪除成功。", $_SERVER["PHP_SELF"]."?func=AllowIP");
		        }else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    		
        		break;
            case "login":
            	return $this->login();
            	break;
            case "logout":
            	$db = new CDbShell();
            	$d = new CToday();
		        $sess = new CSession;
		        $sid = session_id();
		        $m_id = $sess->getVar("admin_login");
		        
		        if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   
					$myip = $_SERVER['REMOTE_ADDR'];   
				} else {   
					$myip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);   
					$myip = $myip[2];   
				}
		
            	/*$field = array("RecordTime","Login","Action","IP");
				$value = array($d->Today(),$m_id,"2",$myip);
		    	$db->insert("admin_log", $field, $value);*/
            	///$sess->clearAll();
            	
            	CSession::ClearVar("admin_login");
        		CSession::ClearVar("admin_password");
        		CSession::ClearAll();
            	echo"<script language=javascript>";
			    echo"top.document.location='./admin.php';";
			    echo"</script>";
			    exit;
            	break;
            case "DeleteData":
            	$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($is_logined == 1 && $AdminLevel <= 2) {
            		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            			$db = new CDbShell();
            			foreach ((array)$_POST["DelCheck"] as $key => $value) { 
            				$db->query("delete from house where Sno = '".$value."'");
            			}
		            	echo "<script language=javascript>";
			            echo "Javascript:alert('資料清除成功!');";
			            echo "document.location='admin.php?func=Inquiry';";
			            echo "</script>";
			            exit;
            		}
	            	
		        }else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    		
            	break;
            case "ImportDuty":
            	$is_logined = $this -> is_logined();
            	$AdminLevel = $sess->getVar("AdminLevel");
            	$Boss = $sess->getVar("Boss");
            	if ($is_logined == 1 && ($Boss == 1 || $AdminLevel == 2)) {
           			return $this->ImportDuty();
           		}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    
            	break;
            case "InquiryDuty":
            	$is_logined = $this -> is_logined();
            	$AdminLevel = CSession::getVar("AdminLevel");
            	$Boss 		= CSession::getVar("Boss");
            	if ($is_logined == 1 && ($AdminLevel <= 2)) {
            		return $this->InquiryDuty();
            	}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    
            	break;
            case "RemoveDuty":
            	$is_logined = $this -> is_logined();
            	$Boss = $sess->getVar("Boss");
            	if ($is_logined == 1 && $Boss == 1) {
            		$this->RemoveDuty();
	            	$db = new CDbShell();
	            	$db->query("TRUNCATE duty");	            
	            	
			    	echo "<script language=javascript>";
			    	echo "Javascript:alert('出缺勤所有資料清除成功!');";
			    	echo "top.document.location='./admin.php?func=InquiryDuty'";
					echo "</script>";
					exit;
				}else {
            		$sess->clearAll();
	            	echo"<script language=javascript>";
				    echo"top.document.location='./admin.php';";
				    echo"</script>";
				    exit;
            	}    
            	break;
            default :
            	
				$db = new CDbShell();
            	$allow =  false;
				if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   
					$myip = $_SERVER['REMOTE_ADDR'];   
				} else {   
					$myip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);   
					$myip = $myip[2];   
				}
				
				//echo strrpos($myip, '.');
				$FrontIP = substr($myip, 0, strrpos($myip, '.'));
				$SectionIP = substr($myip, strrpos($myip, '.') + 1);
				//echo $_SERVER['REMOTE_ADDR'];
				//exit;
				/*$db->query("select * from allowip where IP = '".$FrontIP."' and MinIPSection <= $SectionIP and MaxIPSection >= $SectionIP");
				if ($db->num_rows()) {
					$allow = true;
					//echo $allow;
				}*/
				//if ($allow == true) {
		        	$is_logined = $this -> is_logined();
		        	//echo "is_logined:".$is_logined;
		        	if ($is_logined == "1") {	
		        		$RefundNumber = CLedger::GetRefundNumber();
	            
	            		if ($RefundNumber > 0) {
				            $js  = "<script type=\"text/javascript\" language=\"javascript\">";
							$js .= "alert(\"有 ".$RefundNumber."筆交易正等待退款處理！\");";
				            $js .= "</script>\r\n";
				
							echo $js;				
						}	
						self::Home();
					}else if ($is_logined == "3") {
						echo "<script language=javascript>";
		            	echo "alert(\"登入失敗！沒有設置任何管理權限。\");";
					    echo "top.document.location='./admin.php?func=logout';";
					    echo "</script>";
					    exit;
					}else {	
			    		include("../adm_html/admin/login.html");
					}
				/*}
				else {
					echo "";
					echo"<script language=javascript>";
					echo "Javascript:alert('Warning! Your IP is not allowed.');";
				    echo"top.document.location='http://www.google.com.tw';";
				    echo"</script>";
				    exit;
				}*/
				exit;
		}

	}

}
	
?>