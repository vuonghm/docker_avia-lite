<?php

/**
Author: Uma Mudunuri
Date: 2008
Description: PHP class with functions to execute Oracle queries 
*/

class OraDbUtils {

  /**
  function to connect to an Oracle database 
  input: connection parameters
  output: database connection
  */ 
  function openConn($connParams) {
    $conn = OCILogon($connParams['username'],$connParams['password'],
		     $connParams['databaseName']);
    if (!$conn) {
      $e = oci_error();
      echo "Error while trying to connect to the database: " .
	"$e[message]";
      exit;
    }#ok hv
    return ($conn);
  }
  

  /**
  function to disconnect from an Oracle database
  input: database connection
  */   
  function closeConn($conn) {
    OCILogoff($conn);
  }
  

  /**
  function to parse Oracle query
  input: database connection, Oracle query
  output: parsed statement
  */ 
  function parseQuery($conn, $query) {
    $stmt = OCIParse($conn, $query);
    return ($stmt);
  }
  

  /**
  function to execute Oracle query
  input: parsed statement
  output: executed statement
  */   
  function execQuery($stmt) {
    OCIExecute($stmt, OCI_DEFAULT);
    return ($stmt);
  }

  
  /**
  function to get database results
  input: executed statement
  output: database query results
  */  
  function getResults($stmt) {
    $nrows = oci_fetch_all($stmt, $results);

    # format the results so that results from
    # any database can be accessed the same way
    for($i = 0; $i < $nrows; $i++) {
      reset ($results);

      # $key is the name of the result column 
      while(list($key, $val) = each($results)) {
	$resHash[$key][$i] = $val[$i];
      } 
    }
    return ($resHash);
  }
}

?>
