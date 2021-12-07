<?php
class CToday {
   var $type;
   var $f_year, $f_month, $f_day, $f_hour, $f_min, $f_sec; // 給 initDate() 分析後的日期存放資料
// ----------------------------------------------------------------------------
    function __construct($type='0') {
        if($type=='0') $type="Y-m-d H:i:s";
        if($type=='1') $type="Y-m-d";
        if($type=='2') $type="Y-m-d H:i";
        if($type=='3') $type="Y/m/d H:i:s";
        if($type=='4') $type="Y/m/d";
        if($type=='5') $type="Y/m/d H:i";
        if($type=='6') $type="m/d";
        $this->type=$type;
    }
        
// ----------------------------------------------------------------------------
    function Today($days=0) {
        if ($days==0)
        return date("$this->type");
        else return date("$this->type",mktime (date("H"),date("i"),date("s"),date("m") ,(date("d")+$days) ,date("Y")));
    }
// ----------------------------------------------------------------------------
// 功能：分析與 SQL 相容的日期型態以單獨取出年月日時分秒
// 傳入：$this->initDate(SQL 的日期型態);
//       只能接受三種格式輸入：[Y-m-d H:i:s]、[Y-m-d]、[H:i:s]
// 傳回：一組雜湊陣列 ['year'], ['month'], ['day'], ['hour'], ['min'], ['sec']
//       放置資料到物件中的 $this->f_year, f_month, f_day, f_hour, f_min, f_sec;
//       (f是format的意思)
// 預設：今天
// ----------------------------------------------------------------------------
    function initDate($date=NULL) {
        if(!isset($date)) $date = date('Y-m-d H:i:s');
        $is_right_date = preg_match_all("/\-/", $date, $backdate);
        $is_right_time = preg_match_all("/\:/", $date, $backtime);
        //如果有兩個 - 表示為 [y-m-d H:i:s]、[y-m-d] 其中一種
        if($is_right_date==TRUE && count($backdate[0])==2) {
            $date_array = split("[[:space:]\:\-]", $date);
            $ret_date['year']  = $date_array[0];
            $ret_date['month'] = $date_array[1];
            $ret_date['day']   = $date_array[2];
            if(count($backtime[0])==2) {
                $ret_date['hour']  = $date_array[3];
                $ret_date['min']   = $date_array[4];
                $ret_date['sec']   = $date_array[5];
            }
        } else {
            if($is_right_time==TRUE && count($backtime[0])==2) {
                $date_array = explode(":", $date);
                $ret_date['hour'] = $date_array[0];
                $ret_date['min']  = $date_array[1];
                $ret_date['sec']  = $date_array[2];
            } else {
                echo "<LI>Input Date Error at initDae() of CDate Class!";
                return FALSE;
            }
        }
        $this->f_year  = $ret_date['year'];
        $this->f_month = $ret_date['month'];
        $this->f_day   = $ret_date['day'];
        $this->f_hour  = $ret_date['hour'];
        $this->f_min   = $ret_date['min'];
        $this->f_sec   = $ret_date['sec'];
        return $ret_date;
    }
    
    // ----------------------------------------------------------------------------
// 配合 initDate() 得到 "時:分" 格式
// ----------------------------------------------------------------------------
    function shortTime($date=NULL) {
        if(!isset($date)) $date = date('Y-m-d H:i:s');
        $this->initDate($date);
        return "$this->f_hour:$this->f_min";
    }
// ----------------------------------------------------------------------------
// 配合 initDate() 得到 "月-日" 格式
// ----------------------------------------------------------------------------
    function shortDate($date=NULL) {
        if(!isset($date)) $date = date('Y-m-d H:i:s');
        $this->initDate($date);
        return "$this->f_month-$this->f_day";
    }
// ----------------------------------------------------------------------------
// 配合 initDate() 得到 "時:分:秒" 格式
// ----------------------------------------------------------------------------
    function longTime($date=NULL) {
        if(!isset($date)) $date = date('Y-m-d H:i:s');
        $this->initDate($date);
        return "$this->f_hour:$this->f_min:$this->f_sec";
    }
   //=====================================================
        function MakeJavaScript($opener,$field) {
                $javastr="";
                $javastr.="<script language=\"JavaScript\">\n";         
                        $javastr.="<!--\n";
                        
                        $javastr.="if (self.focus != null) self.focus();\n";
                        $javastr.="function ".$field."SelDate() {\n";
                        $javastr.="var dval = document.$opener.$field.value;\n";
                        $javastr.="var aurl = \"cal.php?rfd=$field&opener=$opener&idate=\"+dval;"."\n";
                $javastr.="var hWnd = window.open(aurl,\"HelpWindow\",\"width=400,height=300,resizable=yes,scrollbars=yes\");\n";
                $javastr.="if (!hWnd.opener) hWnd.opener = self;\n";
                $javastr.=" }\n";
                        $javastr.="-->\n";
                        $javastr.="</script>\n";
                        echo $javastr;
                
        }
    // ----------------------------------------------------------------------------
}
?>