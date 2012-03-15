<?php
/*
	Session Simple manager
 *
 *  Session simple manager
	@author: Danilo Josue Ramirez Mattey
	@year: 2010
	@version: 1.0
*/

class Session{
	static function setSession($sessionName,$sessionValue){
        @session_start();
        $_SESSION[$sessionName] = $sessionValue;
        return session_id();
    }
    static function verifySession($sessionName){
        @session_start();
        $status = null;
        if(   isset( $_SESSION[$sessionName] )   ){
            $status = $_SESSION[$sessionName];
        }
        return $status;
    }
    static function destroySession($sessionName){
        @session_start();
        session_destroy();
    }

    static function setCookie($cookieName,$value,$time = 3600){
        setcookie ($cookieName, $value, time() + $time, "/");
    }

    static function getCookie($cookieName){
        $status = null;
        if(isset($_COOKIE[$cookieName])){
            $status= $_COOKIE[$cookieName];
        }
        return $status;
    }

    static function destroyCookie($cookieName){
        setcookie ($cookieName, "", time() - 36000, "/");
    }

}


?>