<?php

/*
 * Validator manager.
 * A class to validate variety of information.
 * @author: Danilo Josue Ramirez Mattey
 * */

class DataProcessor {

    //Class description
    private $version = "1.0";
    private $author = "Danilo Josue Ramirez Mattey";
    private $rigths = "All rigths reserved.";
    private $year = "2010";
    private $classDescription = "Validator for PHP";
    //Reporting Variables
    private $lastErrorId;
    private $errors = array(
        0 => "",
        9 => "The length cannot be 0.",
        10 => "Text cannot be empty."
    );

    //////////////////////////////
    /////Public Functions////////
    /////////////////////////////
    function __construct() {
        $this->resetError();
        return true;
    }

    function getLastErrorDescription() {
        return $this->errors[$this->lastErrorId];
    }

    function getLastErrorId() {
        return $this->lastErrorId;
    }

    function cutString($textString, $stringLength, $cutWord="...", $strict = true) {
        $this->resetError();
        if ($textString != "") {
            $textString = trim($textString);
            if ($stringLength != 0) {
                //Here can start the proccess.
                if (strlen($textString) > $stringLength) {
                    if (!$strict) {
                        return $this->cutStringByWords($textString, $stringLength) . $cutWord;
                    } else {
                        $textString = substr($textString, 0, $stringLength) . $cutWord;
                        return trim($textString);
                    }
                }
            } else {
                $this->setError(9);
                return false;
            }
        } else {
            $this->setError(10);
            return false;
        }
    }

    function strToHex($string){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }
    function hexToStr($hex){
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2){
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }

    function truncHTML($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                        // if tag is an opening tag
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                // if the maximum length is reached, get off the loop
                if ($total_length >= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        // add the defined ending to the text
        $truncate .= $ending;
        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }
        return $truncate;
    }

    function findLinks($string, $longLink = 15) {
        return $this->getTextWithLink($string, $longLink);
    }

    function sanitize($input) {
        $this->resetError();
        $regex = "/[a-z|0-9|-|_|.|\/|@|!|:|;|,|\'| ]/i";
        preg_match_all($regex, $input, $matches);
        $output = "";
        foreach ($matches[0] as $v) {
            $output .= $v;
        }
        return $output;
    }

