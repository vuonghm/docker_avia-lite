<?php

/**
Author: Uma Mudunuri
Date: 2008
Description: PHP class with common functions to execute 
             Oracle and MySQL database queries 
*/

include_once("OraDbUtils.php");
include_once("MysqlDbUtils.php");
include_once("XpathUtils.php");

class DbUtils {

  /**
  function to connect to the database
  based on the connection settings in fdiConfig.xml
  output: database connection
  */ 
  function openDbConnections(&$fdiDbConn) {
    global $fdi_file,$fdiPaths_file;
    $xpathParams['xpath'] = "/fdi/input/output/db";
    $xpathParams['attributeArray'][0] = "config";
    // print_r($xpathParams);
    $dbRes = XpathUtils::getAttributeValues($xpathParams);
    $dbRes = array_unique($dbRes['config']);
    foreach($dbRes as $key => $configFileName) {
      $xpathParams['xpath'] = "/fdiConfig/*";
      // print_r($xpathParams);
      // echo $_SERVER['DOCUMENT_ROOT']."/FDI/config/$configFileName\n";exit;
      $xRes = XpathUtils::getNodesValues($xpathParams,$_SERVER['DOCUMENT_ROOT']."/FDI/config/$configFileName");#ok
      // print_r($xRes);exit;
      if($xRes['databaseType'] == "oracle") {
        // echo "trying Oracle<br />";
	       $conn = OraDbUtils::openConn($xRes);
	       $fdiDbConn[$configFileName]['dbType'] = "oracle";
      }
      else if($xRes['databaseType'] == "mysql") {
	       $conn = MysqlDbUtils::openConn($xRes);
	       $fdiDbConn[$configFileName]['dbType'] = "mysql";
      }
      if (!$conn){
          echo "not connected<br />";
      }
      $fdiDbConn[$configFileName]['conn'] = $conn;
    }
  }



  /**
  function to disconnect from the database
  input: database connection
  */ 
  function closeDbConnections(&$fdiDbConn) {
    foreach ($fdiDbConn as $key => $configFileName) {
      $db = $fdiDbConn[$configFileName]['dbType'];
      $conn = $fdiDbConn[$configFileName]['conn'];
      if($db == "oracle") {
	OraDbUtils::closeConn($conn);
      }
      else if ($db == "mysql") {
	MysqlDbUtils::closeConn($conn);
      }
    }
  }


  /**
  function to query the database with an existing connection
  input: SQL query, database type, database connection
  output: database results array
  */ 
  function queryDb($query, $db, $conn) {
    if ($db == "oracle") {

      $stmt = OraDbUtils::parseQuery($conn, $query);
      $stmt = OraDbUtils::execQuery($stmt);

      $dbResults =  OraDbUtils::getResults($stmt);
       // print "Querying ($query) and the results is : \n";print_r($dbResults);sleep(2);
      ociCommit($conn);
    }
    else if ($db == "mysql") {
      $dbResults = MysqlDbUtils::getResults($query);
    }
    return $dbResults;
  }

}
