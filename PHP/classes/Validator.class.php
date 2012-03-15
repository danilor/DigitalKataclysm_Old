<?php

/*
 * Validator manager.
 * A class to validate variety of information.
 * @author: Danilo Josue Ramirez Mattey
 * */

class Validator {

    //Class description
    private $version = "1.4";
    private $author = "Danilo Josue Ramirez Mattey";
    private $rights = "All rigths reserved.";
    private $year = "2010";
    private $classDescription = "Validator for PHP";
    //Reporting Variables
    private $lastErrorId;
    private $errors = array(
        0 => "",
        1 => "Data introduced is not a number.",
        2 => "Zipcode invalid.",
        3 => "Password length incorrect.",
        4 => "Passwords do not match.",
        5 => "Passwords empty.",
        6 => "Username have to start with a letter.",
        7 => "Username length incorrect.",
        8 => "Username empty.",
        9 => "The length cannot be 0.",
        10 => "Text cannot be empty.",
        11 => "Email invalid",
        12 => "Missing information",
        13 => "Regular failed"
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

    function validStringStructure($input, $structure) {
        //Here you have to use # if you want to use numbers or ? if you want to use letters
        $this->resetError();
        if (!($input == "" || $structure == "")) {
            if (!preg_match("/" . str_replace(array("#", "?", "/"), array("[0-9]", "[A-Za-z]", "\/"), $structure) . "/", $input)) {
                $this->setError(13);
                return 0;
            } else {
                return 1;
            }
        } else {
            $this->setError(12);
        }
    }

    function validReg($input, $exp) {
        $this->resetError();
        if (!($input == "" || $exp == "")) {
            if (!preg_match($exp, $input)) {
                $this->setError(13);
                return 0;
            } else {
                return 1;
            }
        } else {
            $this->setError(12);
        }
    }

    function validEmail($email) {
        $this->resetError();
        if (!preg_match("/^([a-zA-Z0-9_\-\.]+)@(([\-]|[\']|[\/])*[a-zA-Z0-9]+)(([\-]|[\']|[\/])*[a-zA-Z0-9]*)*([\.]+[a-zA-Z0-9]{2,})+$/", $email)) {
            $this->setError(11);
            return 0;
        } else {
            return 1;
        }
    }

    function validNumber($number, $numberLengthMin, $numberLengthMax=0) {
        $this->resetError();
        $isValid = false;
        if ($numberLengthMax < $numberLengthMin) {
            $numberLengthMax = $numberLengthMin;
        }
        for ($i = $numberLengthMin; $i <= $numberLengthMax; $i++) {
            if (preg_match("/^([0-9]{" . $i . "})$/i", $number)) {
                $isValid = true;
            }
        }
        if (!$isValid) {
            $this->setError(1);
        }
        return $isValid;
    }

    function validZipcode($zip, $length=5) {
        $this->resetError();
        if ($this->validNumber($zip, $length) == true) {
            return true;
        } else {
            $this->setError(2);
            return false;
        }
    }

    function validExpirationDate($theDate, $patern = "/^[0-2][0-9]\/[0-9][0-9]$/") {
        $this->resetError();
        return preg_match($patern, $theDate);
    }

    function validDate($theDate, $patern = "/^[0-2][0-9]\/[0-9][0-9]\/[0-9][0-9][0-9][0-9]$/") {
        $this->resetError();
        return preg_match($patern, $theDate);
    }

    function validPasswords($pass1, $pass2, $lengthMin=0, $lenghtMax=9999) {
        $this->resetError();
        if ($pass1 != "" && $pass2 != "") {
            if ($pass1 == $pass2) {
                if ((strlen($pass1) >= $lengthMin) && (strlen($pass2) <= $lenghtMax)) {
                    return true;
                } else {
                    $this->setError(3);
                    return false;
                }
            } else {
                $this->setError(4);
                return false;
            }
        } else {
            $this->setError(5);
            return false;
        }
    }

    function validUsername($userName, $lengthMin=0, $lengthMax=9999, $strict=true) {
        if ($userName != "") {
            $firstChar = substr($userName, 0, 1);
            if ($strict) {
                if (!preg_match("/^[a-zA-Z|'|-| ]$/i", $firstChar)) {
                    $this->setError(6);
                    return false;
                }
            }
            if (strlen($userName) >= $lengthMin && strlen($userName) <= $lengthMax) {
                return true;
            } else {
                $this->setError(7);
                return false;
            }
        } else {
            $this->setError(8);
            return false;
        }
    }

    function validString($inString, $lengthMin, $lengthMax) {
        if ($inString != "") {
            if (strlen($inString) >= $lengthMin && strlen($inString) <= $lengthMax) {
                return true;
            } else {
                $this->setError(7);
                return false;
            }
        } else {
            $this->setError(8);
            return false;
        }
    }

    function sanitize($input) {
        // I would suggest only allowing A-Z, 0-9 and @, ., !, - and _ in your scripts - bellsworth
        $regex = "/[a-z|0-9|\-|?|_|.|\/|@|!|:|;|\'|+|;|(|)|,| ]/i";
        preg_match_all($regex, $input, $matches);
        $output = "";
        foreach ($matches[0] as $v) {
            $output .= $v;
        }
        return $output;
    }

    function validSegmentURL($input) {
        $pos = strrpos($input, " ");
        if ($pos === false) {
            $regex = "/[a-z0-9_?!]/i";
            preg_match_all($regex, $input, $matches);
            $output = "";
            foreach ($matches[0] as $v) {
                $output .= $v;
            }
            if ($output == $input) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function getRealIPAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    function __toString() {
        $out = "";
        $out .= "Author: " . $this->author . " | ";
        $out .= "Version: " . $this->version . " | ";
        $out .= "Year: " . $this->year . " | ";
        $out .= "Description: " . $this->classDescription;
        return $out;
    }

    function __destruct() {

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

}

?>