    function createPassword($maxLenght = 12) {
        if (!is_numeric($maxLenght)) {
            $maxLenght = 12;
        }
        $this->resetError();
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        srand((double) microtime() * 1000000);
        $i = 0;
        $pass = '';

        while ($i <= $maxLenght) {
            $num = rand() % strlen($chars);
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }

    function getDomain() {
        $pageUR1 = preg_replace("/\/(.+)/", "", $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
        $curdomain = str_replace("", "", $pageUR1);
        $curdomain = "http://" . $curdomain;
        return $curdomain;
    }

    function xml_entity_decode($text, $charset = 'Windows-1252') {
        $text = html_entity_decode($text, ENT_COMPAT, $charset);
        $text = html_entity_decode($text, ENT_COMPAT, $charset);
        return $text;
    }

    function xml_entities($text, $charset = 'Windows-1252') {
        $text = htmlentities($text, ENT_COMPAT, $charset, false);
        $arr_xml_special_char = array("&quot;", "&amp;", "&apos;", "&lt;", "&gt;");
        $arr_xml_special_char_regex = "(?";
        foreach ($arr_xml_special_char as $key => $value) {
            $arr_xml_special_char_regex .= "(?!$value)";
        }
        $arr_xml_special_char_regex .= ")";
        $pattern = "/$arr_xml_special_char_regex&([a-zA-Z0-9]+;)/";
        $replacement = '&amp;${1}';
        return preg_replace($pattern, $replacement, $text);
    }

    function __toString() {
        $out = "";
        $out .= "Author: " . $this->author . " | ";
        $out .= "Version: " . $this->version . " | ";
        $out .= "Year: " . $this->year . " | ";
        $out .= "Description: " . $this->classDescription . " | ";
        $out .= "Working Status: WORKING";
        return $out;
    }

    function __destruct() {
        
    }

    //Static Public Functions

    static function safeHTML($text, $valid="<a><b><h1><h2><h3><h4><h5><p><span><b><i><u>") {
        return strip_tags($text, $valid);
    }

    static function safeEmail($email) {
        $email = str_replace("@", "[at]", $email);
        $email = str_replace(".", "[dot]", $email);
        return $email;
    }

    static function encryptInfo($str, $ky='') {
        if ($ky == ''
            )return $str;
        $ky = str_replace(chr(32), '', $ky);
        if (strlen($ky) < 8
            )exit('key error');
        $kl = strlen($ky) < 32 ? strlen($ky) : 32;
        $k = array();
        for ($i = 0; $i < $kl; $i++) {
            $k[$i] = ord($ky{$i}) & 0x1F;
        }
        $j = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $e = ord($str{$i});
            $str{$i} = $e & 0xE0 ? chr($e ^ $k[$j]) : chr($e);
            $j++;
            $j = $j == $kl ? 0 : $j;
        }
        return $str;
    }

    static function getArrayStates() {
        $states = array(
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'District of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
        );
        return $states;
    }

    static function getArrayCountries() {
        $country_list = array(
            "Afghanistan",
            "Albania",
            "Algeria",
            "Andorra",
            "Angola",
            "Antigua and Barbuda",
            "Argentina",
            "Armenia",
            "Australia",
            "Austria",
            "Azerbaijan",
            "Bahamas",
            "Bahrain",
            "Bangladesh",
            "Barbados",
            "Belarus",
            "Belgium",
            "Belize",
            "Benin",
            "Bhutan",
            "Bolivia",
            "Bosnia and Herzegovina",
            "Botswana",
            "Brazil",
            "Brunei",
            "Bulgaria",
            "Burkina Faso",
            "Burundi",
            "Cambodia",
            "Cameroon",
            "Canada",
            "Cape Verde",
            "Central African Republic",
            "Chad",
            "Chile",
            "China",
            "Colombi",
            "Comoros",
            "Congo (Brazzaville)",
            "Congo",
            "Costa Rica",
            "Cote d'Ivoire",
            "Croatia",
            "Cuba",
            "Cyprus",
            "Czech Republic",
            "Denmark",
            "Djibouti",
            "Dominica",
            "Dominican Republic",
            "East Timor (Timor Timur)",
            "Ecuador",
            "Egypt",
            "El Salvador",
            "Equatorial Guinea",
            "Eritrea",
            "Estonia",
            "Ethiopia",
            "Fiji",
            "Finland",
            "France",
            "Gabon",
            "Gambia, The",
            "Georgia",
            "Germany",
            "Ghana",
            "Greece",
            "Grenada",
            "Guatemala",
            "Guinea",
            "Guinea-Bissau",
            "Guyana",
            "Haiti",
            "Honduras",
            "Hungary",
            "Iceland",
            "India",
            "Indonesia",
            "Iran",
            "Iraq",
            "Ireland",
            "Israel",
            "Italy",
            "Jamaica",
            "Japan",
            "Jordan",
            "Kazakhstan",
            "Kenya",
            "Kiribati",
            "Korea, North",
            "Korea, South",
            "Kuwait",
            "Kyrgyzstan",
            "Laos",
            "Latvia",
            "Lebanon",
            "Lesotho",
            "Liberia",
            "Libya",
            "Liechtenstein",
            "Lithuania",
            "Luxembourg",
            "Macedonia",
            "Madagascar",
            "Malawi",
            "Malaysia",
            "Maldives",
            "Mali",
            "Malta",
            "Marshall Islands",
            "Mauritania",
            "Mauritius",
            "Mexico",
            "Micronesia",
            "Moldova",
            "Monaco",
            "Mongolia",
            "Morocco",
            "Mozambique",
            "Myanmar",
            "Namibia",
            "Nauru",
            "Nepa",
            "Netherlands",
            "New Zealand",
            "Nicaragua",
            "Niger",
            "Nigeria",
            "Norway",
            "Oman",
            "Pakistan",
            "Palau",
            "Panama",
            "Papua New Guinea",
            "Paraguay",
            "Peru",
            "Philippines",
            "Poland",
            "Portugal",
            "Qatar",
            "Romania",
            "Russia",
            "Rwanda",
            "Saint Kitts and Nevis",
            "Saint Lucia",
            "Saint Vincent",
            "Samoa",
            "San Marino",
            "Sao Tome and Principe",
            "Saudi Arabia",
            "Senegal",
            "Serbia and Montenegro",
            "Seychelles",
            "Sierra Leone",
            "Singapore",
            "Slovakia",
            "Slovenia",
            "Solomon Islands",
            "Somalia",
            "South Africa",
            "Spain",
            "Sri Lanka",
            "Sudan",
            "Suriname",
            "Swaziland",
            "Sweden",
            "Switzerland",
            "Syria",
            "Taiwan",
            "Tajikistan",
            "Tanzania",
            "Thailand",
            "Togo",
            "Tonga",
            "Trinidad and Tobago",
            "Tunisia",
            "Turkey",
            "Turkmenistan",
            "Tuvalu",
            "Uganda",
            "Ukraine",
            "United Arab Emirates",
            "United Kingdom",
            "United States",
            "Uruguay",
            "Uzbekistan",
            "Vanuatu",
            "Vatican City",
            "Venezuela",
            "Vietnam",
            "Yemen",
            "Zambia",
            "Zimbabwe"
        );
        return $country_list;
    }

    static function relativeTime($time) {

        // this function will calculate a friendly date difference string
        // based upon $time and how it compares to the current time
        // for example it will return "1 minute ago" if the difference
        // in seconds is between 60 and 120 seconds
        // $time is a GM-based Unix timestamp, this makes for a timezone
        // neutral comparison
        $delta = strtotime(gmdate("Y-m-d H:i:s", time())) - $time;
        //echo "|".strtotime(gmdate("Y-m-d H:i:s", time()));
        if ($delta < 1 * MINUTE) {
            return $delta == 1 ? "one second ago" : $delta . " seconds ago";
        }
        if ($delta < 2 * MINUTE) {
            return "a minute ago";
        }
        if ($delta < 45 * MINUTE) {
            return floor($delta / MINUTE) . " minutes ago";
        }
        if ($delta < 90 * MINUTE) {
            return "an hour ago";
        }
        if ($delta < 24 * HOUR) {
            return floor($delta / HOUR) . " hours ago";
        }
        if ($delta < 48 * HOUR) {
            return "yesterday";
        }
        if ($delta < 30 * DAY) {
            return floor($delta / DAY) . " days ago";
        }
        if ($delta < 12 * MONTH) {
            $months = floor($delta / DAY / 30);
            return $months <= 1 ? "one month ago" : $months . " months ago";
        } else {
            $years = floor($delta / DAY / 365);
            return $years <= 1 ? "one year ago" : $years . " years ago";
        }
    }

    static function extractDomain($url){
    $nowww = str_replace('www.','',$url);
    $domain = parse_url($nowww);
    if(!empty($domain["host"]))
        {
         return $domain["host"];
         } else
         {
         return $domain["path"];
         }

    }
    
    /////////////////////////////
    /////Private Functions//////
    ////////////////////////////
    private function resetError() {
        $this->lastErrorId = 0;
    }

    private function setError($id) {
        $this->lastErrorId = $id;
        return true;
    }

    private function cutStringByWords($text, $maxLength) {
        $this->resetError();
        if ($text != "") {
            if ($maxLength > 0) {
                if (strlen($text) > $maxLength) {
                    //All ready to work.
                    $aux = "";
                    $vectorWords = split(" ", $text);
                    $con = 0;
                    while (strlen($aux) <= $maxLength) {
                        $aux.=" " . $vectorWords[$con];
                        $aux = trim($aux);
                        $con++;
                    }
                    return $text = $aux;
                } else {
                    return $text;
                }
            } else {
                $this->setError(9);
                return false;
            }
        } else {
            $this->setError(10);
        }
    }

    private function get_links($string) {
        $pattern = '/\<a href\=(.*?)\>(.*?)\<\/a\>/is';
        preg_match_all($pattern, $string, $equals);
        return $equals[0];
    }

    private function get_links_text($string) {
        $pattern = '#[^<ref=">]http\:\/\/([^"<>]*)#is';
        preg_match_all($pattern, $string, $equals);
        return $equals[0];
    }

    private function redlink($link, $longParts) {
        $pattern = '/\<a href\=(.*?)\>(.*?)\<\/a\>/i';
        preg_match_all($pattern, $link, $equals);
        $s = '/\<a href\=(.*?)\>(.*?)\<\/a\>/se';
        if (strlen($equals[2][0]) > ($longParts << 1))
            $r = '"<a href=$1>".substr("$2",0,"$longParts")."...".substr("$2","-$longParts")."</a>"';
        else
            $r='"<a href=$1>$2</a>"';
        $t = preg_replace($s, $r, $link);
        return $t;
    }

    private function redlink2($link, $longParts) {
        if (strlen($link) > ($longParts << 1))
            $r = '<a href="' . $link . '" target="_blank">' . substr($link, 0, $textString) . '...' . substr($link, -$textString) . '</a>';
        else
            $r='<a href="' . $link . '" target="_blank">' . $link . '</a>';
        return $r;
    }

    private function getTextWithLink($textString, $longText = 15) {
        $textString = " " . $textString;
        $links = $this->get_links($textString);
        $urls = $this->get_links_text($textString);
        foreach ($links as $v) {
            $replace[] = $this->redlink($v, $longText);
        }
        foreach ($urls as $v) {
            $replace[] = $this->redlink2($v, $longText);
        }
        $search = array_merge($links, $urls);
        return @trim(str_replace($search, $replace, $textString));
    }

}
?>




