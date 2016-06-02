<?php

/**
Author: Uma Mudunuri
Date: 2008
Description: PHP class with functions to execute db2db, dbReport and dbWalk
*/
include_once("DbUtils.php");
include_once("GenUtils.php");
include_once ("FdiQueries.php");
include_once ("fdiDefinitions.php");

class FdiUtils {
    function executeDb2dbAll($fdiParams) {
      // print_r($fdiParams);
    $fdiParams['paths'] = array();
    $input = $fdiParams['input'];
    $outputs = $fdiParams['outputs'];
    $connectors = Array("Variant ID", "Variant OnDeck");

    if(!$fdiParams['dbConn']) {
      $conn = DbUtils::openDbConnections($fdiDbConn);
      $fdiParams['dbConn'] = $conn;
    }
    // print "at line 25 in FDIUtils:\n";print_r($fdiParams);exit;
    $notConnectorOutputs = array_diff($outputs, $connectors);

    if($notConnectorOutputs) {
      $fdiParams['connectors'] = $connectors;
      $fdiParams['outputs'] = $connectors;
      $fdiParams=FdiUtils::executeDb2db($fdiParams);
      
      $fdiParams['completedOutputs'] = $connectors;
      if(!in_array($input, $fdiParams['completedOutputs'])) {
            $fdiParams['completedOutputs'][] = $input;
      }
      $fdiParams['outputs'] = $notConnectorOutputs;
    }
    else {
      $fdiParams['outputs'] = $outputs;
    }
      // print_r($fdiParams);exit;
    $fdiParams['connectors'] = array();
    $fdiParams['paths'] = array();
// print_r($fdiParams);
    $fdiParams = FdiUtils::executeDb2db($fdiParams);

    $fdiParams['outputs'] = $outputs;
    // print_r($conn);
    if($conn) {
      DbUtils::closeDbConnection($conn);
    }
    return $fdiParams;
  }
  

  /**
  function for executing db2db
  input: fdiParams array handle containing all the input parameters
  output: fdiParams array handle with results
  */
  function executeDb2db($fdiParams) {#hv check
    $input = $fdiParams['input'];
    $outputs = $fdiParams['outputs'];
    $fdiRes = $fdiParams['results'];
    DbUtils::openDbConnections($fdiDbConn);#ok
    $fdiParams['dbConn'] = $fdiDbConn;
    foreach($outputs as $key => $output) {
      $paths = array();
      $pathRes = FdiQueries::getPaths($input, $output);#hv
      // print "results:\n";print_r($pathRes);exit;
      $fdiParams['results'] = $fdiRes;
      // additional information for showing the network paths
      for($i=0; $i < count($pathRes['path']); $i++) {
    	  $fdiParams['pathInfo'][$pathRes['path'][$i]]['distance'] 
    	    = $pathRes['distance'][$i];
    	  $fdiParams['pathInfo'][$pathRes['path'][$i]]['weight'] 
    	    = $pathRes['weight'][$i];
    	  $paths[] = $pathRes['path'][$i];
      }
      $tmpRes = FdiUtils::walkAllPaths($fdiParams, $paths);
      // merge results for each output with the main results array
      if(is_array($tmpRes)) {
      	if(!is_array($fdiRes)) { $fdiRes = array(); }
      	FdiUtils::array_merge_valueOutputs($fdiRes, $tmpRes);
            }
            $fdiParams['results'] = $fdiRes;
      }
    DbUtils::closeDbConnections($fdiParams['dbConn']);
    return $fdiParams;
  }

  /**
  function for executing dbWalk
  input: fdiParams array handle containing all the input parameters
  output: fdiParams array handle with results
  */
  function executeDbWalk(&$fdiParams) {
    $fdiRes = $fdiParams['results'];
    $dbPath = $fdiParams['dbPath'];

    DbUtils::openDbConnections($fdiDbConn);
    $fdiParams['dbConn'] = $fdiDbConn;

    // execute walkPath function with user defined path 
    if ($fdiParams['debug']==1){echo "about to walk path(80)<br />";}
    $dRes = FdiUtils::walkPath($fdiParams, $dbPath);
    DbUtils::closeDbConnections($fdiDbConn);

    // set the fdiParams variables to print the results 
    // in required format
    $pathArr = preg_split("/->/",$dbPath,-1 );
    $fdiParams['input'] = $pathArr[0];
    $fdiParams['results'] = $dRes;
    $fdiParams['outputs'] = array_slice($pathArr,1);
  }


