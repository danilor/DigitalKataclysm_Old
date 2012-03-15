<?php


function uploadImage($fieldName,$uploadPath = "/",$allowedExtensions = "jpg",$justCut = false){
    $DP = new DataProcessor();
    $returnStatus = "";
$allowedString =     $allowedExtensions;
    $allowedExtensions = explode("|", $allowedExtensions);
    $returnStatus= 'Extension Invalid. Allowed Extensions: '.$allowedString;
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $image =$_FILES[$fieldName]["name"];
        $uploadedfile = $_FILES[$fieldName]['tmp_name'];

        if ($image){
            $filename = stripslashes($_FILES[$fieldName]['name']);
            $extension = getExtension($filename);
            $extension = strtolower($extension);
            if (!in_array($extension, $allowedExtensions))
            {
                $returnStatus=  'Unknown Image extension ';
            }else{
                $size=filesize($_FILES[$fieldName]['tmp_name']);

                if ($size > IMAGE_MAX_SIZE*1024  ||  $size == false)
                {
                    $returnStatus= "You have exceeded the size limit";
                    return $returnStatus;
                }

                if($extension=="jpg" || $extension=="jpeg" )
                {
                    $uploadedfile = $_FILES[$fieldName]['tmp_name'];
                    $src = imagecreatefromjpeg($uploadedfile);
                }
                else if($extension=="png")
                {
                    $uploadedfile = $_FILES[$fieldName]['tmp_name'];
                    $src = imagecreatefrompng($uploadedfile);
                }
                else
                {
                    $src = imagecreatefromgif($uploadedfile);
                }
                $maxSquare = 0;
                list($width,$height)=getimagesize($uploadedfile);
                if($justCut == true){
                    if($width > $height){
                        $maxSquare = $height;
                    }else{
                        $maxSquare = $width;
                    }
                }else{
                    if($width > $height){
                        $maxSquare = $width;
                    }else{
                        $maxSquare = $height;
                    }
                }
                
                $squareImage=imagecreatetruecolor($maxSquare,$maxSquare);
                $cutX = 0 + (($width-$maxSquare)/2);
                $cutY = 0 + (($height-$maxSquare)/2);
                //echo "<br /><br />Original: W:$width - H:$height<br />";
                //echo "New: W:$cutX - H:$cutY<br />";
                //echo "Max Square Dimensions: $maxSquare<br />";

                imagecopyresampled  ( $squareImage  , $src  , 0  , 0  , $cutX  , $cutY  , $maxSquare  , $maxSquare  , $maxSquare   , $maxSquare  );

                $imagesSizes = array(500,150,100,75,50);
                $finalNameFile = date("YmdGis").$DP->createPassword(10).".".$extension;
                foreach($imagesSizes as $s){
                    $newwidth=$s;

                    $newheight=($height/$width)*$newwidth;

                    $tmp=imagecreatetruecolor($s,$s);

                    imagecopyresampled($tmp,$squareImage,0,0,0,0,$s,$s,$maxSquare,$maxSquare);
                    if($s != 500){
                        $filename = $uploadPath.$s."/". $finalNameFile;
                    }else{
                        $filename = $uploadPath. $finalNameFile;
                    }
                    imagejpeg($tmp,$filename,100);
                    imagedestroy($tmp);
                }
                
                //echo 'Lets go to special Sizes';
                $specialSizes = array();
                $specialSizes[0]["width"] = 133;
                $specialSizes[0]["height"] = 61;
                $specialSizes[0]["folder"] = "wid";
                
                $specialSizes[1]["width"] = 204;
                $specialSizes[1]["height"] = 308;
                $specialSizes[1]["folder"] = "spot";
                
                $specialSizes[2]["width"] = 520;
                $specialSizes[2]["height"] = 163;
                $specialSizes[2]["folder"] = "pet";
                
                foreach($specialSizes as $sp){
                	//echo $sp["width"]."x".$sp["height"];
                	if($sp["width"]>=$sp["height"]){
                		if($width >= $height){
                			$specialWidthSrc = ($sp["width"]*$height)/$sp["height"];
		                	$specialHeightSrc = $height;
		                	if($width < $specialWidthSrc){
		                		//echo 'Internal condition <br />';
		                		$auxHeight = 0;
		                		$auxWidth = 0;
		                		$auxHeight = $specialHeightSrc;
		                		$auxWidth = $width;
		                		$specialWidthSrc = $auxWidth;
		                		$specialHeightSrc = ($sp["height"]*$auxWidth)/$sp["width"];
		                		
		                	}
                		}else{
                			$specialWidthSrc = $width;
		                	$specialHeightSrc = ($sp["height"]*$width)/$sp["width"];
                		}
                		//echo "Case for WID".$specialWidthSrc . " x " . $specialHeightSrc."<br />";
	                	$cutX = 0 + (($width-$specialWidthSrc)/2);
		                $cutY = 0 + (($height-$specialHeightSrc)/2);
	                	$specialImage=imagecreatetruecolor($sp["width"],$sp["height"]);
	                	imagecopyresampled  ( $specialImage  , $src  , 0  , 0  , $cutX  , $cutY  , $sp["width"]  , $sp["height"]  , $specialWidthSrc   , $specialHeightSrc  );
	                	$filename = $uploadPath.$sp["folder"]."/". $finalNameFile;
	                	//echo $filename."<br />";
	                	imagejpeg($specialImage,$filename,100);
	                	imagedestroy($specialImage);
                	}
                	if($sp["width"] < $sp["height"]){
                		if($width >= $height){
                			$specialWidthSrc = ($sp["width"]*$height)/$sp["height"];
		                	$specialHeightSrc = $height;
                		}else{
                			$specialWidthSrc = $width;
		                	$specialHeightSrc = ($sp["height"]*$width)/$sp["width"];
		                	//echo "Case for SPOT inside procedure".$specialWidthSrc . " x " . $specialHeightSrc ."<br />";
		                	if($height < $specialHeightSrc){
		                		//echo 'Internal condition <br />';
		                		$auxHeight = 0;
		                		$auxWidth = 0;
		                		$auxHeight = $height;
		                		$auxWidth = $specialWidthSrc;
		                		$specialWidthSrc = ($sp["width"]*$auxHeight)/$sp["height"];
		                		$specialHeightSrc = $auxHeight;
		                		
		                	}
                		}
	                	//echo "Case for SPOT".$specialWidthSrc . " x " . $specialHeightSrc ."<br />";
	                	$cutX = 0 + (($width-$specialWidthSrc)/2);
		                $cutY = 0 + (($height-$specialHeightSrc)/2);
	                	$specialImage=imagecreatetruecolor($sp["width"],$sp["height"]);
	                	imagecopyresampled  ( $specialImage  , $src  , 0  , 0  , $cutX  , $cutY  , $sp["width"]  , $sp["height"]  , $specialWidthSrc   , $specialHeightSrc  );
	                	
	                	//echo "imagecopyresampled  ( $specialImage  , $src  , 0  , 0  , $cutX  , $cutY  , ".$sp["width"]."  , ".$sp["height"]."  , $specialWidthSrc   , $specialHeightSrc  );";
	                	$filename = $uploadPath.$sp["folder"]."/". $finalNameFile;
	                	//echo $filename."<br />";
	                	imagejpeg($specialImage,$filename,100);
	                	imagedestroy($specialImage);
                	}
                }
                imagedestroy($src);
                $returnStatus ="file:".$finalNameFile;
            }
            
        }
    }
    return $returnStatus;
}
function getExtension($str) {
         $i = strrpos($str,".");
         if (!$i) { return ""; }
         $l = strlen($str) - $i;
         $ext = substr($str,$i+1,$l);
         return $ext;
 }
function getFolderFiles($folder,$allowedExtensions){
    if(!is_array($allowedExtensions)){
        return false;
    }
    
    if ($handle = opendir($folder)) {
        $returnArray = array();
        while (false !== ($file = readdir($handle))) {
            //echo "$file<br />";
            if(in_array(getExtension($file), $allowedExtensions)){
                $returnArray[] = $file;
            }
        }
        return $returnArray;
    }else{
        return false;
    }
}
 ?>
