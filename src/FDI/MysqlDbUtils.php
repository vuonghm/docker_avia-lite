<?php

/**
Author: Uma Mudunuri
Date: 2008
Description: PHP class with functions to execute MySQL queries 
*/

class MysqlDbUtils {

  /**
  function to connect to an MySQL database
  input: connection parameters
  output: database connection
  */ 
  function openConn($connParams) {

    $host = $connParams['host'];
    if($connParams['port']) { $host = $host . ":" . $connParams['port']; }
    
    $conn = mysql_connect($host, $connParams['username'], 
			  $connParams['password']);
        mysql_select_db($connParams['databaseName'], $conn);
    $e = mysql_error();
    if($e) {
      echo "Error while trying to connect to the database: " . $e;
      exit;
    }
    return ($conn);
  }
  

  /**
  function to disconnect from a MySQL database
  input: database connection
  */   
  function closeConn($conn) {
    mysql_close($conn);
  }  


  /**
  function to query and get database results
  input: SQL query
  output: database query results
  */   
  function getResults($query) {
    $result = mysql_query($query);
    $i = 0;
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
      foreach($row as $key => $val) {
	$resHash[$key][$i] = $val;
      }
      $i++;
    }
    mysql_free_result($result);
    return ($resHash);
  }

}

?>
