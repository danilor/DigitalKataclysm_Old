<?php
function getSegment($number){
    $longURL = $_SERVER["PHP_SELF"];
    $urlArray = explode('/', $longURL);
    return @$urlArray[$number];
}

function include_block($name){
    include(BLOCKS_FOLDER."/".$name.".php");
}

?>