  /**
  function for executing dbReport
  input: fdiParams array handle containing all the input parameters
  output: fdiParams array handle with results
  */
  function executeDbReport(&$fdiParams) {#hv checked
    // get all possible outputs for the input node and execute db2db
    $input = $fdiParams['input'];
    if (array_key_exists('debug',$fdiParams)){
      // print_r($fdiParams);echo "just printed xpathparams...persists?";
     // echo "running fdiQueries::getOutputsForInput<br />";
    }
    $outputs = FdiQueries::getOutputsForInput($input);#hv
    //  print "In executeDbReport:";
    // print_r($outputs);
    //  print "printed done<br/>";
    $fdiParams['outputs'] = $outputs;
    FdiUtils::executeDb2db($fdiParams);#hv
  }


  /**
  function for walking all possible paths for getting 
  from input to output
  input: fdiParams array handle containing all the input parameters, 
         paths array with all possible paths
  output: results array for that input to output conversion
  */
  function walkAllPaths($fdiParams, $paths) {

    $dRes = array();
    $inputValues = $fdiParams['inputValues'];

    foreach ($paths as $key => $path) {
      //print "$path . <br/>";

      // execute walkPath function for each of the possible paths
      $tmpRes = FdiUtils::walkPath($fdiParams, $path);
      // print_r($tmpRes);echo "IN WALKALLPATHS:<Br />";
      $valuesFound = array(); $diff = array();

      //print_r($tmpRes);

      // if results are obtained from this path
      // combine them with any previous results
      if($fdiParams['found']=="yes" ) {
      	$dRes = $tmpRes + $dRes; // order of addition important

      	$valuesFound = array();
      	foreach($tmpRes as $in => $outArr) {
      	  if(array_key_exists($fdiParams['output'], $outArr)) {
      	    $valuesFound[] = $in;
      	  }
      	}
      }

      // walk the paths until results are obtained for all
      // input values or all possible paths are exhausted
      if(count($valuesFound) > 0) {
    	$diff = array_diff($fdiParams['inputValues'], $valuesFound);
          }
          else { // none of the inputs have results 
    	$diff = $fdiParams['inputValues'];
          }

          if(count($diff) > 0) { // if there are some inputs without results
    	$fdiParams['inputValues'] = $diff; // for next iteration
          }
          else {
    	$fdiParams['inputValues'] = $inputValues;
    	return $dRes;  // exit loop if all inputs have results    
          }
    }
    $fdiParams['inputValues'] = $inputValues;
    // print_r($dRes);echo "printing dRes in walkallpaths<br />";
    return $dRes;
  }


