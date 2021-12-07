<?php

class CSession
{
    // ----------------------------------------------------------------------------
    // 建構時自動判別該使用者是否來過，若未來過，就餵他吃小餅乾。
    // ----------------------------------------------------------------------------
    function __construct($admin = 0)
    {
        if ($cookie_sid != NULL) {
            session_id($cookie_sid);
        } else {
            //session_cache_limiter('private');
            session_start();
            $set_sid = session_id();
            //      if($admin) setcookie("cookie_sid", $set_sid); 
            //   setcookie("cookie_sid", $set_sid,time()+120);
            
        }
    }
    // ----------------------------------------------------------------------------
    // 登記一個變數在 Session 中。
    // 範例： $this->setVar("aaa", "Hello Kitty");
    //        則 $aaa = "Hello Kitty";
    // ----------------------------------------------------------------------------
    static function SetVar($name, $vars)
    {
        session_start();
        $_SESSION[$name] = $vars;
    }
    // ----------------------------------------------------------------------------
    // 取出 Session 的變數。
    // 範例： $bbb = $this->getVar("aaa");
    //        則會將曾經登錄過的變數內含值 $aaa 取出。
    // ----------------------------------------------------------------------------
    static function GetVar($name)
    {
        session_start();
        return $_SESSION[$name];
    }
    // ----------------------------------------------------------------------------
    static function ClearVar($name)
    {
        session_start();
        unset($_SESSION[$name]);
    }
    // ----------------------------------------------------------------------------
    static function ClearAll()
    {
        session_start();
        session_destroy();
        return TRUE;
    }
    // ----------------------------------------------------------------------------
    static function ShowAll()
    {
        session_start();
        $there = session_encode();
        $here  = split(";", $there);
        return $here;
    }
}
?>