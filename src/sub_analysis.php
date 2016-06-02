<?php  
// For all main content pages, enclose in div id="column-two".  This will ensure that your page is formatted correctly.
?>
<div id="column-two">
<?php
		
		$msg="<h5><font color=\"#666666\" face=\"Times\">";
		$sfx="If a gene list is specified in Section II, the highlight and filter options only apply to the Circos visualization.";
		$prioritizationheader="Section III";
		echo "$msg&nbsp;&nbsp;$sfx Please read our <a href=\"/apps/site/faq/\">FAQ</a> or <a href=\"/apps/site/tutorials\">Tutorials</a> for detailed information.&nbsp;&nbsp;If you do not have any data to start with, click on the button below labeled 'Sample BED data' for a self guided tutorial.</font></h5>";
?>


<h4> <font color="#404040">Section I.  Input Data (Required)  </font></h4>
 A field with an asterisk (*) before it is a required field. <br />
<form action="/upload_viz.php" method="post" name="myform" enctype="multipart/form-data" >
<table id="formatted" >
	<tr id="fileupload">
		<td width="30%" id="user_file_div">
			<label for="file">*Input Filename:</label>
		</td>
		<td colspan="2">
			<input type="file" name="user_file" id="file" size="33" onchange="sizeBind(this)" />
		&nbsp;&nbsp;(<a title="What are acceptable input types?" target="_blank" href="/apps/site/sub#ia_viz" >?</a>)
		</td>
	</tr>
<tr> <td colspan="2"> -- or --</td><td align="right">
</td></tr>
<tr>
	<td colspan="20">
	<input type="hidden" value="0" name="typed_ct" />
	<textarea rows="5" cols="70" id="typed" name="user_typed_input" placeholder="Enter your data here using comma or space separated list (one variant per line)">chr7 87160618 87160618 A C 
chr1 21580 21580 C T 
chr10 105164905 105164905 A - 
chr10 105821222 105821222 A -</textarea> (<a href="/" title="What are acceptable inputs?">?</a>)
	</td>
</tr>
<tr> <td colspan="2"> -- or --</td><td align="right">
</td></tr>
<tr><td>Google Storage Bucket</td><td colspan="2"><input type="text" id="user_google_bucket" name="user_google_input" size="60"></input><a href="/" title="Google Buckets must be made public to server">(!)</a></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td align="left"><span id="user_submission_type_row">Input format:</span></td>
	<td >
		<select name="user_inputformat" id="sampleformat">
<?php
		//	<option value="annot_vcf">Annotated VCF</option>
		//<option value="annot_anvr">Annotated ANNOVAR Output</option>
		$input_fmt_arr=array(""=>"&nbsp;",
			"vcf"=>"VCF4 formatted file",
			"bed"=>"ANNOVAR format input (BED)",
			"clcbio"=>"CLC Bio",
			"hgvs"=>"HGVS",
			"tvc"=>"Ion Torrent Variant Caller"
		);
		$idx='bed';
		if (array_key_exists("user_inputformat",$_POST)){
			$idx=$_POST['user_inputformat'];
		}
		foreach ($input_fmt_arr as $key=>$values){
			$addon='';
			if ($key == $idx){
				$addon="selected=\"true\"";
			}
			echo "<option value=\"$key\" $addon>$values</option>";
		}
?>		</select></td><td>
<tr><td align="left">*Organism and build</td>
	<td><select name="ref_ver" >
			<option selected="true" value="hg19">Human v37</option>
			<option value="hg18">Human v36</option>
			<option value="mm9"> Mouse v37</option>
<?php 
		echo "</select></td><td> ";
?>
</td></tr>
<tr><td><div id="email_div">*E-mail address:</div></td>
	<td><input type="text" name="user_email" maxlength="50" size="26" value=""  /></td>
	<td rowspan="2"><i> You will be notified by email when the process is complete</i></td></tr>
<tr><td><div id="email_val_div" onKeyPress="return disableEnterKey(this,event)" >*Confirm E-mail address:</div></td>
	<td><input type="text" name="confirmemail" maxlength="50" size="26"  value="vuonghm@mail.nih.gov" onchange="checkIfSample()"  onKeyPress="return disableEnterKey(this,event)"/></td>
	<td>&nbsp;</td></tr>
</table>
<h4><font color="#404040"> Section II. Annotation and Visualization Parameters </font>  </h4>
<span id="viz_opts" style="display:block;">
	<?php include_once("xml/annot_viz_dboptions.php");?>
</span>

<div id="mydiv"></div>
	<input type="hidden" name="user_group" value="public"  />
<div id="disclaimer_div" class="outline"> <input type="checkbox" name="disclaimer" id="disclaimer" checked /> By clicking this box, I am verifying that I have read the full <a href="/apps/site/disclaimer" target="_blank">disclaimer</a> and I fully understand that the information provided for me by AVIA is for research purposes only. The ABCC, FNLCR, and the NIH or any of the linked websites do not approve use of this information for diagnostic purposes. <br /></div> 
<input type="submit" name="submit" value="Submit" onclick="return validateInputs('sub_analysis');"/><input type="reset" value="Reset" />
                       
 </form>
</div>
