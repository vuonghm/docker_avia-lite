<?php

/**
Author: Uma Mudunuri
Date: 2008
Description: PHP class with general utility functions
*/

class GenUtils{

  /**
  function to print the column headers of the results
  either in the html table or tab delimited text format
  input: handle for the fdiParams array containing FDI results
  */
  function printResColHeaders(&$fdiParams) {
    $outputs = $fdiParams['outputs'];
    $input = $fdiParams['input'];
    $toFile =  $fdiParams['toFile'];
    $paths = array_key_exists("paths", $fdiParams['results']);
      $writer= "<table id='example' border='1' class=\"display\"><thead>";
      // class='tablesorter' 
      $st = "<tr>"; $end = "</tr>\n";
      $sst = "<th >"; $send = "</th>";
    // }
    // else {
    //   $st = ""; $end = "\n";
    //   $sst = ""; $send = "\t";
    // }
    //print column headers
    $writer.= $st;
    $writer.= $sst . $input . $send;
    

    $mapper_file=$_SERVER[DOCUMENT_ROOT]. "/src/avia.database.info.txt";
    if (!file_exists ($mapper_file)){
      $mapper_file="$_SERVER[DOCUMENT_ROOT]/src/avia.database.info.txt";
    }
    echo "<br /> Using  a mapper file~~~  $mapper_file!!!!<br />";
    $description_line=$st;
    // $head_convert=array();
    // if (file_exists ($mapper_file)){
    //   $lines = file($mapper_file);
    //   // print_r($lines);
    //   // Loop through our array, show HTML source as HTML source; and line numbers too.
    //   foreach ($lines as $line_num => $line) {
    //     $headers=explode("\t",$line);
    //     $head_convert[$headers[0]]=$headers[1];
    //   }
    // }
    // print_r($head_convert);
     // echo "OUTPUT:::::&gt;";print_r($outputs);
     //      $output2 =  array_splice ( $outputs , 0, -1,array("foo"));
     //      echo "<br />OUTPUT2:::::&gt;";print_r($output2);echo "<br />";
    if (preg_match("/Variant ID/",$outputs[0])){
      $header_content="#hg18_Variant\t";
      $description_line.=$sst." ".$send;
    }else{
      $header_content="#Variant ID\t";
      $description_line.=$sst."User's Variants".$send;

    }
    // print_r($outputs);//starts with ANNOVAR annot (no summary or hg19 or variant ID)
    $avia_db_info=$fdiParams['avia_db_info'];
    // return;
    foreach($outputs as $key => $output) {
      if (preg_match("/userdb(\d+)/",$output,$matches)){
        $map_arr=$fdiParams['mapCustom'];
        $output = preg_replace("/userdb$matches[1]/",$map_arr[$matches[1]],$output);
      }
      if (preg_match("/(FunSeq)/i",$output,$matches)){//split the column into multiple 
       $fs_mapping_arr=$fdiParams[$matches[0]];

        foreach ($fs_mapping_arr as $key =>$value){
          $writer.= $sst . GenUtils::addClass($value,$avia_db_info['FunSeq*']['description']) . $send ;
          $description_line.=$sst.$avia_db_info['FunSeq*']['description'].$send;
        }
      }elseif (array_key_exists($output,$avia_db_info) ){
        $writer.=$sst . GenUtils::addClass($avia_db_info[$output]['shown_name'],$avia_db_info[$output]['description']) . $send ;
        $description_line.=$sst.$avia_db_info[$output]['description'].$send;
      }else{
       $writer.= $sst . GenUtils::addClass($output,$avia_db_info[$output]['description']) . $send ;
       $description_line.=$sst.$avia_db_info[$output]['description'].$send;
      }
      $header_content.="$output\t";
      // echo "writing:$header_content<br />";
    }
    $writer.= $end."</thead><tbody>";
    $description_line.=$end;
    $htmlhandle=fopen("$_SERVER[DOCUMENT_ROOT]/public/data/". $fdiParams['avialite_id']."/subtractive_headers.out",'wr');
    fwrite($htmlhandle,"#Summary\t$header_content");
    fclose($htmlhandle);
    $desc_handle=fopen("$_SERVER[DOCUMENT_ROOT]/public/data/". $fdiParams['avialite_id']."/header_descriptions.out",'wr');
    fwrite($desc_handle,$description_line);
    fclose($desc_handle);
    return (array($writer,$description_line));
  }
  function addClass($text,$popup){
    if (preg_match("/FOO/",$text)){
      return preg_replace("/FOO/","title=\"$popup\"",$text);
    }else{
      return ("<a title=\"$popup\">$text</a>");
    }
  }
    /**
  function to print the information
  either in the html table or tab delimited text format
  input: handle for the edgeParams before results for database have been run
  */
  function printVarsInCols(&$fdiParams){//specific for AVIA
    $inputValues = $fdiParams['inputValues'];
    $dir='/bioinfoC/AVA/FDI/';
    $toFile=$dir.$fdiParams['avialite_id']."/". $fdiParams['avialite_id'];
    if (!$handle = fopen($toFile, 'wr')) {
         echo "Cannot open file ($toFile)";
         exit;
    }
     foreach($inputValues as $key => $inVal) {
        $writer= implode ("\t",(explode(':',$inVal))) ."\n";
        fwrite($handle,$writer);
     }
     fclose($handle);
     return;
  }
 /**
  function to get AVIA short descriptions from the database
  */
  function getAVIA ($indexAsKey){
     $db='avia_abcc_dbdev';
     $user="avia_db_admin";
     $pwd='EA8J9br4RGKXm';
     $host = 'sqldb1.abcc.ncifcrf.gov';
     $sqlfile='/bioinfoC/AVA/FDI/sql';
    $arr=explode("\n",`mysql -h $host -u $user -p$pwd <$sqlfile`);
    foreach ($arr as $key =>$val){
      $val=rtrim($val);
      $lineArr=explode("\t",$val);
      $db_info[$lineArr[$indexAsKey]]['description']=$lineArr[1];
      $db_info[$lineArr[$indexAsKey]]['shown_name']=$lineArr[2];
      $db_info[$lineArr[$indexAsKey]]['avia_name']=$lineArr[0];
    }
    return ($db_info);
  }
  /**
  function to print the results in columns (for db2db and dbWalk)
  both in the html table or tab delimited text format
  input: handle for the fdiParams array containing FDI results
  */
  function printResInCols(&$fdiParams) {
    // echo "in printResInCols<br />";
    // echo "oh hiiiii\n";
    $fdiParams['avia_db_info']=GenUtils::getAVIA(0);
    // print_r($fdiParams);
    if ($fdiParams['noweb']==1){
      return;
    }
    $header_arr=GenUtils::printResColHeaders($fdiParams);
    $writer=$header_arr[0];
    $desc_line=$header_arr[1];
    // echo "This should be the descriptors...$desc_line<hr />";
    $inputValues = $fdiParams['inputValues'];
    $results = $fdiParams['results'];
    $outputs = $fdiParams['outputs'];
    // echo "<br /><br />";
     // print_r($outputs);
    $input = $fdiParams['input'];
    $toFile =  $fdiParams['toFile'];
    $paths = array_key_exists("paths", $results);
    // if(!$toFile) {
      $st = "<tr valign='top' CLASS>"; $end = "</tr>\n";
      $sst = "<td>"; $send = "</td>";
      $brk = ", ";
    // }
    // else {
    //   $st = ""; $end = "\n";
    //   $sst = ""; $send = "\t";
    //   $brk = ", ";
    // }
    //print values
      $count=0;
    
    // print_r($results);
     if($toFile && array_key_exists('avialite_id',$fdiParams)) {
        if (!is_writable($toFile)) {
          system ("rm $toFile -f\n");
        }
        // if ($fdiParams['noweb']==0){}
        echo "writing to hg19_summarize_auto\n<Br />";
          $summary_fn=$fdiParams['outputdir']."/".$fdiParams['avialite_id'].".hg19_summarize_auto.txt";#for testing
          $summary_desc="Quick description of variant where C=mutation is in the COSMIC database, D=multiple damaging calls by protein scoring variants,F=has a variant with a FunSeq score greater than 2,O=in a gene identified in MIM, P=in a Post translational modification site, V=identified as clinically significant in ClinVar";
          $writer=preg_replace("/<tr><th >/","<tr><th><a title=\"$summary_desc\">Summary</a></th><th>",$writer);
          // if (!file_exists($summary_fn)){#run everytime for now, but reinstate file_exists in prod
            $summary_handle=fopen($summary_fn,'wr');
            echo "writing to $summary_fn\n";
            fwrite($htmlhandle,$writer."#Summary");
            // $desc_line="##$summary_desc\t".$desc_line;
            fclose($htmlhandle);
          // }
          foreach($inputValues as $key => $inVal) {
            if ($count%2==1){
              $class="class='altclr'";
            }else{
              $class='';
            }
            $count++;
            $curr_line= preg_replace('/CLASS\>/',"$class>",$st);
            $pos_arr=explode(":",$inVal);//7:87160618:87160618:A:C
            if (preg_match("/Variant ID/",$outputs[0])){
              $org="hg18";
            }else{
              $org="hg19";
            } 
            $summarize='';
            //FOOBAR is a placeholder for the summary of consequence
            // echo "working on $inVal<br />";
            $curr_line.='FOOBAR'. $sst . "<a href=\"http://genome.ucsc.edu/cgi-bin/hgTracks?db=$org&amp;position=chr$pos_arr[0]:". ($pos_arr[1]-2000) ."-".($pos_arr[2]+2000). "\" target=\"_blank\" class=\"resultslink\">".  htmlentities($inVal) ."</a>$send"."";
            // $writer.= $sst . htmlentities($inVal) ." ||". ($pos_arr[0]-1)."||$send";

           
            foreach($outputs as $num => $output) {
              $tmpArr=array();
              if(preg_match("/(FunSeq)/i",$output,$matches)){
                $tmpArr=$fdiParams[$matches[0]];
                // print_r($results[$inVal]);echo "<br />";
                foreach ($tmpArr as $fskey=>$fsVal){
                  if (!isset($results[$inVal][$fsVal][0])){
                    $results[$inVal][$fsVal][0]='-';
                  }elseif ($results[$inVal][$fsVal][0]=='.'){
                    $results[$inVal][$fsVal][0]='-';
                  }
                  $curr_line.=$sst . GenUtils::formatCell($results[$inVal][$fsVal][0],1). $send;
                  if (preg_match('/score/',$fsVal) ){
                    if ($results[$inVal][$fsVal][0]>=2){
                     $summarize.="F";
                    }
                  }
                  // echo "FOO:writing ($fsVal)".$sst . $results[$inVal][$fsVal][0]. $send. "(summarize??$summarize)<br />";
                }
              }else{
                if(is_array($results[$inVal][$output])) {
                  $outVals = array_unique($results[$inVal][$output]);
                  $outVal = implode($brk, $outVals);
                  if (preg_match("/(rs(\d+))([:,])(.*)/",$outVal,$rsids)){
                    $curr_line.= $sst . GenUtils::addClass("<a href=\"http://www.ncbi.nlm.nih.gov/projects/SNP/snp_ref.cgi?rs=$rsids[2]\" target=\"_blank\" class=\"resultslink\" FOO>$rsids[1]</a>$rsids[3]$rsids[4]", $fdiParams['avia_db_info'][$output]) . $send;
                  }elseif(preg_match("/^COS\d+/",$outVal,$cosmicids)){
                    $cos_id=preg_replace("/COS/","",$cosmicids[0]);
                    $outVal2=preg_replace("/COS\d+/","<a href=\"http://cancer.sanger.ac.uk/cosmic/mutation/overview?id=$cos_id\" target=\"_blank\" class=\"resultslink\" FOO>COSMIC ID:$cos_id</a> ",$outVal);
                    if (preg_match("/PMID\-\d+/",$outVal2,$pmids)){
                      $pmid=preg_replace("/PMID\-/",'',$pmids[0]);
                      $outVal2=preg_replace("/PMID-\d+/","PubMed:<a href=\"http://www.ncbi.nlm.nih.gov/pubmed/?term=$pmid\" target=\"_blank\" class=\"resultslink\" >$pmid</a> ",$outVal2);
                    }
                    $curr_line.= $sst .  GenUtils::addClass($outVal2,$fdiParams['avia_db_info'][$output]) . $send;
                    $summarize.="C";
                  }else{
                    $link='';
                    if (preg_match("/(sift|pp2|provean|ma$|mt$)/i",$output) && (preg_match("/(damaging|med|high|disease causing|deleterious)/i",$outVal))){
                      $summarize.="X";
                      $curr_line.= $sst . GenUtils::formatCell($outVal,1) . $send;
                    }elseif (preg_match("/(p)tm$/i",$output,$mymatches) && $outVal!='-' && $outVal!='') {
                      $summarize.=strtoupper($mymatches[1]).strtoupper($mymatches[2]);
                      $curr_line.= $sst . htmlentities($outVal) . $send;
                    }elseif (preg_match("/ClinVar/i",$output) && preg_match('/SIG=(probable-){0,1}pathogenic/',$outVal)) {
                      $summarize.="V";
                      $curr_line.= $sst . htmlentities($outVal) . $send;
                    }elseif(preg_match("/(OMIM|Mendelian)/i",$output) && preg_match("/\d+/",$outVal)){
                      $summarize.="O";
                      $eachOMIM=explode(";",$outVal);
                      $newVal='';
                      foreach ($eachOMIM as $key=>$omim){
                        $newVal.="<a href=\"http://omim.org/entry/$omim\" target=\"_blank\" class=\"resultslink\">$omim</a>;";
                      }
                      $newVal=preg_replace("/;\s$/",'',$newVal);
                      $curr_line.= $sst . GenUtils::formatCell($newVal,0). $send;
                    }elseif (preg_match("/(DGV|Database of Genomic Variants)/i",$output) && preg_match("/\d+/",$outVal)){
                      $link1="http://dgv.tcag.ca/dgv/app/variant?ref=NCBI37/hg19&amp;id=";
                      $link2="http://www.ncbi.nlm.nih.gov/pubmed/?term=";
                      $eachDGV=explode(";",$outVal);
                      $newVal='';
                      foreach ($eachDGV as $key=>$dgv){
                          $info=explode(":",$dgv);
                         $newVal.="<a href=\"$link1$info[0]\" target=\"_blank\" class=\"resultslink\">$info[0]</a>:$info[1]:<a href=\"$link2$info[2]\" target=\"_blank\" class=\"resultslink\">$info[2]</a>;";
                      }
                      $newVal=preg_replace("/;$/",'',$newVal);
                      $curr_line.= $sst . GenUtils::formatCell($newVal,0) . $send;
                    }else{
                       // $curr_line.= $sst . htmlentities(preg_replace("/,/",", ",$outVal)) . $send;
                      $curr_line.= $sst . GenUtils::formatCell($outVal,1) . $send;
                    }
                  }
                }else {
                  $curr_line.= $sst . "-" . $send;
                }     
              }
            }
            $curr_line.= $end;
            // $summarize=preg_replace("/^D{1}[^D]/","MOO",$summarize);#requires 2 or more to be called damaging
            $summarize=preg_replace("/X{2,}/","D",$summarize);#gets rid of extra "D"s
            $summarize=preg_replace("/X/","",$summarize);#requires 2 or more to be called damaging
            $curr_line=preg_replace("/FOOBAR/","<td>$summarize</td>",$curr_line);
            $results[$inVal]['Summary']=$summarize;
            if ($fdiParams['noweb']==1 && $summarize==''){//for large datasets only write those with consequence to the webpage
              $other_writer.=$curr_line;
            }else{
              $writer.=$curr_line;//write to the webpage
            }

            fwrite($summary_handle,"$summarize\n");
          }
          $htmlhandle=fopen($toFile.".html",'wr');
          // if ($fdiParams['noweb']==1){
          //   fwrite($htmlhandle,"Your data set is too large to view on the website.  These are your variants with negative consequence as determined by AVIA.  You can download your full annotations in the \"Download Full Annotations\" button.<br />");
          // }
          fwrite($htmlhandle,$writer."</tbody></table>");
          fclose($htmlhandle);
          if (!$handle = fopen($toFile, 'wr')) {
            echo "Cannot open file ($toFile)";
            exit;
          }
          if ($summary_handle){fclose ($summary_handle) ;} 
          $writer.="$other_writer";
           $writer=preg_replace('/(\<table.*\<thead>?|\<\/thead><tbody>)/','',$writer);
          // $writer=preg_replace('/(\<table.*\<thead>?)/','',$writer);
          // $writer=preg_replace('/(\<\/thead><tbody>)/',$desc_line,$writer);
          $writer=preg_replace("/(\<tr\svalign=\'top\'\sclass='altclr'>?|\<tr\svalign=\'top\'\s>?)/","",$writer);//echo "replacing ". htmlentities($st). "<br/>";
          $writer=preg_replace('/\<\/tr\>/',"",$writer);//echo "replacing ". htmlentities($end). " with \\n<br/>";
          $writer=preg_replace('/\<td\>/',"",$writer);//echo "replacing ". htmlentities($sst). "<br/>";
          $writer=preg_replace('/\<\/td\>/',"\t",$writer);//echo "replacing ". htmlentities($send). "<br/>";
          $writer=preg_replace('/\<tr\>/',"",$writer);//echo "replacing ". htmlentities($send). "<br/>";
          $writer=preg_replace('/\<th\s{0,}\>/',"",$writer);//echo "replacing ". htmlentities($send). "<br/>";
          $writer=preg_replace('/\<\/th\s{0,}\>/',"\t",$writer);//echo "replacing ". htmlentities($send). "<br/>";
          $writer=preg_replace("/\<a href.*?\>/","",$writer);//echo "replacing rsid with MOO<br/>";
          $writer=preg_replace("/\<a title.*?\>/","",$writer);//echo "replacing rsid with MOO<br/>";
          $writer=preg_replace("/\<\/a\>/","",$writer);
          $writer=html_entity_decode($writer);
          fwrite($handle,$writer);
          fclose($handle);
        // }else{
        //   $htmlhandle=fopen($toFile.".html",'wr');
        //   fwrite($htmlhandle,"Your data set is too large to view on the website.  You can still download your data by using the 'Download All' button and viewing on your computer.<br />");
        //   fclose($htmlhandle);
        // }
          return $results;
    }else{
      echo "No request to write<br />";
    }
  }

