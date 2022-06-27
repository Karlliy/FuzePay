<?php 
class CDbShellRead {
	public static $magic = "on";
    public static $type = "mysql";
    /*public static $host = "10.127.9.2"; //sql server hostname
    public static $user = "root"; // username
    public static $pass = "1J8-C*aaV.QaXKf90T-+"; // password
    public static $db 	= "fuzepay"; // database*/

	public static $host = "127.0.0.1"; //sql server hostname
    public static $user = "root"; // username
    public static $pass = "12345"; // password
    public static $db 	= "fuzepay"; // database

	public static $dbh = 0;
	public static $rs = 0;
	public static $debug = 0;
	public static $errorno=0;
	public static $errorstring="";
	
	// -----------------------------------------------------------------------------------
	static function debugOn() {
		self::$debug = 1;
	}

	// -----------------------------------------------------------------------------------
	static function ClearErrorCode() {
		self::$errorno = 0;
		self::$errorstring = "";
	}
	// -----------------------------------------------------------------------------------
	static function SetErrorCode($errno, $errstr) {
		self::$errorno = $errno;
		self::$errorstring = $errstr;
	}
	// -----------------------------------------------------------------------------------
	static function GetErrorNo() {
		$err = self::$errorno;
		return $err;
	}
	// -----------------------------------------------------------------------------------
	static function GetErrorStr() {
		$err = self::$errorstring;
		return $err;
	}
	// -----------------------------------------------------------------------------------
	function __construct($db = '') {

		!empty($_POST)     && self::Add_S($_POST);
	    !empty($_GET)     	&& self::Add_S($_GET);
	    !empty($_COOKIE) 	&& self::Add_S($_COOKIE);
		!empty($_SESSION) 	&& self::Add_S($_SESSION);
		
		if($db) self::$db = $db;
		if(!self::$dbh) self::connect();
	}

	static function Add_S(&$array) {
	    if (is_array($array)) {
	        foreach ($array as $key => $value) {
	            if (!is_array($value)) {
					$value = @addslashes($value);					
					$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
					$value = filter_var($value, FILTER_SANITIZE_STRING);
	                $array[$key] =  @htmlspecialchars($value,ENT_QUOTES);
	            } else {
	                self::Add_S($array[$key]);
	            }
	        }
	    }
	}
	// -----------------------------------------------------------------------------------
	static function Connect() {
		self::$dbh = mysqli_connect(self::$host, self::$user, self::$pass) or die ("DB無法連線");
	}
	static function DB_open($db = '') {
		if($db) self::$db = $db;
		if(!self::$dbh) self::$connect();
	}
	// -----------------------------------------------------------------------------------
	static function query($sqlstr) {
		self::ClearErrorCode();
		mysqli_query(self::$dbh, " SET NAMES 'utf8'");
		mysqli_query(self::$dbh, "SET @@sql_mode = 'ALLOW_INVALID_DATES'"); 
		//self::rs = mysqli_db_query(self::db, $sqlstr);
		//exit;
		mysqli_select_db(self::$dbh, self::$db) or die ("無法連線資料表");
		self::$rs = mysqli_query(self::$dbh, $sqlstr);
 
		self::SetErrorCode(mysqli_errno(self::$dbh),mysqli_error(self::$dbh));
		
		if (self::$errorno != "0") {
			
			if (self::$debug == 1) {
				echo "$sqlstr<br>";
				echo "Mysql errorno: ".self::$errorno." error:".$db->errorstring;
				exit;
				}
		}
		//echo " error:".$db->errorstring;
		//echo "$sqlstr<br>";
		return self::$rs;
	}
	// -----------------------------------------------------------------------------------
	static function num_rows($rs = 0) {
		// if(!self::rs) return 0;
		if($rs != 0) $frs = $rs;
		else $frs = self::$rs;
		return mysqli_num_rows($frs);
	}
	static function affected_rows($rs = 0) {
		if($rs != 0) $frs = $rs;
		else $frs = self::$rs;
		return mysqli_affected_rows(self::$dbh);
	}
	// -----------------------------------------------------------------------------------
	static function fetch_array ($rs = 0) {
		if(!self::$rs) return 0;

		if($rs) $frs = $rs;
		else $frs = self::$rs;
		return mysqli_fetch_array($frs);
	}
	// -----------------------------------------------------------------------------------
	static function insert_id () {
		$id = -1;
		if(!self::$rs) return 0;
		return mysqli_insert_id(self::$dbh);
	}
	function Mkpassword($str) {
		self::Query("SELECT PASSWORD('$str')");
		$row = self::fetch_array();
		return $row[0];
	}
	// -----------------------------------------------------------------------------------
	static function insert($table, $field, $value) {
		if(!is_array($field)) return 0;
		if(!is_array($value)) return 0;
		
		count($field) == count($value) or die(count($field) . ":" . count($value));

		$sql = "INSERT INTO $table ( ";
		for($i = 1;$i <= count($field);$i++) {
			$sql .= $field[$i-1];
			if($i != count($field)) $sql .= ",";
		}
		$sql .= ") values(";

		for($i = 1;$i <= count($value);$i++) {
			//$value[$i-1] = mysqli_escape_string($value[$i-1]);
			$value[$i-1] = mysqli_real_escape_string(self::$dbh, $value[$i-1]);
			//if($charset == "GB2312") $value[$i-1] = $code->Gb_Big5($value[$i-1]);
			$sql .= "'" . $value[$i-1] . "'";
			if($i != count($value)) $sql .= ",";
		}
		$sql .= ")";
	 	//echo $sql."<br>";
	 	//exit;
		self::query($sql);
	}
	// -----------------------------------------------------------------------------------
	static function update($table, $field, $value, $where) {
		if(!is_array($field)) return 0;
		if(!is_array($value)) return 0;
		if(count($field) != count($value)) {
			echo "count(\$field) != count(\$value)";
			exit;
		}
		
		$sql = "update $table set ";
		for($i = 0;$i < count($field);$i++) {
			//$value[$i] = mysqli_escape_string($value[$i]);
			$value[$i] = mysqli_real_escape_string(self::$dbh, $value[$i]);
			$sql .= $field[$i] . "='" . $value[$i] . "'";
			if(($i + 1) != count($field)) $sql .= ",";
		}
		
		if($where != "") $sql .= " where " . $where;
		//echo "$sql<br>";
		//exit;
		self::query($sql);
	}
	
	function cmagic($content){
        	if(self::$magic){
        		foreach ($content as $key => $value) { 
				$content[$key] = stripslashes($value); 
			}
		}
		return $content;
	}
	
	function escape($content){
		//return mysqli_escape_string($content);
		return mysqli_real_escape_string($content);
	}
	// -----------------------------------------------------------------------------------
	static function fetch_row_field ($query) {
		$row = self::query($query);
		while ($row2 = self::fetch_array($row)) {
			$alldata[] = $row2;
		}
		return $alldata;
	}
	
	static function DB_close($identifier = "") {
		mysqli_close(self::$dbh) or die ("Could not close"); 
		
		return true;
	}
	
	static function begin() { 
		$null = mysqli_query("START TRANSACTION", self::$dbh);
		return mysqli_query("BEGIN", self::$dbh); 		
	} 

	static function rollback() { 
		return mysqli_query("ROLLBACK", self::$dbh); 
	} 

	static function commit() { 
		return mysqli_query("COMMIT", self::$dbh); 
	} 
}

?>
