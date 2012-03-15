<?php

/**
 * XML Class
 *
 * @package	xml
 * @category	Xml
 * @author	Danilo Josué Ramírez Mattey
 * @version     1.0
 * @access      Public
 */

class Xml{
    function __construct() {
        return true;
    }

    /**
     * arrayToXml
     *
     * @link http://www.codigogratis.com.ar/-post-273-funcion_php_que_convierte_cualquier_array_en_xml.html
     * @access public
     * @param array Array with the information to turn into an XML
     * @param lastkey OPTIONAL THe root node to enclose all the XML
     * @return string The builded XML.
     */

    function arrayToXml($array,$lastkey='root'){
        $output = '';
        $output.="<".$lastkey."> ";
        if (!is_array($array)) {$output.=trim($array);}
        else{
            foreach($array as $key=>$value) {
                if (is_array($value)) {
                    if ( is_numeric(key($value))) {
                        foreach($value as $bkey=>$bvalue) {
                            $output.=$this->arrayToXml($bvalue,$key);
                        }
                    }else{
                        $output.=$this->arrayToXml($value,$key);
                    }
                }else{
                        $output.=$this->arrayToXml($value,$key);
                }
            }
        }
        $output.="</".$lastkey."> ";
        return $output;
    }

    /**
    * _struct_to_array($values, &$i)
    *
    * This is adds the contents of the return xml into the array for easier processing.
    * Recursive, Static
    *
    * @access    private
    * @param    array  $values this is the xml data in an array
    * @param    int    $i  this is the current location in the array
    * @return    Array
    */
     function _struct_to_array($values, &$i){
        $child = array();
        if (isset($values[$i]['value'])) array_push($child, $values[$i]['value']);

        while ($i++ < count($values)) {
            switch ($values[$i]['type']) {
                case 'cdata':
                array_push($child, $values[$i]['value']);
                break;

                case 'complete':
                    $name = $values[$i]['tag'];
                    if(!empty($name)){
                    $child[$name]= ($values[$i]['value'])?($values[$i]['value']):'';
                    if(isset($values[$i]['attributes'])) {
                        $child[$name] = $values[$i]['attributes'];
                    }
                }
              break;

                case 'open':
                    $name = $values[$i]['tag'];
                    $size = isset($child[$name]) ? sizeof($child[$name]) : 0;
                    $child[$name][$size] = $this->_struct_to_array($values, $i);
                break;

                case 'close':
                return $child;
                break;
            }
        }
        return $child;
    }//_struct_to_array

    /**
    * createArray($data)
    *
    * This is adds the contents of the return xml into the array for easier processing.
    *
    * @access    public
    * @param    string    $data this is the string of the xml data
    * @return    Array
    */
    function createArray($xml){
        $values = array();
        $index  = array();
        $array  = array();
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parse_into_struct($parser, $xml, $values, $index);
        xml_parser_free($parser);
        $i = 0;
        $name = $values[$i]['tag'];
        $array[$name] = isset($values[$i]['attributes']) ? $values[$i]['attributes'] : '';
        $array[$name] = $this->_struct_to_array($values, $i);
        return $array;
    }//createArray
    
    
    function value_in($element_name, $xml, $content_only = true) {
        if ($xml == false) {
            return false;
        }
        $found = preg_match('#<'.$element_name.'(?:\s+[^>]+)?>(.*?)'.
                '</'.$element_name.'>#s', $xml, $matches);
        if ($found != false) {
            if ($content_only) {
                return $matches[1];  //ignore the enclosing tags
            } else {
                return $matches[0];  //return the full pattern match
            }
        }
        // No match found: return false.
        return false;
    }


}





/* End of file xml.class.php */
/* Location: /classes/xml.class.php */