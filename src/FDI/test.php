<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="/css/styles.css" />
<link rel="stylesheet" href="/css/jquery-ui.css" />
 <link rel="stylesheet" href="/css/results_styles.css" /> 
 <link rel="stylesheet" href="/css/jqueryslidemenu.css" /> 
<script src="/js/jquery-1.9.1.js"></script>
<script src="/js/jquery-ui.js"></script>
<script >
	$(document).ready( function () {
		var oTable = $('#example').dataTable({
          "bPaginate": true,
          "bFilter": true,
          "sScrollY": "600",
          "sScrollX": "100%",
		});
	} );
</script>
<script class="jsbin" src="http://datatables.net/download/build/jquery.dataTables.nightly.js"></script>
</head>
<body>
<?php
// set_time_limit(0);
//Server-specific variables
$server_variable_id_name='avialite_id'; //unique variable name for your server (the identifier for the user specific run)
$upload_dir="/code/src/data";//upload directory where user's files are read/written
//Application specific variables 
$MAX_LINES=5100; // This is the number of lines you choose to display on the webpages (upper limit of server capacity to hold array)
$MAX_ARG=`getconf ARG_MAX`;$MAX_ARG=rtrim( $MAX_ARG );
$fn_results_suffix=".annot.txt";
putenv("ORACLE_HOME=/opt/nasapps/production/oracle/product/11.2.0/client");
if (!array_key_exists('/DOCUMENT_ROOT/',$_SERVER)){
	$_SERVER['DOCUMENT_ROOT']='/src';
}
//Other vars
$id='';
$start_time=`date`;
$isdev=0; //for when you have a dev box
$noweb=0;
 // open the file and add to var idList
$mapCustomDb=array();
echo "<br />Here in some Random place for testing<br />";
//FDI/BioDBNet Variables
$allPaths = "yes";
$taxonId = "9606";//Human default
$idList='';
$fdi_file=$_SERVER['DOCUMENT_ROOT']."/FDI/fdi.xml";
$fdiPaths_file=$_SERVER['DOCUMENT_ROOT']."/FDI/fdiPaths.xml";


#trim the input values
#array_walk($inputValues, 'GenUtils::trimValue');
#remove empty values
#$inputValues = array_values(array_filter($inputValues));
if (isset($argv[0])){
	$id=$argv[1];
	$_SERVER['DOCUMENT_ROOT']="/mnt/webrepo/fr-s-abcc-avia-l/htdocs";
}else{
	$id=htmlentities($_GET["id"]);
}

