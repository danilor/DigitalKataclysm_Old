<?php function callback($buffer){  return trim($buffer); }
ob_start("callback");
/*
 * Config.php
 * Config file for the site
 */

ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);
if(isset($_GET["debug"])){
        ini_set('display_errors', 1); 
}


/************************************************************************/


/************************************************************************/
/*Here are all require for configurations*/
define("CONFIG_FOLDER","configuration");
define("CONFIG_EXTENSION",".conf.php");
$configuration_files = scandir("".CONFIG_FOLDER."");
//We will read the folder of configuration files and add them to the site
foreach($configuration_files AS $cfile) {
    $pattern = "/^(\w)+\.conf\.php$/";
    if (preg_match($pattern,$cfile)){   require_once(CONFIG_FOLDER."/".$cfile);   }
    
}
/************************************************************************/

/************************************************************************/
/*Here are all includes for process files*/
define("INC_FOLDER","inc");
define("INC_EXTENSION",".inc.php");
$inc_files = scandir("".INC_FOLDER."");
//We will read the folder of includes files and add them to the site
foreach($inc_files AS $ifile) {
    $pattern = "/^(\w)+\.inc\.php$/";
    if (preg_match($pattern,$ifile)){   require_once(INC_FOLDER."/".$ifile);   }

}
/************************************************************************/

/************************************************************************/
/*Here are all classes  files*/
define("CLASS_FOLDER","classes");
define("CLASS_EXTENSION",".class.php");
$class_files = scandir("".CLASS_FOLDER."");
//We will read the folder of includes files and add them to the site
foreach($class_files AS $cfile) {
    $pattern = "/^(\w)+\.class\.php$/";
    if (preg_match($pattern,$cfile)){   require_once(CLASS_FOLDER."/".$cfile);   }

}
/************************************************************************/

//Timer functions
define("MINUTE", 60);
define("HOUR", 60*60);
define("DAY", 60*60*24);
define("MONTH", 60*60*24*30);

/*This following variable is to put extra information on the
 * footer of the site, just like javascript validations or other kind of css or information*/
$extraFooter = array(); //This extraFooter variable will carry on all footer scripts and other stuff to put them
$DP = new DataProcessor();
$domain = $DP->getDomain();
$DP = null;
define("DOMAIN",$domain);
$currentPage = $_SERVER["PHP_SELF"];
if($_SERVER["QUERY_STRING"] &&  trim($_SERVER["QUERY_STRING"]) != ""){
    $currentPage .= "?".$_SERVER["QUERY_STRING"];
}
define("CURRENT_PAGE",$domain.$currentPage);


/*************************************************************************/

//Database global variable
global $DB,$VA,$DP,$global_ip,$global_sess,$global_domain,$global_current_url;
$DB = new MySQLdatabase(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE, FALSE);
$VA = new Validator();
$DP = new DataProcessor();

/************************************************************************/
/*Here are all init  files*/
/*This function has to be to the end of config file because this is the process that is gonna be run every time a page loads.*/
define("INIT_FOLDER","init");
define("INIT_EXTENSION",".init.php");
$class_files = scandir("".INIT_FOLDER."");
//We will read the folder of includes files and add them to the site
foreach($class_files AS $cfile) {
    $pattern = "/^(\w)+\.init\.php$/";
    if (preg_match($pattern,$cfile)){   require_once(INIT_FOLDER."/".$cfile);   }

}
/************************************************************************/
/*Aditional Help params*/
$noShare = FALSE;
$codeFile = $_SERVER["PHP_SELF"];
$codeFile = str_replace("/","",$codeFile);
$codeFile = str_replace(".php", ".code.php", $codeFile);
if(file_exists($codeFile)){
    require_once($codeFile);
    if(function_exists('page_init')){
        page_init();
    }
}
