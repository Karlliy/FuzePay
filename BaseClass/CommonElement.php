<?php 
class CommonElement {
	static function EventLog($Operation) {
		
		if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   
			$myip = $_SERVER['REMOTE_ADDR'];   
		} else {   
			$myip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);   
			$myip = $myip[2];   
		}
		
		$Operation = "管理員：".CSession::getVar("AdminName")." " .$Operation;
		@CDbShell::connect();
		$field = array("AdminSno", "IP","Operation");
		$value = array(CSession::getVar("AdminSno"), $myip, $Operation);
		CDbShell::insert('EventLog', $field, $value);
		
	}
	static function CountHoliday($CheckDate, $DelayDay, $Initial) {

		if ($Initial == true) {
			$Result4 = CDbShell::query("SELECT * FROM holiday WHERE Date = '".$CheckDate."'");
			if (CDbShell::num_rows($Result4) != 0) {
				$DelayDay++;
			}
		}

		if ($DelayDay == 0) return $CheckDate;
		$CheckDate = date('Y-m-d', strtotime($CheckDate ." +1 day"));
		/*echo $CheckDate . "|". $DelayDay;
		echo "<br />";
		echo "SELECT * FROM holiday WHERE Date = '".$CheckDate."'";
		echo "<br />";*/
		$Result3 = CDbShell::query("SELECT * FROM holiday WHERE Date = '".$CheckDate."'");
    	if (CDbShell::num_rows($Result3) == 0) {
    		if ($DelayDay > 0) $DelayDay--;
    		if ($DelayDay == 0) return $CheckDate;
    		else return self::CountHoliday($CheckDate, $DelayDay, false);
    		
    	}else {
    		return self::CountHoliday($CheckDate, $DelayDay, false);
    	}
	}
	static function IsHoliday($Date)
    {
    	$Result3 = CDbShell::query("SELECT * FROM holiday WHERE Date = '".$Date."'");
    	if (CDbShell::num_rows($Result3) != 0) {
    		$LastDate = date('Y-m-d', strtotime($Date ." +1 day"));
    		return self::IsHoliday($LastDate);
    	}else {
    		$retDate = $Date;
    		//echo "\$retDate =>".$retDate;
    	}
    	
    	return $retDate;
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
	static function CopyImg($Serial, $Files, $Path)
    {
        if (!$Files['tmp_name'] || $Files['tmp_name'] == "") return "";
        
        //$dir = $this->GetAdmDataPath() . "$path";
        //@mkdir($dir, 0777); 
        $FileName = str_pad($Serial,8,'0',STR_PAD_LEFT).".". preg_replace('/^.*\.([^.]+)$/D', '$1', $Files['name']);
        //exit;
        //copy($img['tmp_name'], "$dir/" . $img['name']);
        copy($Files['tmp_name'], $Path. $FileName);
        return $FileName;
    } 
    
    static function Add_S(&$array) {
	    if (is_array($array)) {
	        foreach ($array as $key => $value) {
	            if (!is_array($value)) {
					$value= 		@addslashes($value);
	                $array[$key] =  @htmlspecialchars($value,ENT_QUOTES);
	            } else {
	                self::Add_S($array[$key]);
	            }
	        }
	    }
	}
	
	public function	__construct () {
		
		
	}
	
	static function Run() {
		
		if (!class_exists('Operate'))		include_once("../operate/Operate.php");
		$_Options = substr($_SERVER["UNENCODED_URL"] , strrpos($_SERVER["UNENCODED_URL"], "/", (strrpos($_SERVER["UNENCODED_URL"], "/") - strlen($_SERVER["UNENCODED_URL"]) - 1)));
	
		//echo "<br />";
		//echo $_GET["a"];
		//print_r($_SERVER);
		preg_match('/\/(.*?)\//', $_Options, $_Searched);
		
		if (strcmp($_Searched[1] , "Game") == 0) {
			Operate::Hall();
			//echo $_Searched[1]."|Game";
			exit;
		}
		if (strcmp($_Searched[1] , "MemberOnline") == 0) {
			Operate::MemberOnline();
			//echo $_Searched[1]."|Game";
			exit;
		}
	}
	
	static $iv = "@Si&mon#";
	static $key = "DA74DFA9C4EBD612ECC3DF15";
	static function encrypt($str) {

		//$key2 = self::hex2bin(self::$key);    
		//$iv = $this->iv;

		$td = @mcrypt_module_open(MCRYPT_TRIPLEDES, '', MCRYPT_MODE_CBC, self::$iv);
		
		@mcrypt_generic_init($td, self::$key, self::$iv);
		$encrypted = @mcrypt_generic($td, $str);

		@mcrypt_generic_deinit($td);
		@mcrypt_module_close($td);

		return bin2hex($encrypted);
	}
	        
	//===============================================================
	static function decrypt($encrypted){

		$encrypted = self::hex2bin($encrypted);
		$cipher = @mcrypt_module_open(MCRYPT_TRIPLEDES, '', MCRYPT_MODE_CBC, self::$iv );
	    $key = substr(self::$key, 0, @mcrypt_enc_get_key_size($cipher));
	 
		// 128-bit blowfish encryption:
		if (@mcrypt_generic_init($cipher, $key, self::$iv) != -1){
			// PHP pads with NULL bytes if $cleartext is not a multiple of the block size..
			$cleartext = @mdecrypt_generic($cipher,$encrypted );
			@mcrypt_generic_deinit($cipher);
		}
	 
		// suppression du padding.
		$pad = ord(substr($cleartext,strlen($cleartext)-1));
		if($pad>0 & $pad<=8){
			$cleartext = substr($cleartext, 0, strlen($cleartext) - $pad);
		}

		return $cleartext;
	 
	}
	
	static function hex2bin($hexdata) {
		$bindata = '';

		for ($i = 0; $i < strlen($hexdata); $i += 2) {
		    $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
		}

		return $bindata;
    }
    
	static function AllPages($SQLString, $PageItems) { 
		
		CDbShell::query($SQLString);
		$AllItems = CDbShell::num_rows();
		//CDbShell::DB_close();
		
		$Pages = (INT)($AllItems / $PageItems);
		if($AllItems % $PageItems == 0) $Pages--;
		return $Pages;
	}
	
	static function ShowPageBar($IPage, $Pages, $option_query) { //
		if($Pages < 1) return;
		$prevpage = $IPage; 
		if($prevpage < 0) $prevpage = 0;
		$nextpage = $IPage + 1;
		if($nextpage > $Pages) $nextpage = $Pages;
		$Pagebar = "";
		
		if($IPage > 0) $Pagebar .= "<span onclick=\"document.location='".$_SERVER["PHP_SELF"]."?ipage=".($IPage-1)."$option_query'\" style=\"cursor: pointer;\"></span>";
        else $Pagebar .= "<span></span>";
                
        $tp = 9;
        $pi = 0;
        $HPages = $Pages;
        if($Pages > $tp) {
        	$htp = (INT)($tp/2);
        	$pi = $IPage-$htp;
        	if($pi < 0) $pi = 0; 
        	$HPages = $pi + $tp;
        	if($HPages > $Pages ) { 
				$HPages = $Pages; 
				$pi = $HPages - $tp;
			}
        }
		for($i = $pi; $i <= $HPages; $i++) {
			if($IPage != $i) $Pagebar .= "<span onclick=\"document.location='".$_SERVER["PHP_SELF"]."?ipage=".$i."$option_query'\" style='cursor: pointer; margin-left:10px; margin-right:10px; font-size:14px;' class='label label-info'>". ($i+1);
			else $Pagebar .= "<span class='label label-info' style='margin-left:10px; margin-right:10px; color:#CCC; font-size:14px;'>" . ($i+1);
			
			if($IPage!=$i) $Pagebar.="</span>";
			else $Pagebar .="</span>";
		}
		
		if($IPage < $Pages) $Pagebar .= "<span onclick=\"document.location='".$_SERVER["PHP_SELF"]."?ipage=".($IPage+1)."$option_query'\" style=\"cursor: pointer;\"></span>";
        else $Pagebar .= "<span></span>";
		return $Pagebar;
	}
}


?>