  function formatCell($str,$htmlSmart){
    if ($htmlSmart==1){
      return htmlentities(preg_replace("/,/",", ",$str));
    }else{
      return preg_replace("/,/",", ",$str);
    }
  }

  /**
  function to print the results in rows (for dbReport)
  either in the html table or tab delimited text format
  input: handle for the fdiParams array containing FDI results
  */    
  function printResInRows(&$fdiParams) {
    $inputValues = $fdiParams['inputValues'];
    $results = $fdiParams['results'];
    $outputs = $fdiParams['outputs'];
    $input = $fdiParams['input'];
    $toFile =  $fdiParams['toFile'];

    if(!$toFile) {
      echo "<table>";
      $st = "<tr>"; $end = "</tr>\n";
      $sst = "<th align='left' nowrap>"; $send = "</th>";
      $tst1 = "<td align='left' valign='top' nowrap>";
      $tst = "<td>"; $tend = "</td>";
      $brk = ", ";
    }
    else {
      $st = ""; $end = "\n";
      $sst = ""; $send = "\t";
      $tst = ""; $tend = "\t";
      $brk = ", ";
    }

    foreach($inputValues as $key => $inVal) {
     
      echo $st . $sst . $send . $sst . $send . $end;
      echo $st . $sst . $input . $send . $sst . " " . $inVal . $send . $end;

      foreach($outputs as $key2 => $output) {
	$path = $results[$inVal][$output]['path'];
	$tmpArr['path'] = $path;
	
	if(is_array($results[$inVal][$output])) {
	  $outVals = array_diff(array_unique($results[$inVal][$output]), $tmpArr);
	  $outVal = implode($brk, $outVals);
	  if($outVal) {
	    echo $st . $tst1 . $output . $tend . $tst . $outVal . $tend . $end;
	  }
	}
      }
    }
    if(!$toFile) {
      echo "</table>\n";
    }    
  }


