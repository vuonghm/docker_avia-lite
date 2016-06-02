
<?php 
include("xml/header.php");
include("xml/navigation.php");
include("xml/side_nav.php");
echo "<div id=\"column-two\">";
$MAX_FILE_SIZE=15000000000; //This is determined by your server configuration and is usually 100kb.
//Process stuff first then write the page
$id="viz".uniqid();#'viz5278fb05dda66';#"viz".uniqid();
$date=date("YmdHis");
//validate and sanitze all input
//Put your stuff somewhere
$upload_dir="/code/src/data";
$dev='';
$user_ip=$_SERVER['REMOTE_ADDR'] ;
// $err_redirect_msg="html><meta http-equiv=\"refresh\" content=\"0; url=error_page.php\" /> </html>";
if (preg_match("/(analysis|ia_upload|setup|default)/i",$_SERVER["HTTP_REFERER"])){
	if (!isset($_POST["mydata"])){
	 	echo "$err_redirect_msg";
	 }
}else{
	echo $err_redirect_msg;
}
$file_info='';
//count the number of elements in array $_FILES
$filecount=0;
foreach ($_FILES as $key=>$value){
 	if ($_FILES[$key]["name"]){
 		$filecount=$filecount+1;
 	}
}
$files_found=array();//added this for tools.php
$foundafile=0;//This keeps track to make sure at least one of the many ways to enter data is used.
if ($filecount>0){
	$main_upload_file='';
	foreach ($_FILES as $param=>$myvalue){
		if ($_FILES[$param]["name"] !='' ){
		 	$pattern="/(txt|text|csv|tsv|gz|zip|bz|tar|vcf|cg|pileup|soap|gff|maq|bed)/i";$id=$id."ia";
		 	$application_pattern="/(text\/plain|application\/.*zip|application\/octet-stream|vcard|x-compressed|x-tar)/i";
			if ($_FILES[$param]["error"] > 0) {
				$err_msg=$err_redirect_msg;
			}elseif ($pattern=="" &&  $application_pattern==""){
				//do nothing
				$filename="";
			}elseif (  preg_match($application_pattern,$_FILES[$param]["type"])  &&   (preg_match($pattern,$_FILES[$param]["name"])) ){
			  	if ($_FILES[$param]["error"] > 0) {
			    	$err_msg=$err_redirect_msg;
				}elseif (($_FILES["user_file"]["size"]) > $MAX_FILE_SIZE ){//in 1GBs
					$filesizeuser=($_FILES["user_file"]["size"] / 10000000000)."GB";
					$err_msg=$err_redirect_msg;
				}else{
					 $filename=htmlentities($_FILES[$param]["name"]);     
					$filename=$id.".".$filename;
					echo "The new filename should be $filename...<br />";
				    if (preg_match("/userdefined_db(\d+)/",$param,$matches)){
				    	// echo "found USERDEFINED...$matches[1] from $param<br />";
				    	$filename=$id.".".htmlentities($_POST["report_userdefined_annotdb$matches[1]"]).".txt";
				    	$_POST["report_userdefined_annotdb$matches[1]"]=preg_replace("/\s/",'',$filename);
				    	// echo "USERDEFINED...$filename<br />";
				    }else{
				    	$main_upload_file=$filename;
				    }
				    if ($value ==''){
						echo "$param was empty<br />";
					}elseif (move_uploaded_file($_FILES[$param]["tmp_name"],"$upload_dir/upload/" . $filename)){
					   	echo "Stored in: " . "$upload_dir/upload/$filename";
						system ("chmod 777 $upload_dir/upload/$filename");  // octal; correct value of mode
						system ("dos2unix $upload_dir/upload/$filename\n");
						$param=preg_replace('/\_/','.', $param,1);
						if (preg_match('/user.file/',$param)){//Added next to deal with dual upload
							$foundafile++;
						}
						$file_info.=$param."=$upload_dir/upload/$filename\n";
						$files_found[$param]="$upload_dir/upload/$filename";
					}else{
						$msg="<font color='red'>Could not upload $param or $filename to our server</font> <br />";
						echo "$msg";
					}
			     //}
			  }
			}elseif (!preg_match($pattern,$_FILES[$param]["name"])){
				$msg="Your file type is invalid. ".  $_FILES[$param]["type"]." Please try .txt file";
				$err_msg=$err_redirect_msg;
			}elseif (!preg_match($application_pattern,$_FILES[$param]["type"])){
				$msg="ERR2-2: Invalid file type ($application_pattern) ".$_FILES[$param]["type"];
				$err_msg=$err_redirect_msg;
			}else{	
				$msg="Unknown!";
			  	$err_msg=$err_redirect_msg;
			}
		}else{
			//Globals::redirect($_SERVER["HTTP_REFERER"],"error","if statement gone wrong!");
		}
		$filename=$main_upload_file;
	}//end foreach loop?
}
if ($err_msg){
	echo $err_msg;exit;
}
$file="$upload_dir/$id.abcc";
$fh=fopen ($file,'w');
if (!$fh){
	$err_msg.="Could not open $file for writing!<br />";
}
if ($filename!=""){
	fwrite($fh,"$file_info");
	if (preg_match("/user\.file/",$file_info)){
		$foundafile++;
	}
}
if (!preg_match('/Enter your data here using comma/',$_POST['user_typed_input']) && $_POST['user_typed_input'] && !$foundafile){
	$input_fn=$id.".txt";$input_fh=fopen("$upload_dir/upload/$input_fn", 'w');
	fwrite($fh,"user.file=$upload_dir/upload/$input_fn\n");
	fwrite($fh,"user.wastyped=1\n");
	fwrite($input_fh,preg_replace('/,/',"\t",htmlentities($_POST['user_typed_input'])));
	fclose($input_fh);
	$foundafile++;
}elseif (!preg_match('/Enter your genelist/',$_POST['user_typed_input1']) && $_POST['user_typed_input1'] ){
	$input_fh=fopen("$upload_dir/upload/$id.txt", 'w');
	$input_fn=$id.".txt";
	fwrite($fh,"user.file=$upload_dir/upload/$id.txt\n");
	fwrite($fh,"user.wastyped=1\n");
	fwrite($input_fh,preg_replace('/,/',"\n",htmlentities($_POST['user_typed_input1'])));
	fclose($input_fh);
	$foundafile++;
}
if ($_POST["user_modulesid"]==5 & preg_match("/protein/",$_POST["user_inputformat"])){
	system ("mkdir $upload_dir/data/$id\n");
	if (!$input_fn && $filename){
		system ("mv $upload_dir/upload/$filename $upload_dir/data/$id/$id.annot.txt.prot\n");
	}else{
		system ("mv $upload_dir/upload/$input_fn $upload_dir/data/$id/$id.annot.txt.prot\n");
	}
	system ("chmod 777 $upload_dir/data/$id -R\n");
	system ("chmod 777 $upload_dir/data/viz5220c19a63ec8-dev -R\n");
	Globals::redirect("/apps/site/jmol/?id=$id","","");
}
if ($_POST['user_google_input'] != ''){
	$foundafile++;
}
if ($foundafile==0){
	echo "Could not find any file<br />";
}
foreach($_POST as $vblname => $value) {
	$new_vbl=preg_replace('/\_/','.', $vblname,1);
	//echo "working on $vblname and $value<br />";
	if (preg_match("/(compare_cgi_pop|abcc_genelists)/",$vblname)){
		$test=$_POST[$vblname];
		$info='';
		foreach ($test as $t) {
			if ($t){	$info = $info . "$t,";}
		}
		fwrite ($fh,$new_vbl. '=' .rtrim($info,",")."\n");
	}elseif (preg_match('/user.typed_input/',$vblname)){
		//do nothing as it was already handled above
	}elseif (!preg_match("/\_/",$vblname )){
		//do not write captcha or submit buttons or honeypot
	}elseif ( ($value=="" || preg_match("/Enter the full path/",$value)) ){
		//do not record
	}elseif ( preg_match("/(recaptcha)/",$vblname) ){
		//do not record
	}else{
		$carriage_returns=array ("\n","\r");
		$value=str_replace($carriage_returns,",",$value);
		$value=str_replace(",,",",",$value);
		fwrite ($fh, $new_vbl . '=' . $value . "\n");
	}
}
fwrite($fh, "HOST=" . $_SERVER['HTTP_HOST'] ."\n"); 
fwrite ($fh, "webuser=1\nuser.label=$id");
fclose($fh);
chmod ("$file", 0777);
$perl=`which perl`; $perl=rtrim($perl);
//This is where you would launch your back end scripts so that you can do some stuff
//Put a message to display on the webpage saying it was successfully launched or an error occurred
if ($perl && file_exists("/code/src/data/scripts.dir/processing_wrapper_fdi.pl")){
	system ("mv $file $upload_dir/completed/.\n");
	#if (file_exists("/code/src/data/completed/$id.abcc")){
	#	echo "/code/src/data/completed/$id.abcc exist<br />";
	#}elseif (file_exists("data/completed/$id.abcc")){
	#	echo "data/completed/$id.abcc exists<br />";  
	#}else{
	#		echo "None exists!<br />";
	#}
	#echo "About to run processing_wrapper_fdi.pl<br />";
	#echo ("$perl /code/src/data/scripts.dir/processing_wrapper_fdi.pl -f /code/src/data/completed/$id.abcc -w -o /code/src/data/completed/$id.config \n");
	system ("$perl /code/src/data/scripts.dir/processing_wrapper_fdi.pl -f /code/src/data/completed/$id.abcc -w -o /code/src/data/completed/$id.config \n");
//	system ("echo test >$upload_dir/tester.logger\n");
	$msg.="<br />Your unique identifier is <font color=\"red\">$id</font>.  You will receive an email when your process is complete. ";
}else{
	$msg.="scripts/processing_wrapper_fdi.pl does not exist on server or container";
}


echo "$msg<br />$err_msg</div>";
include("xml/footer.php");
?>
</div></body></html>
