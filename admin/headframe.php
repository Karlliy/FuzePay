<?php
include_once("../BaseClass/header.php");
//需視情況修改路徑
$incdir="./";
$basedir="../BaseClass";

//$incdir="/admin";
//$basedir="/BaseClass";
//==============================================

ob_start(); //output buffer control 這樣才不會有cookie問題
include_once($basedir."/CDbShell.php");
include_once($basedir."/CToday.php"); 
include_once($basedir."/CUrlQuery.php");
include_once($basedir."/CSession.php");  
include_once($incdir."/Cadmin.php");

unset($MenuHtml);

$MainMenu =  simplexml_load_file('Menu.xml');

$sess = new CSession;
$recurit = $sess->getVar("Recurit");
$Boss = $sess->getVar("Boss");
$AdminLevel = $sess->getVar("AdminLevel");

//echo $MainMenu->MenuItem->SubMenuItem->attributes()->url;
foreach($MainMenu->MenuItem as $index => $affiliate) {
	//echo "div<br>";
		
		$MenuHtml .= "<li><a href=\"\">".$affiliate->attributes()->title."</a><ul>";
	    //echo $affiliate->SubMenuItem."<br>";
	    foreach($affiliate->SubMenuItem as $SubMindex => $SubMaffiliate) {
	    	
    			$MenuHtml .= "<li><a href=\"".$SubMaffiliate->attributes()->url."\"";
				if ($SubMaffiliate->attributes()->target == "true") {
	    			$MenuHtml .= "target=\"_blank\"";
	    		}else {
	    			$MenuHtml .= "";
	    		}
	    		$MenuHtml .= ">".$SubMaffiliate->attributes()->title."</a></li>";
	    		
    		}
	    $MenuHtml .= "</ul>";
	    $MenuHtml .= "</li>";
}
//echo $title["value"];

include("../adm_html/header.html");
?>
 