  /**
  function to create html select boxes
  input: select options and parameters used by html select  
  */
  function createSelectOptions($selectOptions, $params) {

    $selected = $params['selected'];

    // size of array 
    foreach ($selectOptions as $key => $value) {

      $optionDisplayName = $value;

      if(is_array($params['values'])) {
	$optionValue = $params['values'][$optionDisplayName];
      }
      else { 
	$optionValue =  $optionDisplayName; 
      } 

      If ($optionDisplayName == $selected) {
	$select = "selected";
      }
      else { $select = ""; }
      
      if(GenUtils::strExists($optionDisplayName)) {
	echo "<option " . $select . " value='$optionValue'>" . 
	  $optionDisplayName . "</option>\n";
      }
    }
  }


  /**
  function to trim a string
  input: string to be trimmed
  output: input string after trim
  */
  function trimValue (&$value) {
    $value = trim($value);
  }


  /**
  function to check if a given string has value
  input: string variable to be checked
  output: true or false 
  */
  function strExists($strVar) {
    if (isset($strVar) and strlen($strVar) > 0) {
      return 1;
    }
    else {
      return 0;
    } 
  }

  /** endsWith and startsWith function code by SteveRusin 
http://us3.php.net/strings */
  function strStartsWith($str,$start) {
    return ( substr( $str, 0, strlen( $start ) ) === $start );
  }

