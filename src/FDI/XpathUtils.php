<?php

/**
Author: Uma Mudunuri
Date: 2008
Description: PHP class containing various functions to get 
             information from XML files 
*/

class XpathUtils {
  
  /**
  common function that executes the xpath query
  input: xpath
  output: all the nodes that satisfy the query
  */   
  function runXpathQuery($xpathParams, $file) {
    // echo "<font color=\"red\">$file</font><br />";
    if (file_exists ($file)){
      // echo "file $file exists in runXpathQuery<br />";
    }
    $dom = new DomDocument();
    $status=$dom->load($file);
    // echo "The status is:$status<br />";
    $xpath = new domxpath($dom);
    // echo "In XpathUtils.php PATH:". $xpathParams['xpath']."|<br />printing params:";
    // print_r($xpathParams);
// echo "<br/><br/>nodeList: <br/>";
    $nodeList = $xpath->query($xpathParams['xpath']);
    //print_r($nodeList);
    // print_r($nodeList->saveXML());
    // echo "(done printing nodeList in runXPathQuery)<br />";
    // print "in XpathUtils line 33\n";print_r($nodeList);exit;
    return ($nodeList);

  }

  /**
  function to get the values of the requested attributes for all  
  the nodes that satisfy the xpath query
  input: xpath and attribute array
  output: result array with the values of all the attributes
  */ 

  function getAttributeValues($xpathParams, $file) {#hv checked
    // echo "$file<hr/>";
    // echo getcwd()."<hr/>";
    if (!file_exists ($file)){
      global $fdi_file;
      $file="/mnt/webrepo/fr-s-abcc-avia-l/htdocs/FDI/fdi.xml";
    }
    $nodeList = XpathUtils::runXpathQuery($xpathParams, $file);
    // print_r($xpathParams['attributeArray']);
    $attrArray = $xpathParams['attributeArray'];
    echo "attrvalue:";print_r($xpathParams['attributeArray']);echo "<br />";
    $valArr = array();
    $nodeCount = 0;
    foreach ($nodeList as $node) {
       for ($i = 0; $i < sizeof($attrArray); $i++) {
      	$valArr[$attrArray[$i]][$nodeCount] = 
      	  $node->attributes->getNamedItem($attrArray[$i])->nodeValue;
      }

      $nodeCount++;
    }
    // echo "valArr:($nodeCount)";  print_r($valArr);
        // echo "In XpathUtils getAttributeValues and this is not printing anything:";
    print_r($valArr);
    // echo "(done nodeList)<br />";
    return ($valArr);
  }

  /**
  gets the values of the requested elements of a particular node
  input: xpath
  output: node values array
  */
  function getNodeElementValues($xpathParams, $file) {

    if (!file_exists ($file)){
      global $fdi_file;
      $file=$fdi_file;
      //echo "Something's wrong!!";
    }

    $nodeList = XpathUtils::runXpathQuery($xpathParams, $file);
    $elements = $xpathParams['elements'];
    $x = 0;

    foreach ($nodeList as $node) {
      foreach($elements as $key => $element) {	
	$values = $node->getElementsByTagName($element);
	foreach ($values as $value) {
	  $nodeValues[$element][$x] = $value->textContent;
	}
      }
      $x++;
    }    
    // echo "In XpathUtils getNodeElementValues and this is not printing anything:";
    // print_r($nodeValues);
    // echo "(done nodeList)<br />";

    return ($nodeValues);
  }

  /**
  gets the values of all the nodes (diff names)that satisfy the xpath query
  input: xpath
  output: node values array
  */
  function getNodesValues($xpathParams, $file) {
    if (!file_exists ($file)){
      global $fdi_file;
      $file=$fdi_file;
      //echo "Something's wrong!!";
    }
    $nodeList = XpathUtils::runXpathQuery($xpathParams, $file);

    foreach ($nodeList as $node) {
      if($node->nodeType == 1) {
	$nodeValues[$node->nodeName] = $node->textContent;
      }
    }
     // echo "In XpathUtils getNodesValues and this is not printing anything:";
    // print_r($nodeValues);
    // echo "(done nodeList)<br />";
    return ($nodeValues);
  }

  /**
  gets the values of all the nodes (can have same node name)
  that satisfy the xpath query
  input: xpath
  output: node values array
  */
  function getNodeValues($xpathParams, $file) {

    if (!file_exists ($file)){
      global $fdi_file;
      $file=$fdi_file;
      // echo "Something's wrong!!";
      // echo $file;exit;

    }
    $nodeList = XpathUtils::runXpathQuery($xpathParams,$file);#hv enter

    foreach ($nodeList as $node) {
      $val = $node->textContent;
      $key = $node->nodeName;
      $nodeValues[$key][] = $val;
    }
    return ($nodeValues);
  }

}

?>