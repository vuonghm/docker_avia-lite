
<?php include_once("xml/header.php");?>
<link rel="stylesheet" href="css/styles.css" />
<link rel="stylesheet" href="css/jquery-ui.css" />
 <link rel="stylesheet" href="css/results_styles.css" /> 
 <link rel="stylesheet" href="css/jqueryslidemenu.css" /> 
<script src="js/jquery-1.9.1.js"></script>
<script src="js/jquery-ui.js"></script>
<style>
td{
	vertical-align: center;
}
</style>


<script type="text/javascript">
	$(document).ready(function() {
	    $('table.display').dataTable();
	    $('.display tbody td').each(function(index){
		    $this = $(this);
		    var titleVal = $this.text();
		    // if (titleVal != ''  ) {
		    if (titleVal.length>30){
		      $this.attr('title', titleVal);
		    }
		 });
	} );

</script>
<script class="jsbin" src="http://datatables.net/download/build/jquery.dataTables.nightly.js"></script>

<?php 
	
	include_once("xml/navigation.php");
	echo "<br /><div style=\"clear:left;\">";
	$id=htmlentities($_GET["id"],ENT_QUOTES);
	$bin=$_SERVER['DOCUMENT_ROOT']."/scripts/";
	if (!isset($id)){
		$id="sampledata";//"intviz501fbb09e2c6aia";
	}elseif (!preg_match("/\.\./",$id)){
	}elseif (!preg_match("/^[\w\-]*$/",$id)){
		Globals::redirect("/apps/site/retrieve_a_request","error","Invalid id.");
	}else{
		Globals::redirect("/apps/site/retrieve_a_request","error","You have entered an invalid id.");
	}
	
	$upload_dir="data";
	$input_file="$upload_dir/$id/$id.annot.txt.html";
	if (!file_exists ($input_file)){
		$err_msg="Your results file does not exist on server($input_file)";
		
	}
	$largedata=0;
?>

<?php 
	if (preg_match("/sampledata/",$id)){
		echo "<h2>You are viewing the sample results page from AVIA.</h2><div id=\"note\" ><em>Note: </em>The data run through the pipeline was a subset from the RIKEN Liver Cancer data set (from http://www.icgc.org) and with  annotations from various coding and non-coding databases. Below, you can click on the different tabs to view subsets of your variant data.  You can download all of the results using the \"Download All Data\" button below.  You may also download individual files located near the files displayed.  <br />If you have any questions about the outputs, please click <a href=\"/apps/site/tutorials\">here</a> to view the tutorial.</div>";
	}else{
		echo "<h2>Your results for $id</h2>";
	}
?>

<table width="100%"><tr>
<td align="left" width="10px">
<?php 


$display_type='ALL';//valid ones= ALL GENE LD
if (file_exists ("$upload_dir/$id/$id.annot.txt.zip")){ 
	echo "<a href=\"download.php?file=$id/$id.annot.txt.zip\"><button type=\"button\">Download All Annotations</button></a></td>";
}else if (file_exists("$upload_dir/$id/$id.annot.txt")){
	echo "<a href=\"download.php?file=$id/$id.annot.txt\"><button type=\"button\">Download All Annotations $id</button></a></td>";
}else{
	echo "</td>";
}
?>

<td align="left" width="10px">
	<a href="download.php?file=<?php echo "$id/$id.zip"?>"><button type="button">Download All Data</button></a>
	</td>
<?php
if (file_exists("$upload_dir/$id/$id.vcf")){
	if (file_exists("$upload_dir/$id/$id.vcf.tgz")){
		$dl_fn="$id/$id.vcf.tgz";
	}elseif (file_exists("$upload_dir/$id/$id.vcf.zip")) {
		$dl_fn="$id/$id.vcf.zip";
	}else{
		$dl_fn="$id/$id.vcf";
	}
?>
<?php  } // do not delete if /else vcf file exists loop 
?>


</tr>
</table>
<form name="myform"><input type="hidden" name="avia_id" value="<?php echo $id;?>" />
<?php

	echo "<div id=\"tabs\">";

	echo "\n<div id=\"example_dt\" >";
	 // $input_file="$upload_dir/$id/$id.annot.txt.html";
	 if (file_exists ($input_file) && $largedata==0){
	 	echo "<br /> <b>Please click <a href=\"/apps/site/faq#40-29\" class=\"resultslink\" target=\"_blank\">here</a> to read how the 'Summary' column was generated.  In the table below, if you hover over a header, it should show you a description of the database annotation.  For cells in tables with many characters, elipsis should appear, hover over cell to view the entire annotation.  Downloads should have complete annotation.</b>\n";
	 	echo file_get_contents($input_file);
	 }else{
	 	echo "Your file ($input_file) does not exist on server.<br />";
	 }
	echo "</div>";
?></div>


</form>

<!-- Do not alter -->
</div>
<?php include("xml/footer.php");?>
</body></html>
