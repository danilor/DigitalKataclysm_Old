<?php
/*
 * Files.
 * A class to manage all files information. (BETA VERSION)
 * @author: Danilo Josue Ramirez Mattey
 * */

class Files{
    
    /*Private Variables*/
    private $outputFormat = "XML";
    private $initDir = "/";
    
    /*Public functions*/
   public function __construct($format,$initDir){
        $this->initDir =$initDir;
        $this->outputFormat = $format;
    }
    public function readDir($dir,$format = "XML",$complete = TRUE){
        if(in_array($format, array("XML","HTML","JSON"))){
            
        }
        
    }
    public function getExtension($str) {
         $i = strrpos($str,".");
         if (!$i) { return ""; }
         $l = strlen($str) - $i;
         $ext = substr($str,$i+1,$l);
         return $ext;
    }
    
    
    function __destruct() {
        
    }
    /*Private Functions*/
    
}
?>
