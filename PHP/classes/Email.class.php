<?php
/*
	Email Class for Good Sam
 *
 *  This email class tries to help the send of emails using an HTML template
	@author: Danilo Josue Ramirez Mattey
	@year: 2010
	@version: 2.0
 * 
 *      @Revision: 1.0 > First email class
 *                 2.0 > Attachment added
*/

class Email{
	
	//Class Variables
	private $from="";
	private $to="";
	private $subject="";
	private $title="";
	private $text="";
	private $email="";
        private $domain = "";
        private $attachment = "";
        private $attachmentName = "";
	
	function __construct($from,$to,$subject,$title,$text,$template,$attachment="",$name=""){
		$this->from = $from;
		$this->to = $to;
		$this->subject = $subject;
		$this->title = $title;
		$this->text = $text;
                $this->attachment = $attachment;
                $this->attachmentName = $name;
		$this->fillEmail($template);
                
                $pageUR1 = preg_replace("/\/(.+)/", "", $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
                $curdomain = str_replace("", "", $pageUR1);
                $curdomain = "http://" . $curdomain;
                $this->domain = $curdomain;

	}
	
	private function fillEmail($template){
		$this->email = $template;
	}
	function sendEmail($addStyle = true){
		$msg = $this->email;
		$msg = str_replace("[TITLE]",$this->title,$msg);
		$msg = str_replace("[TEXT]",$this->text,$msg);
                $msg = str_replace("[DOMAIN]",$this->domain,$msg);
                $completeMessage = $msg;
                $random_hash = md5(date('r', time()));
                if($addStyle){
                    $msg = str_replace("<p>",'<p style="font-family:Helvetica, Arial, Helvetica, sans-serif sans-serif; font-size:12px; color:#000000;">',$msg);
                    $msg = str_replace("<li>",'<li style="font-family:Helvetica, Arial, Helvetica, sans-serif sans-serif; font-size:12px; color:#000000;">',$msg);
                    $msg = str_replace("<ul>",'<ul style="font-family:Helvetica, Arial, Helvetica, sans-serif sans-serif; font-size:12px; color:#000000;">',$msg);
                }
		$headers = "From: ".$this->from."\r\n"; 
		
                
                if(strlen($this->attachment) == 0){
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
                }else{
                    $msg = "";
                    $bound_text =	"actrightcom";
                    $bound =	"--".$bound_text."\r\n";
                    $bound_last =	"--".$bound_text."--\r\n";

                    $headers =	"From: ".$this->from."\r\n";
                    $headers .=	"MIME-Version: 1.0\r\n"
                            ."Content-Type: multipart/mixed; boundary=\"$bound_text\"";
                    $msg = ""; 	 
                    $msg .=	"If you can see this MIME than your client doesn't accept MIME types!\r\n"
                            .$bound;

                    $msg .=	"Content-Type: text/html; charset=\"iso-8859-1\"\r\n"
                            ."Content-Transfer-Encoding: 7bit\r\n\r\n"
                            .$completeMessage."\r\n"
                            .$bound;

                    $file =	file_get_contents($this->attachment);
                    $finfo = finfo_open(FILEINFO_MIME_TYPE); 
                    $mime = finfo_file($finfo, $this->attachment); 
                    $msg .=	"Content-Type: $mime; name=\"".$this->attachmentName."\"\r\n"
                            ."Content-Transfer-Encoding: base64\r\n"
                            ."Content-disposition: attachment; file=\"".$this->attachmentName."\"\r\n"
                            ."\r\n"
                            .chunk_split(base64_encode($file))
                            .$bound_last;
                    
                    
                }
                
    		
		return mail($this->to, $this->subject, $msg, $headers);
	}
        
        private function getFileExtension($str) {
                 $i = strrpos($str,".");
                 if (!$i) { return ""; }
                 $l = strlen($str) - $i;
                 $ext = substr($str,$i+1,$l);
                 return $ext;
         }
}


?>