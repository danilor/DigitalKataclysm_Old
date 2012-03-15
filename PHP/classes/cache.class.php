<?php
/*
Cache Class
 *
 *  This class pretend make cache for single pages in basic proccess
	@author: Danilo Josue Ramirez Mattey
	@year: 2010
	@version: 1.0
*/

class Cache{
    //Class Variable
    private $cacheContent = NULL;
    private $cacheUrl = '';
    private $cachePath = '';
    private $cacheTime = 60;
    private $ext = ".cache";
    //Error control variables
    private $errors = array(    0 => "No errors",
                                1 => "File doesn't exist",
                                2 => "File out of date",
                                3 => "Error opening the file"
                           );
    private $lastError = 0;
    private $noError = 0;
    //Info Variables
    private $developer = "Danilo Josue Ramirez Mattey";
    private $devYear = "2010";
    private $devClassName = "Cache";

    //Object Functions
    function __construct($path,$time = 60,$url = ''){
        $this->cachePath = $path;
        $this->cacheTime = $time;
         if(trim($url) != ''){
             $this->cacheUrl = $url;
         }else{
             $this->cacheUrl = $this->getCurrentUrl();
         }
         $this->resetError();
    }
    function __toString(){
        return $this->developer."|".$this->devYear."|".$this->devClassName;
    }
    function getUrl(){
        $this->resetError();
       return $this->cacheUrl;
    }
    function setUrl($url = ''){
        $this->resetError();
        if(trim($url) != ""){ $this->cacheUrl = $url; }
        else{ $this->cacheUrl = $this->getCurrentUrl(); }
    }
    function getCacheContent(){
        $file =  $this->strToHex($this->cacheUrl).$this->ext;
        $file = $this->cachePath.$file;
        if(file_exists($file)){
            $modTime = date(filemtime($file));
            $timeBet = (time()-$modTime)/60;
            if($timeBet <= $this->cacheTime){
                $gestor = fopen($file, "r");
                $content = fread($gestor, filesize($file));
                fclose($gestor);
                $this->cacheContent = $content;
                $this->resetError();
                return $this->cacheContent;
            }else{
                $this->setError(2);
                return null;
            }
        }else{
            $this->setError(1);
            return NULL;
        }
    }
    function setCacheContent($content){
        $file =  $this->strToHex($this->cacheUrl).$this->ext;
        $file = $this->cachePath.$file;
        if(file_exists($file)){
            unlink($file);
        }
        $Handle = fopen($file, 'w+');
        if($Handle == null){
            $this->setError(3);
            return false;
        }
        $Data = $content;
        fwrite($Handle, $Data);
        fclose($Handle);
        $this->resetError();
        return true;
    }
    function cleanCache($url = '',$exact = true){
        if(trim($url) != ""){
            $file =  $this->strToHex($url).$this->ext;
            $file = $this->cachePath.$file;
        }else{
            $file =  $this->strToHex($this->cacheUrl).$this->ext;
            $file = $this->cachePath.$file;
        }
        if($exact === true){
            if(file_exists($file)){unlink($file); return true;}
            else{
                $this->setError(1);
                return false;
            }
        }else{
            if ($handle = opendir($this->cachePath)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        $pos = strpos($url, $file); if($pos !== false){ unlink($file);}
                    }
                }
                closedir($handle);
            }
        }
    }
    function getLastCacheContent(){return $this->cacheContent;}
    function getLastErrorId(){return $this->lastError;}
    function getLastErrorDescription(){return $this->errors[$this->lastError];}
    //Static Public Functions

    //Private Functions
    private function getCurrentUrl(){return ($_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]);}
    private function strToHex($string){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }
    private function hexToStr($hex){
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
    private function setError($id){$this->lastError = $id;}
    private function resetError(){ $this->lastError = $this->noError;}
}
?>