  function strEndsWith($str, $end) {
    return ( substr( $str, strlen( $str ) - strlen( $end ) ) === $end );
  }


  /**
  function to generate paths, used by genPaths.php
  input: nodesArray containing input and output node information,
         inArray containing path information obtained so far
         outArray containing path information after the current iteration
  */
  function genPath($nodesArray, $inArray, $outArray) {

    foreach($inArray['path'] as $input=>$outputs) {
      foreach($outputs as $output=>$paths) {

	if(is_array($nodesArray[$output])) {
	  foreach($nodesArray[$output] as $key=>$value) {
	
	    if ($value != $input) {
	      for($i=0; $i< count($paths); $i++) {
		if((!strpos($paths[$i], "->" . $value . "->")) and
		   (!GenUtils::strStartsWith($paths[$i], $value . "->")) and
		   (!GenUtils::strEndsWith($paths[$i], "->" . $value))) {
		  $path = $paths[$i] . "->" . $value;
		  $dist = $inArray['distance'][$input][$output][$i] + 1;
		  $wt = $inArray['weight'][$input][$output][$i] * 
		    $inArray['weight'][$output][$value][0];
		
		  if(!is_array($outArray['path'][$input][$value])) {
		    $outArray['path'][$input][$value] = array();
		    $outArray['weight'][$input][$value] = array();
		    $outArray['distance'][$input][$value] = array();
		  }
		  if(!in_array($path, $outArray['path'][$input][$value])) {
		    $outArray['path'][$input][$value][] = $path;
		    $outArray['weight'][$input][$value][] = $wt;
		    $outArray['distance'][$input][$value][] = $dist;
		  }
		}
	      }
	    }
	  }
	}
      }
    }
  }



}

?>