  /**
  function for walking a path for getting from input to output
  input: fdiParams array handle containing all the input parameters, 
         path string to get from input to output
  output: results array for that input to output conversion
  */
  function walkPath($fdiParams, $path) {
    // split the path on '->' 
    $pathArr = preg_split("/->/",$path,-1 );
    $input = $pathArr[0];
    $output = $pathArr[count($pathArr)-1];
    $fdiParams['output'] = $output;
    $tmpInputValues = $fdiParams['inputValues'];
    $outputs = array();
    $dRes = array();
    $edgeParams['dbConnAll'] = $fdiParams['dbConn'];

    // recursively iterate through the edges by splitting the path  
    for($i=0; $i < (sizeof($pathArr)-1); $i++) {
      $tmpInput = $pathArr[$i];
      $tmpOutput = $pathArr[$i + 1];     
/////here insert code20140206

      if((count($fdiParams['connectors']) == 0) and (is_array($fdiParams['connector']))) {
          if(in_array($tmpOutput, array_keys($fdiParams['connector']) )) {
            $dRes = $fdiParams['results'];
            $tmpInputValues = $fdiParams['connector'][$tmpOutput];
            $tmpPrevValues = $tmpInputValues;
            continue;
          }
      }
     
      $interRes = array();
      if((count($tmpRes) > 0) || (!is_array($tmpRes))) {
	
        	$edgeParams['input'] = "$tmpInput";
        	$edgeParams['output'] = $tmpOutput;
        	$edgeParams['inputValues'] = $tmpInputValues;
          $edgeValues=$fdiParams['addons'];
          foreach ($edgeValues as $key=>$value){
            $edgeParams[$value] = $fdiParams[$value];
          }
        	$tmpRes = FdiUtils::walkEdge($edgeParams);


        if($tmpRes[$tmpOutput]) {
          if(count($fdiParams['connectors']) > 0) {
            if(in_array($tmpOutput, $fdiParams['connectors'])) {
              if(!is_array($fdiParams['connector'][$tmpOutput])) { 
                $fdiParams['connector'][$tmpOutput] = array(); }

              #$fdiParams['connector'][$tmpOutput] = 
                #$tmpRes[$tmpOutput] + $fdiParams['connector'][$tmpOutput];
              FdiUtils::appendDbResults($fdiParams['connector'], $tmpRes); 
            }
          }
        }

        // assign results such that all the results obtained by
        // intermediate edges are connected to the main input
        $interRes = FdiUtils::assignFdiResults($tmpRes, $tmpInput, $tmpOutput);

      	if($i == 0) {
      	  $dRes = $interRes;
      	}
      	else {
      	  FdiUtils::assignRecursiveFdiResults($dRes, $interRes, $tmpInput, $tmpOutput);
          // print_r($tmpRes[$tmpOutput]);echo "printing tmpRes after AssignRecursive<br />";
      	}
      }
      $tmpInputValues = $tmpRes[$tmpOutput];
    }
    // assign variable values to send back to walkAllPaths
    if(($tmpOutput == $output) && (count($tmpRes) > 0)) {
      $fdiParams['found'] = "yes";
    }
    else {
      $fdiParams['found'] = "no";
    }
    $fdiParams['paths'][] = $path;
     //print_r($dRes);echo "Just printed dres<br />";

    return $dRes;
  }
  
  function walkEdge($edgeParams) {

    $input = $edgeParams['input'];
    $output = $edgeParams['output'];
    $xpathParams['xpath'] = "/fdi/input[@id='$input']/output[@id='$output']/db";
    $xpathParams['attributeArray'][0] = "config";
    $dbRes = XpathUtils::getAttributeValues($xpathParams);
// print_r($dbRes);sleep (10);
    $xpathParams['xpath'] = "/fdi/input[@id='$input']/output[@id='$output']/api";
    $xpathParams['attributeArray'][0] = "cmd";
    $apiRes = XpathUtils::getAttributeValues($xpathParams);
    if($dbRes) {
      $edgeParams['dbConnection'] = $edgeParams['dbConnAll'][$dbRes['config'][0]]['conn'];
      $edgeParams['dbType'] = $edgeParams['dbConnAll'][$dbRes['config'][0]]['dbType'];
      
      $edgeRes = FdiUtils::walkDbEdge($edgeParams);
      return $edgeRes;
   }   else if($apiRes) {
      
      if (preg_match("/userdb(\d+)/",$apiRes['cmd'][0],$matches)){
        // echo "DEBUG ..changing " . $apiRes['cmd'][0] . " and $matches[1]<br />";
        $map_arr=$edgeParams['mapCustom'];
          $edgeParams['cmd'] = preg_replace("/userdb$matches[1]/",$map_arr[$matches[1]],$apiRes['cmd'][0]);
      }else{
        $edgeParams['cmd'] = $apiRes['cmd'][0];
      }
      $edgeRes = FdiUtils::walkCodeEdge($edgeParams);##STAT
      return $edgeRes;
    }
  }

  function walkCodeEdge($edgeParams) {
    $inputVals = implode(",", $edgeParams['inputValues']);
    // echo "$inputVals...<br />";
    $output = $edgeParams['output'];
    // print_r($output);
    $input = $edgeParams['input'];
    $tmpRes = array();
    $cmd = $edgeParams['cmd'] . " " .  $edgeParams['avialite_id'] ;
    $results =`$cmd 2>&1`;
    echo "The command is $cmd\n";

    $res = explode("\n",$results);
    foreach($res as $key => $value) {
      $eachRes = explode("\t",$value);
      $tmpRes[$input][] = $eachRes[0];
      $tmpRes[$output][] = $eachRes[1];
    }
    return $tmpRes;
  }


