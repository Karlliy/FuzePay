<?php 
// ----------------------------------------------------------------------------
// 明碼佇列處理
// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
class CUrlQuery{
        var $allvars=array();
// ----------------------------------------------------------------------------
        function __construct(){           
                $this->getvars() ;
        }
// ----------------------------------------------------------------------------
        function getvars() {
                
                reset($this->allvars);
                if(strlen($_SERVER["QUERY_STRING"])==0) return;
                $pieces = mb_split ("\&", $_SERVER["QUERY_STRING"]);
                $i=0;
                while ($i < count($pieces)) {
                        $b = mb_split ('\=', $pieces[$i]);
                        $var = $b [0];
                        $val = $b [1];
                        $this->allvars[$var]=$val;
                        $i++;
                }
        }
        function getvars2($QUERY_STRING) {
                
                reset($this->allvars);
                if(strlen($QUERY_STRING)==0) return;
                $pieces = mb_split ("\&", $QUERY_STRING);
                $i=0;
                while ($i < count($pieces)) {
                        $b = mb_split ('\=', $pieces[$i]);
                        $var = $b [0];
                        $val = $b [1];
                        $this->allvars[$var]=$val;
                        $i++;
                }
        }
        function getvars3($v) {
                return $this->allvars[$v];  
               
        }
// ----------------------------------------------------------------------------
        function setvars($v,$c) {
                $this->allvars[$v]=$c;  
               
        }
// ----------------------------------------------------------------------------
        function geturlstr() {
                $url="";
                reset ($this->allvars);
                for($i=1;$i<=count($this->allvars);$i++) {
                     list ($key, $val) = each ($this->allvars); 
                     if ($val){
                     	 $temp[$key]=$val;
                     	
                     	}
                }  
              		
                for($i=1;$i<=count($temp);$i++) {
                        list ($key, $val) = each ($temp);                        
                        if ($val) {
                        	
                           $url.="$key=$val";
                           if($i!=count($temp)) $url.="&";
                          // echo "$key=$val"."<br>";
                        }
                }
                
                return $url;
        }
// ----------------------------------------------------------------------------
}
// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
?>
