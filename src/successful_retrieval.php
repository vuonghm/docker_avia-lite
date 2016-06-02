<?php 
	if ($_GET["id"] != ""){
		$id=htmlentities($_GET["id"],ENT_QUOTES);
	}else{
		if ($_POST["user_requestor_id"] == ''){
			$msg.= "Please enter a identifier. <br />";
		}
		$id=htmlentities($_POST["user_requestor_id"],ENT_QUOTES);
	}
	if (!preg_match("/^\w+$/",$id)){
		$msg.="You have entered an invalid identifier\n";
	}
	$upload_dir=$_SERVER['DOCUMENT_ROOT']."/public";
	$myFile="$upload_dir/data/$id/$id.annot.txt.html";
	if (file_exists($myFile)){
		header("Location: http://fr-s-abcc-avia-l/results.php?id=$id");
		exit;
	}else{
		include_once("xml/header.php");
		include_once("xml/navigation.php");
		include_once("xml/side_nav.php");
		echo "<div id=\"column-two\">";
		if ($msg){
			echo "<h2>ERROR</h2>$msg";
		}else{
			echo "<h2>You have entered an invalid id or your submission has not yet completed.($myFile)</h2>";
		}
		echo "</div>";
		include_once("xml/footer.php");
	}
?>