  /**
  function for walking an edge in the network
  input: edgeParams handle from the walkPath() function
  output: results array for that edge conversion
  */
  function walkDbEdge($edgeParams) {
    $input = $edgeParams['input'];
    $output = $edgeParams['output'];
    $inputValues = $edgeParams['inputValues'];
    $conn = $edgeParams['dbConnection'];
    $dbType = $edgeParams['dbType'];
    $dRes = array();
    // assign the right name for the i;nput node
    $tmpInput=$input;
    if(is_array($FDI['aliases'])) {
     if(in_array($input, array_keys($FDI['aliases']))) {
      $tmpInput = $FDI['aliases'][$input];
     }
     else {
      $tmpInput = $input;
     }
    }

    // get all the required values for the edge from fdi.xml
    $xpathParams['xpath'] = "/fdi/input[@id='$tmpInput']/output[@id='$output']/db/*";
    $xRes = XpathUtils::getNodeValues($xpathParams);
      for($i=0; $i < count($xRes['inputColumn']); $i++) { //for nodes connected by more than one edge
        // execute prepareFdiQuery to get the partial select 
        // statement without input values

        $dbQuery = FdiUtils::prepareFdiQuery($xRes, $input, $output, $i);
        $dbQuery .=  $xRes['inputColumn'][$i];

        FdiUtils::completeFdiQuery($dbQuery, $inputValues);
        // query the database with the select statement
        $tmpRes = DbUtils::queryDb($dbQuery, $dbType, $conn);	
        // combine reults if the same nodes are connected by multiple edges
        if(is_array($tmpRes)) {
        	if((count($dRes)) == 0) {
        	  $dRes = $tmpRes;
        	}
        	else {
        	  FdiUtils::appendDbResults($dRes, $tmpRes);
        	}
        }			
      }
    // }
    return $dRes;
  }


  /**
  function for preparing partial SQL select statement
  input: results for the edge from fdi.xml, input, output 
         and count (for multiple edges)
  output: partial select query
  */
  function prepareFdiQuery($xRes, $input, $output, $i) {

    $sel = "select distinct " . $xRes['inputColumn'][$i] . " as \"" . 
      $input . "\", " . $xRes['outputColumn'][$i] . " as \"" . $output . "\"";
    $from = " from " . $xRes['tableName'][$i];
    $where = " where " ;

    if(array_key_exists('whereClause', $xRes)) {
      $where .= $xRes['whereClause'][$i] . " and " ;
    } 
    $query = $sel . $from . $where;  
    return $query;
  }


  /**
  function for completing SQL select statement
  by including the input values in the where clause
  input: partial SQL query, array of input values
  output: completed select query
  */
  function completeFdiQuery(&$dbQuery, &$inputValues) {

    $partialDbQuery = $dbQuery;
    $dbQuery = "";
    // SQL 'in' clause allows only 1000 values
    // if there are more input values split the 
    // select query and combine results
    if(count($inputValues) > 1000) {
      $inputVals = array_chunk($inputValues, 1000);
      for($x=0; $x < count($inputVals); $x++) {
	$inputStr = implode("','", $inputVals[$x]);
	$dbQuery .= $partialDbQuery . " in ('" . $inputStr . "')";
	if (!($x == (count($inputVals) - 1))) {
	  $dbQuery .= " UNION ALL ";	  
	}
      }
    }
    elseif (count($inputValues) > 1) {
      $inputStr = implode("','", $inputValues);
      $dbQuery = $partialDbQuery . " in ('" . $inputStr . "')";
    }
    elseif (count($inputValues) == 1) {
      $inKeys = array_keys($inputValues);
      $dbQuery = $partialDbQuery . " = '" . $inputValues[$inKeys[0]] . "'";
    }
    else {
      $dbQuery = $partialDbQuery . " = ' ' ";
    }    
  }


  /**
  function to combine two result arrays
  input: mainArray handle to which the results have to be appended,
         tmpArray handle containing the results to be appended
  output: mainArray with results appended
  */
  function appendDbResults(&$mainRes, &$tmpRes) {
    foreach($tmpRes as $columnName => $values) {
      foreach($values as $key2 => $value) {
	if(!in_array($value, $mainRes[$columnName])) {
	  $mainRes[$columnName][] = $value;
	}
      }
    }
  }


