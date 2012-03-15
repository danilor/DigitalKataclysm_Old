<?php
/*
 * MySQL database manager.
 * A simple class to mange simple MySQL conections and transactions.
 * @author: Danilo Josue Ramirez Mattey
 * */

class MySQLdatabase{
    //Class description
    private $version="1.2";
    private $author="Danilo Josue Ramirez Mattey";
    private $rights="All rights reserved.";
    private $year="2010";
    private $classDescription ="MySQL Server connecting class for PHP.";
    //Database conection info
    private $databaseUser;
    private $databasePassword;
    private $databaseHost;
    private $databaseCon;
    private $databaseName;
    private $secure;
    //Query results info
    private $lastQueryCount;
    private $lastErrorLogId;
    private $lastErrorLogDescription;
    private $lastInsertedId;
    //////////////////////////////
    /////Public Functions////////
    /////////////////////////////
    function __construct($host,$user,$password,$dataName,$secure = false){
        $this->resetError();
        $this->setData($host,$user,$password,$dataName);
        $this->lastQueryCount=0;
        $this->secure = $secure;
        return true;
    }
    function setData($host,$user,$password,$dataName){
        $this->resetError();
        $this->databasePassword=$password;
        $this->databaseUser=$user;
        $this->databaseHost=$host;
        $this->databaseName = $dataName;
    }
    function testConnection(){
        if($this->valid()){
            if($this->connect() != false){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    function getLastErrorDescription(){
        return $this->lastErrorLogDescription;
    }
    function getLastErrorId(){
        return $this->lastErrorLogId;
    }
    function getLastQueryCount(){
        return $this->lastQueryCount;
    }
    function getTableList(){
        return $this->query("show tables");
    }
    function getLastInsertedId(){
        return $this->lastInsertedId;
    }
    function getTableInformation($tableName = ""){
    	$tableName = mysql_real_escape_string($tableName);
        $tableStructure=array();
        if($tableName !=""){
            $result = $this->query("describe ".$tableName);
            $con = 0;
            while($row = mysql_fetch_array($result)){
                $tableStructure[$con]["Field"]=$row["Field"];
                $tableStructure[$con]["Type"]=$row["Type"];
                $tableStructure[$con]["Key"]=$row["Key"];
                $con++;
            }
        }
        return $tableStructure;
    }
    function query($query){
        $this->resetError();
        if($this->validAndConnect()){
            try {
                $result = mysql_query($query,$this->databaseCon);
                $this->lastInsertedId = mysql_insert_id($this->databaseCon);
                $this->lastQueryCount = 0;
                @$this->lastQueryCount = mysql_num_rows($result);
                if( mysql_errno($this->databaseCon) != 0){
                    $this->setError(mysql_errno($this->databaseCon), mysql_error($this->databaseCon));
                    $this->closeConnection();
                    return false;
                }else{
                    $this->closeConnection();
                    return $result;
                }
            } catch (Exception $e) {
                $this->setError(mysql_errno($this->databaseCon), mysql_error($this->databaseCon));
                $this->closeConnection();
                return false;
            }
            $this->closeConnection();
        }else{
            return null;
        }
    }
    function insertData($tableName,$fields){
        $this->resetError();
        if($tableName != "" && count($fields)>0){
            try {
                $sqlQuery = "Insert into ".$tableName."(";
                foreach ($fields as $nameField => $valueField) {
                      $sqlQuery .=mysql_real_escape_string($nameField).",";
                }
                $sqlQuery=substr($sqlQuery,0,-1);
                $sqlQuery .=") values(";
                foreach ($fields as $nameField => $valueField) {
                     $sqlQuery .="'".mysql_real_escape_string($valueField)."',";
                }
                $sqlQuery=substr($sqlQuery,0,-1);
                $sqlQuery .=")";
                return $this->query($sqlQuery);
           } catch (Exception $e) {
               $this->setError(2, $e);
               return false;
           }
        }else{
            $this->setError(2, "Insuficient information");
            return null;
        }
    }
    function updateData($tableName,$fields,$condition){
        $this->resetError();
        if($tableName != "" && count($fields)>0 && $condition != ""){
            try {
                $sqlQuery = "Update ".$tableName." set ";
                foreach ($fields as $nameField => $valueField) {
                      $sqlQuery .= mysql_real_escape_string($nameField) ."='".mysql_real_escape_string($valueField)."',";
                }
                $sqlQuery = substr($sqlQuery,0,-1);
                $sqlQuery .= " where ".$condition;
                return $this->query($sqlQuery);
            } catch (Exception $e) {
                $this->setError(2, $e);
            }
        }else{
            $this->setError(2, "Insuficient information");
            return null;
        }
    }
    function deleteData($tableName,$condition){
        $this->resetError();
        if($tableName != "" &&  $condition != ""){
            try {
                $sqlQuery = "Delete from ".mysql_real_escape_string($tableName);
                $sqlQuery .= " where ".$condition;
                return $this->query($sqlQuery);
            } catch (Exception $e) {
                $this->setError(2, $e);
            }
        }else{
            $this->setError(2, "Insuficient information");
            return null;
        }
    }
    function findData($tableName,$fieldName,$itemFind,$totalFind=false){
            $this->resetError();
            if($tableName != "" &&  $itemFind != "" && $fieldName != ""){
                try {
                    $sqlQuery = "select * from ".$tableName." where ".$fieldName." ";
                    if($totalFind){
                        $sqlQuery.="like '%".$itemFind."%'";
                    }else{
                        $sqlQuery.="='".$itemFind."'";
                    }
                    $this->query($sqlQuery);
                    if($this->lastQueryCount>0){ //Item find
                        return $this->query($sqlQuery);
                    }else{
                        return false;
                    }
                    return $sqlQuery;
                } catch (Exception $e) {
                    $this->setError(2, $e);
                }
            }else{
                $this->setError(2, "Insuficient information");
                return null;
            }
    }
    function __toString(){
        $out="";
        $out .= "Author: ".$this->author." | ";
        $out .= "Version: ".$this->version." | ";
        $out .= "Year: ".$this->year." | ";
        $out .= "Description: ".$this->classDescription." | ";
        if($this->testConnection()){
            $out .= "Connection Status: CONNECTED";
        }else{
            $out .= "Connection Status: NOT CONNECTED";
        }
        return $out;
    }
    function __destruct(){
        $this->closeConnection();
    }
    /////////////////////////////
    /////Private Functions//////
    ////////////////////////////
    private function validAndConnect(){
        if ($this->valid() && $this->connect()){
            $this->connect();
            return true;
        }else{
            return false;
        }
    }
    private function resetError(){
      $this->lastErrorLogDescription="";
      $this->lastErrorLogId=0;
    }
    private function setError($id,$des){
      $this->lastErrorLogId=$id;
      $this->lastErrorLogDescription=$des;
      return true;
    }
    private function connect(){
        $this->resetError();
        //echo $this->databaseHost.$this->databaseUser.$this->databasePassword;
      try{
          if($this->secure){
              $secure = MYSQL_CLIENT_SSL;
          }else{
              $secure = null;
          }
        if (   !    (    @$this->databaseCon=mysql_connect($this->databaseHost,$this->databaseUser,$this->databasePassword,false,$secure)   )    ){
            $this->setError(10, "Error connecting with the database.");
            return false;
        }else{
            if (!@mysql_select_db($this->databaseName,$this->databaseCon)){
                $this->setError(10, "Error finding the database");
                return false;
            }else{
                return $this->databaseCon;
            }
        }
      }catch (Exception $e){
          $this->setError(10, $e);
      }
    }
    private function closeConnection(){
        $this->resetError();
        try {
            @mysql_close($this->databaseCon);
        } catch (Exception $e) {

        }
        return true;
    }
    private function valid(){
        $this->resetError();
        if($this->databaseName == "" || $this->databaseHost == "" || $this->databaseUser == ""){
            $this->setError(1, "Information is missing.");
            return false;
        }else{
            return true;
        }
    }
}
?>