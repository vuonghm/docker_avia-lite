<?php

/**
Author: Uma Mudunuri
Date: 2008
Description: PHP class with various database and XML queries
*/

class FdiQueries {


  /**
  function to get all the input nodes
  input: none
  output: inputs array
  */
  function getInputs() {
    $xpathParams['xpath'] = "/fdi/input";
    $xpathParams['attributeArray'][0] = "id";
    $inRes = XpathUtils::getAttributeValues($xpathParams);
    $inputs = $inRes['id'];
    return $inputs;
  }


  /**
  function to get all the possible output nodes for a given input node
  input: name of input node
  output: outputs array
  */
  function getOutputsForInput($input) {
    $xpathParams['xpath'] = "/fdiPaths/input[@id='$input']/output";
    $xpathParams['attributeArray'][0] = "id";
    $outRes = 
      XpathUtils::getAttributeValues($xpathParams);
    $outputs = $outRes['id'];
    return $outputs;
  }


 /**
  function to get the direct output nodes (single edge) for a given input node
  input: name of input node
  output: outputs array
  */
  function getDirectOutputsForInput($input) {
    $xpathParams['xpath'] = "/fdi/input[@id='$input']/output";
    $xpathParams['attributeArray'][0] = "id";
    $outRes = XpathUtils::getAttributeValues($xpathParams);
    $outputs = $outRes['id'];
    return $outputs;
  }

 /**
  function to get the paths for getting from input node to output node
  input: name of input node, name of output node
  output: paths array
  */
  function getPaths($input, $output) {
    global $fdiPaths_file,$fdi_file;
    if(is_array($FDI['aliases'])) { 
      if(in_array($input, array_keys($FDI['aliases']))) {
        $input = $FDI['aliases'][$input];
      }  
     }
     $xpathParams['xpath'] = "/fdiPaths/input[@id='$input']/output[@id='$output']/pathInfo/*";
     // $file=$xpathParams['rootdir']."/FDI/fdi.xml";
     // print "$fdiPaths_file\n";
      $pathRes = 
        XpathUtils::getNodeValues($xpathParams,$fdiPaths_file);

      return $pathRes;
    }

}

?>