  /**
  function to assign results to the input values
  */
  function assignFdiResults(&$tmpRes, $input, $output) {
    if(is_array($tmpRes[$input])) {
      //split $output on ;
      //$outputs = array()
      //foreach $outputs
      $res=array();
      foreach($tmpRes[$input] as $key => $value) {

	       $res[$value][$output][] = $tmpRes[$output][$key];
      }
    }
    return $res;
  }


  /**
  function to assign all the results to the input values
  */
  function assignAllFdiResults(&$tmpRes) {
    $colNames = array_keys($tmpRes);
    foreach($tmpRes[$colNames[0]] as $key => $value) {
      for($i = 1; $i < sizeof($colNames); $i++) {
	 $res[$value][$colNames[$i]][] = $tmpRes[$colNames[$i]][$key];
      }
    }
    $tmpRes = $res;
  }


  /**
  function to assign all the intermediate results to the main input value
  */
  function assignRecursiveFdiResults(&$mainRes, &$interRes, $interOutput, $output) {

    if (preg_match("/(FunSeq)/i",$output,$matches)){
       // echo "working on $output......<br />";
// print_r($mainRes);echo "<br /><br />";
// print_r($interRes);echo "INTERRES<br />";
       //get the headers for the interRes.
      $out=array();
      foreach ($interRes as $inputVal=>$outputArr){
        foreach ($outputArr as $outIdx=>$outVal){
          if (!array_key_exists($outIdx,$out)){
            $out[$outIdx]='1';
          }
        }
      }
      // print_r($out);
      if(is_array($mainRes)) {
        foreach($mainRes as $inputVal => $outputArr) {
          // echo "&nbsp;working on $outputArr<br />";
          if(is_array($outputArr[$interOutput])) {
            foreach($outputArr[$interOutput] as $numKey => $interOutVal) {
              // echo "&nbsp;&nbsp;2)working on $interOutVal<br />";
              if(is_array($interRes[$interOutVal])) {
                // echo "in this arr..<br />";
                foreach($out as $key => $value) {
                  // echo "&nbsp;&nbsp;&nbsp;3)working on ($key)$interOutVal|||".$interRes[$interOutVal][$key][0]. "<br />";
                  // print "========adding $inputVal for $output ($key).".$interRes[$interOutVal][$key][0]."<br />";
                   $mainRes[$inputVal][$key][] = $interRes[$interOutVal][$key][0];
                }
              }
            }
          }
        }
      }
    }else{
      if(is_array($mainRes)) {
        foreach($mainRes as $inputVal => $outputArr) {
          if(is_array($outputArr[$interOutput])) {
            foreach($outputArr[$interOutput] as $numKey => $interOutVal) {
              if(is_array($interRes[$interOutVal])) {
                foreach($interRes[$interOutVal][$output] as $key => $value) {
                  // print "adding $inputVal for $output ($key)$value<br />";
                $mainRes[$inputVal][$output][] = $value;
                }
              }
            }
          }
        }
      }
    } 
  }


  /**
  function to combine two arrays
  */
  function array_merge_values(&$mainArray, &$tmpArray) {
    if(!is_array($mainArray)) { $mainArray = array(); }
    if(!is_array($tmpArray)) { $tmpArray = array(); }
    $mergeArray = $mainArray + $tmpArray;
    $mainArray = $mergeArray;
  }


  /**
  function to combine values of two arrays
  */
  function array_merge_valueOutputs(&$mainArray, &$tmpArray) {

    $mainValues = array_keys($mainArray); 
    $tmpValues = array_keys($tmpArray); 

    $val1 = array_diff($mainValues, $tmpValues);
    $val2 = array_diff($tmpValues, $mainValues);
    $val3 = array_intersect($mainValues, $tmpValues);
    foreach($val1 as $key => $value) {
      $mergeArray[$value] = $mainArray[$value];
    }
    foreach($val2 as $key => $value) {
      $mergeArray[$value] = $tmpArray[$value];
    }
    foreach($val3 as $key => $value) {
      $resValue = $tmpArray[$value] + $mainArray[$value];
      $mergeArray[$value] = $resValue;
    }
    $mainArray = $mergeArray;
  }

}