$linecount=0;
if (file_exists ("$upload_dir/$id/$id.txt")){
	$read_lim = 10000; //size in bytes to load into memory
	$file = "$upload_dir/$id/$id.txt";
	$fh = fopen($file, "rb");
	
	$size = filesize($file); //total file size
	$lastinput='';
	while ($size > 0 ) {
	  $rlen = ($size > $read_lim) ? $read_lim : $size; //read length
	  $buffer = fread($fh, $rlen);
	  $arr=explode("\n","$lastinput$buffer");
	  foreach ($arr as $linenbr=>$line){
	  	if ($linenbr==count($arr)-1){
	  		$lastinput=$line;
	  	}else{
		  	$data=explode("\t",$line);
		  	if (preg_match('/^#/',$data[0])){ 
					// skip
			}else if (count($data)<5) {
				$idList.=preg_replace("/\s/",":",$data[0]).",";//in case users use spaces
			}else{
				// $idList.=join(":",$data[0],$data[1],$data[2],$data[3],$data[4]).",";
				$data=array_splice($data,0,5);
				$idList.=implode(":",$data).",";
			}
			$linecount++;
		}
	  }
	  $size -= $rlen;
	  #we do this at a greater line count because of the way the file is read; sometimes one line is broken into two
	  // if ($linecount>100100){$size=0;$noweb=1;}//REINSTATE 
	  
	}  
	if ($linecount>$MAX_LINES){$size=0;$noweb=1;system ("date >> $upload_dir/$id/noweb.txt\n");}#moved this out of loop
	if ($lastinput != ''){
		$data=explode("\t",$lastinput);
	  	if (count($data)<5) {
			$idList.=preg_replace("/\s/",":",$data[0]).",";//in case users use spaces
		}else{
			// $idList.=join(":",$data[0],$data[1],$data[2],$data[3],$data[4]).",";
			$data=array_splice($data,0,5);
			$idList.=implode(":",$data).",";
		}
	}
	$idList=preg_replace('/(chr)/','',$idList);
	$idList=preg_replace('/,{2,}/',',',$idList);//format input data for FDI
	$idList=rtrim($idList,",");
	$abcc_fn="$upload_dir/$id/$id.abcc";
	$dbArray=array("ANNOVAR annot","Annot Feat","Gene","ProtPos");
	if (($handle2 = fopen("$abcc_fn", "r")) !== FALSE) {
		while (($data = fgetcsv($handle2, 1000, "\t")) !== FALSE) {
			if (preg_match('/report.annotdb_(\S+)=on/i',$data[0],$matches)){
				if (!preg_match('/userdefined/',$matches[0])){
					if (!in_array("$matches[1]",$dbArray,1)){
						array_push($dbArray,$matches[1]);
					}
				}
			}elseif(preg_match('/(userdefined_annotdb(\d+)=)(\S+)/',$data[0],$matches)){
				// Array ( [0] => userdefined_annotdb1=20131113093926.userdb1.txt [1] => userdefined_annotdb1= [2] => 1 [3] => 20131113093926.userdb1.txt ) 
				if (file_exists("$upload_dir/../upload/$matches[3]")){
					echo "hooray $upload_dir/../upload/$matches[3] exists on server!\n";
					array_push($dbArray,"userdb$matches[2]");
					$mapCustomDb[$matches[2]]=preg_replace('/(^\d*\.|\.txt)/','',$matches[3]);
				}else{
					echo "Skipping $upload_dir/../upload/$matches[3] because it doesn't exist!\n";
				}

			}elseif (preg_match("/ref.ver=(.*)/",$data[0],$matches)){#Add on the fdi liftover converter???
				if(preg_match("/hg19/",$matches[1])){
					$convert='';
				}else{
					$convert="$matches[1]";
				}
			}
			if (preg_match("/Ensembl/i",$data[0])){
				$runEnsembl=1;
			}
			if (preg_match("/user.inputformat=(\w+)/",$data[0],$matches)){
				$type=$matches[1];
			}
		}
	}
}else{
	echo "<div>Your file does not exist! $upload_dir/$id/$id.txt<br /></div>	";
}
include_once("FdiUtils.php");
if (preg_match ("/^,*$/",$idList)){
}else{
	// echo "Your unique identifier is <em><font color=\"red\">$id</font></em>.  Please keep this for your records.  You will receve an email when your request is complete or you can visit our request <a href=\"/apps/site/retrieve_a_request\">retrieval</a> page using the identifier in red above. <br />";
	$inputValues = explode(",",$idList);
	echo "$idList<br />";
	$fdiParam['MAX_SIZE']=$MAX_ARG;
	$input = "Variant ID";
	$outputs = array();
	$fdiResults = array();
	if ($convert){
		echo "I am going to convert $convert<br />";
		$input="Variant_hg18";
		array_unshift($dbArray,"Variant ID");
	}
	array_push($dbArray,"comments");
	$fdiParams['input'] = $input;
	$fdiParams['inputValues'] = &$inputValues;
	$fdiParams['taxonId'] = $taxonId;
	$fdiParams['results'] = &$fdiResults;
	$fdiParams[$server_variable_id_name] = $id;
	$fdiParams["ensembl"]=$useensembl;
	$fdiParams['mapCustom']=$mapCustomDb;
	$fdiParams['noweb']=$noweb;
	$fdiParams['runEnsembl']=$runEnsembl;
	#Add any split columns here and then to the array $edgeP
	$edgeP=array($server_variable_id_name,"noweb","mapCustom",'runEnsembl');
	$fdiParams['addons']=$edgeP;##add this for future AVIA additions 
	// $fdiParams['debug']=1;//use this for printing debug messages

	if (file_exists ("$upload_dir/$id")){system ("chmod 777 $upload_dir/$id -R\n");}
	$fdiParams['toFile']="$upload_dir/$id/$id$fn_results_suffix";
	$dir=dirname($fdiParams['toFile']);rtrim( $dir );
	$fdiParams['allPaths'] = $allPaths;
	$fdiParams['outputs'] = $dbArray;
	$fdiParams['outputdir']=$dir;
	echo "about to FDIUtils...<br />";
	FdiUtils::executeDb2DbAll($fdiParams);
	print "done with FDI...let's print!\n";
	if ($fdiParams['noweb']!=1){
		$results2=GenUtils::printResInCols($fdiParams);
		echo "Done printResInCols...<br />";
		print file_get_contents($fdiParams['toFile'].".html");
	}
}
if (!file_exists ("$upload_dir/$id/viz") && file_exists($fdiParams['toFile'].".html") && $fdiParams['noweb']!=1){
	#this will launch the post processing scripts that will generate viz, plots, etc.  It will also send the final completion email to user
	system ("qsub -u www-avial -j eo -o $upload_dir/$id/stderr $upload_dir/$id/runAVIA_post.bat >/dev/null\n");
}
system ("chmod 777 $upload_dir/$id/* -R 2>/dev/null\n");
?>
</body>
</html>
