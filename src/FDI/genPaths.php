<?php

/**
Author: Uma Mudunuri
Date: 2008
Description: PHP code to generate paths of the FDI network 
    input file: fdi.xml
    output file: fdiPaths.xml and fdiPaths.txt files
*/

include_once("FdiUtils.php");

// get all the input nodes defined in fdi.xml
$xpathParams['xpath'] = "/fdi/input";
$xpathParams['attributeArray'][0] = "id";
$inRes = XpathUtils::getAttributeValues($xpathParams);

$tmpPathArr = array();

for($i=0; $i < sizeof($inRes['id']); $i++) {
  // get output nodes for each of the inputs
  $input = $inRes['id'][$i];
  $xpathParams['xpath'] = "/fdi/input[@id='$input']/output";
  $outRes = XpathUtils::getAttributeValues($xpathParams);

  // for each input to output node defined in fdi.xml
  // get weight, distance parameters and assign
  // these values to the paths array 
  for($j=0; $j < sizeof($outRes['id']); $j++) { 
    $output = $outRes['id'][$j];
    $xpathParams['xpath'] = "/fdi/input[@id='$input']/output[@id='$output']/weight";
    $wt = XpathUtils::getNodesValues($xpathParams);
    $wt = $wt['weight'];
    $path = $input . "->" . $output;
    $tmpPathArr['path'][$input][$output][0] = $path;
    $tmpPathArr['weight'][$input][$output][0] = $wt;
    $tmpPathArr['distance'][$input][$output][0] = 1;
    $nodesArray[$input][] = $output;
  }
}

$pathArr = $tmpPathArr; $prevPath = array();

// recursively iterate through the path array 
// to generate all possible paths and conversions
while ($pathArr != $prevPath) {
  $prevPath = $tmpPathArr;
  GenUtils::genPath($nodesArray, &$tmpPathArr, &$pathArr);
  $tmpPathArr = $pathArr;
}

// write the paths results to fdiPaths.txt and fdiPaths.xml files
$txtFile = "fdiPaths.txt";
$tf = fopen($txtFile, 'w');
if(!$tf) {
  echo "Unable to open text file for writing database paths\n";
  exit();
}
fwrite($tf, "Input\tOutput\tPath\tWeight\tDistance\n");

$xw = new XMLWriter;

$xw->openURI("fdiPaths.xml");
$xw->setIndent(true);

$xw->startDocument('1.0', 'ISO-8859-1');

$xw->startElement('fdiPaths');

foreach ($pathArr['path'] as $input=>$outputs) {
  $xw->startElement('input');
  $xw->writeAttribute('id', $input);

  foreach($outputs as $output=>$paths) {
    $xw->startElement('output');
    $xw->writeAttribute('id', $output);

    for($i=0; $i<count($paths); $i++) {
      $path = $paths[$i];
      $dist = $pathArr['distance'][$input][$output][$i];
      $wt = $pathArr['weight'][$input][$output][$i];
      
      $xw->startElement('pathInfo');
      $xw->writeElement('path', $path);
      $xw->writeElement('weight',$wt);
      $xw->writeElement('distance', $dist);
      $xw->endElement();
   
      fwrite($tf,"$input\t$output\t$path\t$dist\t$wt\n");
      $cnt++;
    }
    $xw->endElement();
  }
  $xw->endElement();
}
$xw->endElement();
$xw->endDocument();
fclose($tf);

//echo $xw->outputMemory();
?>
