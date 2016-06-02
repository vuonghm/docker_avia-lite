function playVideo(myDiv){
	var e=document.getElementById('tutorial_name');
	var video=e.options[e.selectedIndex].value;
	if (e.selectedIndex==0){
		alert("Please select a tutorial!");return;
	}
	//here we determine what browser is used
	// if (/Firefox/.test(navigator.userAgent)){ //test for Firefox x.x;\
		WriteContentIntoID(myDiv,'<object width="1024" height="840" data="/modules/site/library/images/videos/'+ video + '.mp4" ><embed src="/modules/site/library/images/videos/'+ video + '.mp4 type="audio/midi" autostart="true" hidden="true"></embed> </object> ');
	// }else{
	// 	WriteContentIntoID(myDiv,'Unfortunately, windows media player cannot be played on your current web browser.  Please download and install Firefox by clicking <a href="http://www.mozilla.org/en-US/firefox/new/" target="_blank">here</a>.');
	// }
}
function xload(type,myDiv){
	// alert(type);
	myid=document.myform.avia_id.value;
	loadXMLDoc("/apps/site/mid/?id="+myid+'&db='+type,myDiv);
}
function writeGBK(buttonpressed,table){
	WriteContentIntoID(table+'_results','<img src="/modules/site/library/images/spinningred.gif" alt="spinning gif" /><em>If this takes more than a few minutes, the server is not responding or there is an issue with javascript.</em>');
	var myvalue=buttonpressed.value;
	var arr=myvalue.split("|"); 
	if (/(GKB)/.test(arr[0]) ){
	// alert("/apps/site/drugs/?id="+document.myform.avia_id.value + '&gkbid=' + arr[1]+"&gene="+arr[0]);
		loadXMLDoc("/apps/site/drugs/?id="+document.myform.avia_id.value + '&gkbid=' + arr[1]+"&gene="+arr[0],"pharmgkb_results");
	}else{
		// alert("/apps/site/avia_ws/?ids="+ arr[1] + "&table="+table);
		loadXMLDoc("/apps/site/avia_ws/?ids="+ arr[1] + "&table="+table,table+"_results");
	}

}
function checkInput2(){
	if (/annotateGeneList/.test(document.myform.whattodo.value)){
		if (document.myform.user_email.value != document.myform.confirmemail.value){
			alert ("Your email addresses do not match!");return false;
		}
		if (!(/\S+\@\w+\.\w+/.test(document.myform.user_email.value))){
			alert ("You did not enter a correct email address\n");
			return false;
		}
		// if (document.myform.user_typed_input1.value == ''){
		// 	alert ("You must enter text in the input box!\n");
		// 	return false;
		// }
	}
	return true;
}
function disableEnterKey(obj,e)
{
     var key;
     if(window.event){
          key = window.event.keyCode;     //IE
     }else{
          key = e.which;     //firefox
     }
     if(key == 13){
         var ele = document.forms[0].elements;
                for(var i=0;i<ele.length;i++){
                        var q=(i==ele.length-1)?0:i+1;// if last element : if any other
                        if(obj==ele[i]){ele[q].focus();break}
                }
                return false;
     }else{
          return true;

     }
}
function loadXMLDoc(myPage,myDiv)	{
	var xmlhttp;
	//alert("looking for " + myPage);
	//console.log ("looking for " + myDiv);
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  console.log("getting " + xmlhttp);
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	xmlhttp.onreadystatechange=function(){
	  if (xmlhttp.readyState==4 && xmlhttp.status==200){
	    var info=xmlhttp.responseText;
	    document.getElementById(myDiv).innerHTML=info;
	  }
	}
	xmlhttp.open("GET",myPage,true);
	xmlhttp.send();
	
}
function loadXMLFrame(myPage,myDiv,gene)	{
	document.getElementById(myDiv).innerHTML="<h2><a href=\"http://biogps.org\"><b>BioGPS</b></a> Visualization for GNF Expression Data for " + gene+"</h2><hr /><iframe width=\"100%\" height=\"100%\" src=\"" + myPage + "\"></iframe>"
}

function sizeBind(filenamer){
	var foo = filenamer.files[0].name;
	var bar = parseInt(filenamer.files[0].size);
	// alert(filenamer.name);
	var seenThese =document.getElementById('ckuploadedfile'); 
	if (bar>1500000000){//check that the filesize is <100MB as suggested on the page
		var inGBs= parseInt(bar/1000000000);
		alert ("Your filesize is too large to be uploaded:" + inGBs + "GB.  Try zipping the file.");
		filenamer.value='';
		return false;
	}
	if (!foo.match(/^[A-Za-z0-9\.\-\_]+$/i)){//check that the filename is valid
		alert (foo + " is not a valid filename.  Please considering renaming your file and try again.");
		filenamer.files[0].name='';
		return false;
	}
	return ;
}
function validateInputs(fromPage){
	var valid="";
	if (/(setup|retrieve)/.test(fromPage) ){
	}else if (  document.getElementById("disclaimer").checked){
	}else{
		errorInput("disclaimer_div");
		valid=" - You have not indicated that you have read and agree to the disclaimer box outlined by the ABCC\n";
	}

	if (/submit/.test(fromPage)){
		if (document.myform.search_trace_arc_query.value == "" )  {
			valid = valid + " - Search trace archives cannot be empty\n";
		}
		if (document.myform.search_trace_arc_param.value=="gene"){
			if (document.myform.ref_gene.value==""){
				document.myform.ref_gene.value=document.myform.search_trace_arc_query.value;
			}
		}
	}else if (/retrieve/.test(fromPage)){
		if (document.myform.user_requestor_id.value==""){
			valid = valid + " - You have not specified a request id\n";
		}
		/*var d=document.myform.user_submission_type.selectedIndex;
		if (/none/.test(document.myform.user_submission_type.options[d].value)){
			valid = valid + " - You have not specified a submission type\n";
			errorInput("user_submission_type_row");
		}
		*/
		if (valid!=""){
			valid= "Your request could not submitted because\n" + valid ;
			alert(valid);
			return false;
		}else{
			return true;
		}
		
	}else if (/sub_analysis/.test(fromPage)){
		if ( typeof (document.myform.compare_groupfile)!='undefined'  && document.myform.compare_target_pop.value == ""){
			valid = valid + " - You must specify a header in your file indicating your 'normal' population.\n";
			errorInput("compare_target_pop_div");
			
		}
		if (document.myform.user_inputformat.value==''){
			valid= valid + " - You must specify a input format type\n";
			errorInput("user_submission_type_row");
		}
		// if (document.myform.recaptcha_response_field.value ==''){
		// 	valid=valid + " - You did not enter Captcha\n";
		// }
	}
	if (/(input|sub)_analysis/.test(fromPage)){
		if (document.myform.user_eligibility.value >= 20 && !/(vuonghm|huetogo)/.test(document.myform.user_email.value)){
			alert ("You have submitted too many requests within a 24 hour timeframe.(" + document.myform.user_eligibility.value + ")  Please contact us or wait to submit another request.")
			return false;
		}
	}
	if (document.myform.user_previous_id != ''){
	}else if (/setup/.test(fromPage)){
	}else if (document.myform.user_file.value!=''){
		var matched=/(txt|text|csv|tsv|gz|zip|tar|vcf|cg|pileup|soap|gff|maq)/i.exec(document.myform.user_file.value);
		if (matched==null){
			valid= valid +' - Your file must end in *txt.  If it is in a different file format, then it must end in that specified suffix.\ne.g.  vcf files must end in .vcf'+ matched;
			errorInput("user_file_div");
		}else if (/input_analysis/.test(fromPage)){
			
		}else{
			var d = document.myform.user_inputformat.selectedIndex;
			var val = document.myform.user_inputformat.options[d].value;
			if (/(gz|zip|tar)/i.test(matched[0])){
				alert ('skipped');
			}else if (val.search(matched[0])==-1){
				valid= valid + " - Your input file formats do not match \nFile suffix: "+ matched[0] + " vs Input format: " + val + "\n";
				errorInput ("file");
			}
		}
	}else if (document.myform.user_input_fullpath!=undefined && document.myform.user_input_fullpath!=''){
		var matched=/(txt|text|csv|tsv|gz|zip|tar|vcf|cg|pileup|soap|gff|maq)/i.exec(document.myform.user_input_fullpath.value);
		if (matched==null){
			valid= valid+" - Your file must end in *txt.  If it is in a different file format, then it must end in that specified suffix.\ne.g.  vcf files must end in .vcf\n";
			errorInput("tork_path");
		}else if (/input_analysis/.test(fromPage)){
		}else{
			var d = document.myform.user_inputformat.selectedIndex;
			var val = document.myform.user_inputformat.options[d].value;
			if (/(gz|zip|tar)/i.test(matched[0])){
				alert ('skipped');
			}else if (val.search(matched[0])==-1){
				alert ("Your input file formats do not \nFile suffix: "+ matched[0] + " vs Input format: " + val );
				return false;
			}
		}
	}
	var newContent = "<input type='hidden' name='mydata' value='1' />";
	WriteContentIntoID('mydiv',newContent);
	if (typeof document.myform.user_email=="undefined" || document.myform.user_email.value == ""){
		valid = valid + " - E-mail address cannot be empty\n";
		errorInput ("email_div");
	}
	if ( typeof document.myform.confirmemail=="undefined"|| document.myform.confirmemail.value=="" || document.myform.confirmemail.value!=document.myform.user_email.value){
		valid = valid + " - The confirmation e-mail address does not match or is empty\n";
		errorInput ("email_val_div");
	}
	// content="chr1 21580 21580 C T\n"+
	// "chr10 105164905 105164905 A -\n"+
	// "chr10 105821222 105821222 A -\n"+
	// "chr11 20112466 20112466 T C\n"+
	// "chr11 27114906 27114906 T G\n"+
	// "chr11 7661056 7661056 A -\n"+
	// "chr1 216017736 216017736 T C\n"+
	// "chr1 21924946 21924946 T C\n"+
	// "chr12 20787846 20787846 T A\n"+
	// "chr12 21593346 21593346 T G\n"+
	// "chr12 21991106 21991106 T C\n"+
	// "chr12 22015896 22015896 T C\n"+
	// "chr12 25380276 25380276 T C\n"+
	// "chr12 25398306 25398306 T C\n"+
	// "chr1 248201606 248201606 T A\n"+
	// "chr1 28931966 28931966 T C\n"+
	// "chr13 28599006 28599006 T G\n"+
	// "chr17 29664536 29664536 - GA\n"+
	// "chr18 28919916 28919916 T G\n"+
	// "chr19 12739502 12739502 A -\n"+
	// "chr19 20044886 20044886 T C\n"+
	// "chr19 21300346 21300346 T A\n"+
	// "chr19 22271096 22271096 T C\n"+
	// "chr19 22499326 22499326 T C\n"+
	// "chr19 22939096 22939096 T G\n"+
	// "chr19 23542956 23542956 T G\n"+
	// "chr19 2853696 2853696 T C\n"+
	// "chr20 20018196 20018196 T C\n"+
	// "chr2 219679156 219679156 T G\n"+
	// "chr22 24121566 24121566 T C\n"+
	// "chr22 24145526 24145526 A -\n"+
	// "chr22 24145566 24145566 - CGATGGG\n"+
	// "chr2 242196126 242196126 - CTGGATTTTG\n"+
	// "chr7 2979516 2979516 T G\n"+
	// "chr9 21971126 21971126 - AG\n"+
	// "chr9 21971146 21971146 T A\n"+
	// "chr9 21971176 21971176 T C\n"+
	// "chr9 21971186 21971186 - G\n"+
	// "chr9 21971186 21971186 - GCCACTCG\n"+
	// "chr9 21974696 21974696 T G\n"+
	// "chr9 21974746 21974746 - CG\n"+
	// "chr9 21974786 21974786 T A\n"+
	// "chr9 21994216 21994216 - GGCGC";
	// if ( document.myform.user_typed_input.value==content){
	// 	document.myform.typed_ct.value=2;//indicates to AVIA that this was sample text and should not be run
	// 	return true;
	// }
	if (valid == ""){
		myloader(true);
		if (/setup/.test(fromPage)){
			var ans=confirm ("You will recieve an email confirming this request.  Your changes will not take affect until this email has been confirmed by clicking on the link provided in the email.  By submitting this request, your AVIA feature annotations will always run the queries and parameters.  You can always add more databases on a case by case basis. Click 'OK' if you agree or 'Cancel' to go back");
			if (ans==false){
				return false;
			}else{
				return true;
			}
		}else{
			if (typeof(document.myform.user_input_fullpath)!='undefined'){
				alert("Please make sure that the permission to the directories and file(s) are set correctly.");
			}
			return true;
		}
	}else{
		valid= "Please fix the errors in red:\n" + valid;
		alert(valid);
		return false;
	}
}
function timedRefresh(timeoutPeriod) {
	setTimeout("location.reload(true);",timeoutPeriod);
}
function errorInput(idvar){
	// alert ("testing" +idvar + " in errInp");
	document.getElementById(idvar).style.color="red";
	return;
}
function checkAllCat(bx,cat){
	var cbs = document.getElementsByTagName('input');
  for(var i=0; i < cbs.length; i++) {
    if(cbs[i].type == 'checkbox' ) {
    	if(cbs[i].className==cat){
        	cbs[i].checked = bx.checked;
    	}
    }
  }
 
}
function checkAllAnnot(){
	var cbs = document.getElementsByTagName('input');
	var mychecked=document.getElementById("checkall").value;
	if (/true/.test(mychecked)){mychecked=false;}else{mychecked=true;}
	document.getElementById("checkall").value=mychecked;
  for(var i=0; i < cbs.length; i++) {
    if(cbs[i].type == 'checkbox' ) {
    	if(cbs[i].name.substring(0,14)=="report_annotdb" ){
    		if (!/Flanking/.test(cbs[i].name)){
				cbs[i].checked = mychecked;
    		}
    	}else if (cbs[i].id.substring(0,5)=="allmy"){
    		cbs[i].checked = mychecked;
    	}
    }
  }
 
}
function confirmVer(){
	if (/hg18/.test(document.myform.ref_ver.value)){
		confirm ("Our annotation databases are not mapped in Human v36 coordinates.  In order to use the older build, you understand that UCSC's Liftover will be used and may result in errors.  By clicking 'OK', you are acknowledging that you understand the risks.");
	}
}
function insertCutoff(xname,num){
	if (/cutoff/i.test(document.getElementById(xname).value)){
		showOrHide("fil_myfilter"+num,true);
	}else{
		showOrHide("fil_myfilter"+num,false);
	}
}
function refresherByFile (timeoutperiod){
	setTimeout("location.reload(true);", timeoutperiod)
}
function validateGL(id){
	alert(id );
}
function showOrHideGL(page)
{
	var selectobject=document.getElementById("whattodo");
	var x=document.getElementById("whattodo").selectedIndex;
	for (var i=0; i<selectobject.length; i++){
		if (i == x){
			showOrHide(selectobject[i].value,true);
		}else{
			showOrHide(selectobject[i].value,false);
		}
		
	}
	if (/convert/.test(page)){
		if (x == 0){
			showOrHide('format_type_cell',true);
			showOrHide('format_type_cell2',false);
		}else if (x==1){
			showOrHide('format_type_cell',false);
			showOrHide('format_type_cell2',true);
		}else{
			showOrHide("format_type_cell",false);
			showOrHide('format_type_cell2',false);
		}
	}else{
		if (x==0){
			showOrHide('file1',true);
			showOrHide('file2',true);
			showOrHide('submit_row',true);
			showOrHide('list',false);
			// showOrHide('dbopts',false);
			showOrHide('caseid',true);
			showOrHide('email_div',false);
		}else if (x==1){
			showOrHide('file1',true);
			showOrHide('file2',false);
			showOrHide('submit_row',true);
			showOrHide('list',false);
			// showOrHide('dbopts',false);
			showOrHide('caseid',true);
			showOrHide('email_div',false);
		}else if (x==2){
			showOrHide('file1',false);
			showOrHide('file2',false);
			showOrHide('submit_row',false);
			showOrHide('list',true);
			// showOrHide('dbopts',false);
			showOrHide('caseid',true);
			showOrHide('email_div',false);
		}else if (x==3){
			showOrHide('file1',true);
			showOrHide('file2',false);
			showOrHide('submit_row',true);
			showOrHide('list',false);
			// showOrHide('dbopts',false);
			showOrHide('caseid',false);
			showOrHide('email_div',true);
		}
	}
	return;
}
function showOrHide(id,show){
	var item = document.getElementById(id);
	switch(show){
	case '':
	case 'true':
			if (item.style.display=="none"){
				show=true;
			}else{
				show=false;
			}
			break;
	}
	var imgidname="img_"+id;
	var item2;
	if (document.getElementById(imgidname)){
		item2=document.getElementById(imgidname);
	}
	console.log(show);
	if(show==true || show=='true'){
		if (item.tagName=='TR' || item.tagName=='tr' ){
			item.style.display="table-row";
		}else if (item.tagName=='TD' || item.tagName=='td'){
			item.style.display="table-cell";
		}else{
			item.style.display="block";
		}
		if (item2){item2.src='/images/collapse.gif';}
	}else{
		item.style.display="none";
		if (id=="fileupload"){
			document.myform.user_file.value='';
		}else if(id=="auth_path_a" ){
			document.myform.user_input_fullpath.value='';
			document.myform.user_out_name.value='';
		}else if (id=="auth_path_b" ){
			document.myform.user_input_name.value='';
			document.myform.user_abcc_supply_path.value='';
		}
		if (item2){item2.src='/images/expand.gif';}
	}
	if(/expr_value/i.test(id) && document.getElementById('trx_tissue').value == ''){
		showOrHide('expr_values',false);
	}
	
}
function notutorial(){
        if (document.myform.typed_ct.value==1){
                var turnoff=confirm("By changing the contents of the 'Sample' data, the tutorial pop ups will no longer appear. ");
                if (turnoff==true){
                  document.myform.typed_ct.value=0;
                }else{
                        //change the text back to the sample data
                        WriteContentIntoID('typed','sample');
                }
        }
}
function expandCategoriesNoForm(fieldName){
	var classarr= document.getElementsByClassName('category');
	for(var i=0; i < classarr.length; i++) {
		showOrHide(classarr[i].id,true);
	}
	return;
}
function expandCategories(){
	var classarr= document.getElementsByClassName('category');
	var noshow=document.myform.expanded.value;
	if (noshow==1){noshow=0;}else{noshow=1;}
	document.myform.expanded.value=noshow;
	for(var i=0; i < classarr.length; i++) {
		showOrHide(classarr[i].id,noshow);
	}
	if (document.myform.typed_ct.value==1){
        var exitTut=confirm ("Guided Tutorial:\n\nEach section contains available databases in their appropriate categories.  The databases for protein scoring algorithms (i.e SIFT, Polyphen2, Mutation Taster, and Mutation Assessor) and dbSNP are selected by default.  Check the 'Annotation' checkbox next to each database you wish to annotate with.  Each database is different in terms of what it reports.  For example, SIFT and polyphen2 gives you a score and prediction of whether a nonsynonymous mutation is damaging or tolerant/benign, while dbSNP will give you the id and het frequency.  To find out more information" +
                "on what is reported for each database, select 'Databases' > 'View Databases' on the top navigation pane.\n\n " +
                "If you wish to generate a Circos Plot for that same database, check the 'Visualization' checkbox.  A track is produced for each database selected.  When you retrieve your results, you will have the option to combine the Circos Plots in the order you chosse.  You may also check the 'Select All' for a particular category.  \n\nWhen you are finished, please enter the click the submit button.  This will complete the 'Quick Start' tutorial to AVIA.  For more information or in-depth features available in AVIA, please read the comprehensive tutorial located under 'User Resources' > 'Tutorials' on the top navigation pane.");
        if (exitTut==false){
                document.myform.typed_ct.value=0;
        }
    }
}
function runcircos(){
	if (document.myform.orderedlist.value ==''){
		alert ("You did not add any circos tracks to the box.");	
		return false;
	}else if (document.myform.orderedlist.value.split(/,/g).length - 1 ==1){
		alert ("Please select two or more circos tracks.");
		return false;
	}else{
		myloader(true);
		var newpngname= document.myform.orderedlist.value;
		var n=newpngname.replace(/(USER\_|\.png|,$)/g,''); 
		newpngname=n.replace(/,/g,"-");
		document.myform.pngnewname.value=newpngname;
		return true;
	}
}

function myloader(show){
	if (show=='true' || show == true){
		document.getElementById('loading').style.display="block";
		document.getElementById('bg').style.display="block";
	}else{
		document.getElementById('loading').style.display="none";
		document.getElementById('bg').style.display="none";
	}
	return;
}
function checkRelated(myinputname,currid){
	if (/circos/i.test(myinputname)){
		if (document.getElementById(myinputname).checked==true){
			alert ("Annotation must be run in order to view as a track in circos\n");
			document.getElementById(currid).checked="true";
		}
	}else{	
		if (document.getElementById(myinputname).checked != "on"){
			document.getElementById(myinputname).checked="true";
		}
	}
	return;
}
function scrollAndOpen()
{	
    url = window.location.href;
    url = url.split("#");
    if (url[1])
      {
        answerId = "div" + url[1];
        jQuery("#" + answerId).slideDown('fast');
        //Effect.SlideDown(answerId, {duration: 0.5});
      }
}

function changeText(id){
	if (document.getElementById('auth_path').style.display=="none"){
		WriteContentIntoID(id,'Click for more input options');
	}else{
		WriteContentIntoID(id,'Click to hide input options');
	}
	return;
}
///////////////////////////////////////////////
//Generic "write content into ID" function. //
///////////////////////////////////////////////
//Copyright 2006 Bontrager Connection, LLC
function WriteContentIntoID(id,content) {
	if (id=="typed"){
		var idvar = document.getElementById(id); 
		if (content.match(/sample/)){
			if (document.myform.user_modulesid.value==7){
				content="A1BG:p.C139*\n"+
					"A2ML1:p.T523A\n"+
					"AARSD1:p.D124N\n"+
					"BRAF:p.G30D\n"+
					"HRAS:p.A134S\n"+
					"HRAS:p.E62G\n"+
					"KRAS:p.A59G\n"+
					"NRAS:p.Q61R\n"+
					"TP53:p.A79D\n"+
					"TP53:p.C176*\n";
					inputformatnum=0;
			}else if (content.match(/VCF/)){
				content="## VCFv4.1 \n"+
				"##comments begin with ##\n"+
				"## Also, copy and paste may not work well due to tabs converted to spaces\n"+
				"## A header should always be included and should be formatted like the next line, beginning with a single #\n"+
				"#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO\tFORMAT\n"+
				"1	534317	.	G	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"1	534320	.	G	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"1	763080	.	G	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"2	519973	.	G	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"2	567924	.	A	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"2	633349	.	G	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"3	357208	.	A	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"3	387168	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"3	605311	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"3	733814	.	C	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"4	609695	.	T	C	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"4	680365	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"4	825992	.	A	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"5	854896	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"6	419508	.	C	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"6	425837	.	G	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"6	425839	.	T	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"6	450901	.	T	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"6	501597	.	T	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"6	676129	.	A	C	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"6	676184	.	A	C	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"6	677026	.	A	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"7	134053	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"7	334400	.	G	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"7	782423	.	T	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"8	337586	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"8	787266	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"9	883368	.	C	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"10	189348	.	T	C	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"10	615349	.	A	C	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"11	194908	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"11	805728	.	A	C	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"11	881819	.	T	C	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	95040	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	571588	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	573946	.	C	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	574024	.	C	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	591861	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	595811	.	G	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	596171	.	C	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	596190	.	C	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	596505	.	C	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	596562	.	C	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	831550	.	T	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"12	843554	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"16	131676	.	G	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"16	440907	.	G	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"17	262039	.	G	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"17	291791	.	C	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"18	389081	.	T	C	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"18	450232	.	C	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"18	658362	.	C	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"19	262555	.	T	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"19	306640	.	C	T	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"19	536107	.	T	C	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"19	536108	.	G	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"20	360532	.	A	G	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"20	499340	.	G	A	100	PASS	.	GT:AP	1|0:1.000,1.000\n"+
				"20	773218	.	G	A	100	PASS	.	GT:AP	1|0:1.000,1.000";
				inputformatnum=1;typeFormat='VCF4 formatted file';
			}else{
				content="chr7 87160618 87160618 A C\nchr1 21580 21580 C T\n"+
				"chr10 105164905 105164905 A -\n"+
				"chr10 105821222 105821222 A -\n"+
				"chr11 20112466 20112466 T C\n"+
				"chr11 27114906 27114906 T G\n"+
				"chr11 7661056 7661056 A -\n"+
				"chr1 216017736 216017736 T C\n"+
				"chr1 21924946 21924946 T C\n"+
				"chr12 20787846 20787846 T A\n"+
				"chr12 21593346 21593346 T G\n"+
				"chr12 21991106 21991106 T C\n"+
				"chr12 22015896 22015896 T C\n"+
				"chr12 25380276 25380276 T C\n"+
				"chr12 25398306 25398306 T C\n"+
				"chr1 248201606 248201606 T A\n"+
				"chr1 28931966 28931966 T C\n"+
				"chr13 28599006 28599006 T G\n"+
				"chr17 29664536 29664536 - GA\n"+
				"chr18 28919916 28919916 T G\n"+
				"chr19 12739502 12739502 A -\n"+
				"chr19 20044886 20044886 T C\n"+
				"chr19 21300346 21300346 T A\n"+
				"chr19 22271096 22271096 T C\n"+
				"chr19 22499326 22499326 T C\n"+
				"chr19 22939096 22939096 T G\n"+
				"chr19 23542956 23542956 T G\n"+
				"chr19 2853696 2853696 T C\n"+
				"chr20 20018196 20018196 T C\n"+
				"chr2 219679156 219679156 T G\n"+
				"chr22 24121566 24121566 T C\n"+
				"chr22 24145526 24145526 A -\n"+
				"chr22 24145566 24145566 - CGATGGG\n"+
				"chr2 242196126 242196126 - CTGGATTTTG\n"+
				"chr7 2979516 2979516 T G\n"+
				"chr9 21971126 21971126 - AG\n"+
				"chr9 21971146 21971146 T A\n"+
				"chr9 21971176 21971176 T C\n"+
				"chr9 21971186 21971186 - G\n"+
				"chr9 21971186 21971186 - GCCACTCG\n"+
				"chr9 21974696 21974696 T G\n"+
				"chr9 21974746 21974746 - CG\n"+
				"chr9 21974786 21974786 T A\n"+
				"chr9 21994216 21994216 - GGCGC";
				inputformatnum=2;typeFormat='ANNOVAR format input (BED)';

			}
			var selectEl = document.getElementById('sampleformat');
			var optionEls = selectEl.getElementsByTagName('option');
			for (var i = 0, oEl; oEl = optionEls[i]; i++) {
				// loop counts start from 0, so the 3rd option: 0, 1, 2
				oEl.selected = (i == inputformatnum) ? 'selected' : false;
			}
			idvar.value = '';
			idvar.value = content;
			if (document.myform.typed_ct.value==0){

				var exitTut;
				if (document.myform.user_modulesid==7){
					exitTut=confirm("Guided Tutorial:\n\nIn the textbox is a subset of mutations taken from the COSMIC protein coding mutations and will be used as example data through this guided tutorial.  The coordinates from the dataset will be mapped back to genomic coordinates using NCBI Build 37 (or UCSC's hg19).  For this sample set, you do not need to change this values.\n\nThe next step is to enter your email below in the appropriate input boxes.\n\nIf you wish to by-pass the guided tutorial and see a sample result formats before submitting a request, please click on the link to the left box'View Sample Results Page'.\n\nLinks to our comprehensive tutorial and FAQ page are located at the top of the page.  If you wish to exit the guided tutorial, simply click the 'Cancel' button on the pop-up.  If you wish to continue with the guided tutorial, do not enable the checkbox that says 'Prevent this page from creating additional dialogs'.");
				}else{
					exitTut=confirm("Guided Tutorial:\n\nIn the textbox is a subset of mutations taken from the Complete Genomes variations set and will be used as example data through this guided tutorial.  You will see that this input type has been changed  to '"+typeFormat+"'.  The coordinates from the CGI dataset are from NCBI Build 37 (or UCSC's hg19).  For this sample set, you do not need to change these values.\n\nThe next step is to enter your email below in the appropriate input boxes.\n\nIf you wish to by-pass the guided tutorial and see a sample result formats before submitting a request, please click on the link to the left box'View Sample Results Page'.\n\nLinks to our comprehensive tutorial and FAQ page are located at the top of the page.  If you wish to exit the guided tutorial, simply click the 'Cancel' button on the pop-up.  If you wish to continue with the guided tutorial, do not enable the checkbox that says 'Prevent this page from creating additional dialogs'.");
				}
				if (exitTut==false){
					document.myform.typed_ct.value=0;
				}else{
					document.myform.typed_ct.value=1;
				}
				return;
			}
			
		}else if (document.myform.typed_ct.value==0) {
			if (document.myform.typed.value.match(/Enter/)){
				idvar.value = content;
			}
		}	
			
		return;
	}
	if(document.getElementById) {
		var idvar = document.getElementById(id);
		idvar.innerHTML = '';
		idvar.innerHTML = content;
		}
	else if(document.all) {
		var idvar = document.all[id];
		idvar.innerHTML = content;
	}
	else if(document.layers) {
		var idvar = document.layers[id];
		idvar.document.open();
		idvar.document.write(content);
		idvar.document.close();
	}
} // end of WriteContentIntoID()
function checkIfSample(){
        // alert("checkIfSample" + document.myform.typed_ct.value);
        if (document.myform.typed_ct.value==1){
                var exitTut=confirm ("Guided tutorial:\n\nNow, navigate to the section titled 'Annotation and Visualization Parameters' to select the available databases that you wish to annotate your dataset with. Click on the button 'Expand/Collapse' All Categories to Customize' to see all options.");
                if (exitTut==false){
                        document.myform.typed_ct.value=0;
                }
        }
}
function addDB(id){
	var oldcontent = document.getElementById(id).innerHTML;
	var addContent;
	if (/viz/.test(id)){
		if (document.myform.typed_ct.value==1){
            var exitTut=alert ("Guided Tutorial:\n\nIn this section, you can upload your own database to annotate with. \n\nThe format for the database file must be in the tab delimited format:\nChromosome->Start->End->Feature Name\n");
            if (exitTut==false){
                    alert(document.myform.typed_ct.value);
            }
    	}
		var counter=parseInt(document.getElementById('user_counter').value) +1 ;
		if (counter==11){
			alert ("You have exceeded the number of allowed databases(10)");
			return;
		}
		document.getElementById('user_counter').value=counter;
		var addContent = '<table><tr><td><label for=\"file'+counter+'\">Filename'+counter+': </label></td><td width=\"50%\"><input type=\"file\" name=\"report_userdefined_db' + counter +'\" id=\"file' +counter+'\" onchange=\"sizeBind(this)\"> </td>' ;
		addContent =addContent + '<td><input type="hidden" name="report_annotdb_userdefined_db'+counter +'" value="on" /><input type="checkbox" name="report_circos_type_userdefined_db' + counter + '" onClick="javascript:showOrHide(\'_id' + counter + '\',\'\')" /> Visualization</td></tr>';
		addContent =addContent + '</tr><tr><td><a title="This is used in all annotation headers and Circos legend">Unique database Name:</a></td><td colspan=\"2\"><input type="text" name="report_userdefined_annotdb' + counter + '" value="userdb' + counter + '"  onKeyPress=\"return disableEnterKey(this,event)\" />';
		addContent= addContent + '</tr><tr id="_id' + counter +'" style="display:none"><td align=\"right\">Circos Track Type</td><td><select name="report_viz_type_userdefined_db'+ counter+'"\"> ' +
					'<option value=""></option>' +
					'<option value="scatter">Scatter</option>' +
					'<option value="text">Text</option>' +
					'<option value="line">Line</option>' +
					'<option value="histogram">Histogram</option>' +
				'</select></td></tr></table>';
	}else if (/filter/.test(id)){
		var counter=parseInt(document.getElementById('countfilter').value) +1 ;
		if (counter==6){
			alert ("You have exceeded the number of allowed filter databases(6)");
			return;
		}
		document.getElementById('countfilter').value=counter;
		var addContent = '<table><tr><td>'+ counter + ') Select Filter database:</td><td>'+
					'<select name="fil_filter'+counter+'" > '+
						'<option value="">&nbsp;</option>'+
						'<option value="snp138">SNP (138)</option>'+
						'<option value="1000g">1000 Genomes</option>'+
						'<option value="hapmap_3_3">Hap Map</option>'+
						'<option value="avsift">SIFT Scores</option>'+
						'<option value="ljb2_pp2hvar">Polyphen2 Scores</option>'+
						'<option value="cosmicdbv68">COSMIC</option>'+
					'</select>'+
				'</td><td align="right">'+
					'<select name="fil_keep'+counter+'" id="fil_keep'+counter+'" onChange="javascript:insertCutoff(\'fil_keep'+counter+'\',\''+counter+'\')">'+
						'<option value="">&nbsp;</option>'+
						'<option value="filtered">Keep the variants NOT in db</option>'+
						'<option value="dropped">Keep the variants FOUND in db</option>'+
						'<option value="keepcutoff">Keep variants with cutoff value &gt;= cutoff value </option>'+
						'<option value="dropcutoff">Keep variants with cutoff value &lt;= cutoff value </option>'+
						'<option value="default">Use default</option>'+
					'</select>'+
				'</td>'+
			'</tr>'+
			'<tr id="fil_myfilter'+counter+'" style="display:none">'+
				'<td  colspan="3" align="right">Cutoff: <input type="text" name="fil_keep'+counter+'cutoff" />'+
				'</td>'+
			'</tr></table>';
	}else{
		var addContent = '<label for=\"file'+counter+'\">Filename'+counter+': </label><input type=\"file\" name=\"report_userdefined_db' + counter +'\" id=\"file' +counter+'\" onchange=\"sizeBind(this)\"/>' ;
	}
	var newcontent=oldcontent  + addContent+ '<br />';
	WriteContentIntoID(id,newcontent);
	showOrHide(id,true);
}
function testTAInputs(){
	var myregex = /\n/g;
	var myQuery=new Array();
	var querystring=document.myform.search_trace_arc_query.value;
	var base="http://www.ncbi.nlm.nih.gov/Traces/trace.cgi?&cmd=retrieve&val=SPECIES_CODE %3D \""+document.myform.search_trace_arc_org.value+"\" and "
	var valid="";var isMultiple="";
	if (querystring == "" )  {
		alert( "Please specify a query before clicking TestQuery button\n");
		return;
	}
	if (	myregex.test(querystring)){
		myQuery=querystring.split(myregex);
		isMultiple="1";
	}
	if ( /,/.test(querystring)){
		myQuery=querystring.split(",");
		isMultiple="1";
	}
	if (isMultiple==""){
		myQuery.push(querystring);
	}
<!--	alert(myQuery.join("MOO"));foo;-->
	var test="";var target_url="";var SEARCHTERM="";var quotes="\"";
	if(document.myform.search_trace_arc_param.value == "gene"){
		SEARCHTERM="GENE_NAME";
	}else if(document.myform.search_trace_arc_param.value == "center"){
		SEARCHTERM="CENTER_NAME";
	}else if(document.myform.search_trace_arc_param.value == "ti"){
		SEARCHTERM="TI";
		quotes="";
	}
	var mylength=myQuery.length;
	target_url=base;
	if (isMultiple==1){target_url= target_url + "(";}
	for (var i=0;i<mylength;i++){
		myQuery[i]=myQuery[i].replace(/ /g,"");
		if (myQuery[i]==""){
			break;
		}
		target_url=target_url + SEARCHTERM + "%3D"+ quotes + myQuery[i] + quotes;
		if (i!=mylength-1){
			target_url = target_url + " or ";
		}
	}
	if (isMultiple==1){ target_url = target_url + ")";}
	target_url= target_url + "&retrieve=Submit";
	if (/retrieve=Submit/.test(target_url)){
		return (target_url);
	}else{
		return ('');
	}
}


/*
dragtable v1.0
June 26, 2008
Dan Vanderkam, http://danvk.org/dragtable/
               http://code.google.com/p/dragtable/

Instructions:
  - Download this file
  - Add <script src="dragtable.js"></script> to your HTML.
  - Add class="draggable" to any table you might like to reorder.
  - Drag the headers around to reorder them.

This is code was based on:
  - Stuart Langridge's SortTable (kryogenix.org/code/browser/sorttable)
  - Mike Hall's draggable class (http://www.brainjar.com/dhtml/drag/)
  - A discussion of permuting table columns on comp.lang.javascript

Licensed under the MIT license.
*/

//Here's the notice from Mike Hall's draggable script:
//*****************************************************************************
//Do not remove this notice.
//
//Copyright 2001 by Mike Hall.
//See http://www.brainjar.com for terms of use.
//*****************************************************************************
dragtable = {
// How far should the mouse move before it's considered a drag, not a click?
dragRadius2: 100,
setMinDragDistance: function(x) {
  dragtable.dragRadius2 = x * x;
},

// How long should cookies persist? (in days)
cookieDays: 365,
setCookieDays: function(x) {
  dragtable.cookieDays = x;
},

// Determine browser and version.
// TODO: eliminate browser sniffing except where it's really necessary.
Browser: function() {
  var ua, s, i;

  this.isIE    = false;
  this.isNS    = false;
  this.version = null;
  ua = navigator.userAgent;

  s = "MSIE";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isIE = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  s = "Netscape6/";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = parseFloat(ua.substr(i + s.length));
    return;
  }

  // Treat any other "Gecko" browser as NS 6.1.
  s = "Gecko";
  if ((i = ua.indexOf(s)) >= 0) {
    this.isNS = true;
    this.version = 6.1;
    return;
  }
},
browser: null,

// Detect all draggable tables and attach handlers to their headers.
init: function() {
  // Don't initialize twice
  if (arguments.callee.done) return;
  arguments.callee.done = true;
  if (_dgtimer) clearInterval(_dgtimer);
  if (!document.createElement || !document.getElementsByTagName) return;

  dragtable.dragObj.zIndex = 0;
  dragtable.browser = new dragtable.Browser();
  forEach(document.getElementsByTagName('table'), function(table) {
    if (table.className.search(/\bdraggable\b/) != -1) {
      dragtable.makeDraggable(table);
    }
  });
},

// The thead business is taken straight from sorttable.
makeDraggable: function(table) {
  if (table.getElementsByTagName('thead').length == 0) {
    the = document.createElement('thead');
    the.appendChild(table.rows[0]);
    table.insertBefore(the,table.firstChild);
  }

  // Safari doesn't support table.tHead, sigh
  if (table.tHead == null) {
    table.tHead = table.getElementsByTagName('thead')[0];
  }

  var headers = table.tHead.rows[0].cells;
  for (var i = 0; i < headers.length; i++) {
    headers[i].onmousedown = dragtable.dragStart;
  }

		// Replay reorderings from cookies if there are any.
		if (dragtable.cookiesEnabled() && table.id &&
				table.className.search(/\bforget-ordering\b/) == -1) {
			dragtable.replayDrags(table);
		}
},

// Global object to hold drag information.
dragObj: new Object(),

// Climb up the DOM until there's a tag that matches.
findUp: function(elt, tag) {
  do {
    if (elt.nodeName && elt.nodeName.search(tag) != -1)
      return elt;
  } while (elt = elt.parentNode);
  return null;
},

// clone an element, copying its style and class.
fullCopy: function(elt, deep) {
  var new_elt = elt.cloneNode(deep);
  new_elt.className = elt.className;
  forEach(elt.style,
      function(value, key, object) {
        if (value == null) return;
        if (typeof(value) == "string" && value.length == 0) return;

        new_elt.style[key] = elt.style[key];
      });
  return new_elt;
},

eventPosition: function(event) {
  var x, y;
  if (dragtable.browser.isIE) {
    x = window.event.clientX + document.documentElement.scrollLeft
      + document.body.scrollLeft;
    y = window.event.clientY + document.documentElement.scrollTop
      + document.body.scrollTop;
    return {x: x, y: y};
  }
  return {x: event.pageX, y: event.pageY};
},

// Determine the position of this element on the page. Many thanks to Magnus
// Kristiansen for help making this work with "position: fixed" elements.
absolutePosition: function(elt, stopAtRelative) {
 var ex = 0, ey = 0;
 do {
   var curStyle = dragtable.browser.isIE ? elt.currentStyle
                                         : window.getComputedStyle(elt, '');
   var supportFixed = !(dragtable.browser.isIE &&
                        dragtable.browser.version < 7);
   if (stopAtRelative && curStyle.position == 'relative') {
     break;
   } else if (supportFixed && curStyle.position == 'fixed') {
     // Get the fixed el's offset
     ex += parseInt(curStyle.left, 10);
     ey += parseInt(curStyle.top, 10);
     // Compensate for scrolling
     ex += document.body.scrollLeft;
     ey += document.body.scrollTop;
     // End the loop
     break;
   } else {
     ex += elt.offsetLeft;
     ey += elt.offsetTop;
   }
 } while (elt = elt.offsetParent);
 return {x: ex, y: ey};
},

// MouseDown handler -- sets up the appropriate mousemove/mouseup handlers
// and fills in the global dragtable.dragObj object.
dragStart: function(event, id) {
  var el;
  var x, y;
  var dragObj = dragtable.dragObj;

  var browser = dragtable.browser;
  if (browser.isIE)
    dragObj.origNode = window.event.srcElement;
  else
    dragObj.origNode = event.target;
  var pos = dragtable.eventPosition(event);

  // Drag the entire table cell, not just the element that was clicked.
  dragObj.origNode = dragtable.findUp(dragObj.origNode, /T[DH]/);

  // Since a column header can't be dragged directly, duplicate its contents
  // in a div and drag that instead.
  // TODO: I can assume a tHead...
  var table = dragtable.findUp(dragObj.origNode, "TABLE");
  dragObj.table = table;
  dragObj.startCol = dragtable.findColumn(table, pos.x);
  if (dragObj.startCol == -1) return;

  var new_elt = dragtable.fullCopy(table, false);
  new_elt.style.margin = '0';

  // Copy the entire column
  var copySectionColumn = function(sec, col) {
    var new_sec = dragtable.fullCopy(sec, false);
    forEach(sec.rows, function(row) {
      var cell = row.cells[col];
      var new_tr = dragtable.fullCopy(row, false);
      if (row.offsetHeight) new_tr.style.height = row.offsetHeight + "px";
      var new_td = dragtable.fullCopy(cell, true);
      if (cell.offsetWidth) new_td.style.width = cell.offsetWidth + "px";
      new_tr.appendChild(new_td);
      new_sec.appendChild(new_tr);
    });
    return new_sec;
  };

  // First the heading
  if (table.tHead) {
    new_elt.appendChild(copySectionColumn(table.tHead, dragObj.startCol));
  }
  forEach(table.tBodies, function(tb) {
    new_elt.appendChild(copySectionColumn(tb, dragObj.startCol));
  });
  if (table.tFoot) {
    new_elt.appendChild(copySectionColumn(table.tFoot, dragObj.startCol));
  }

  var obj_pos = dragtable.absolutePosition(dragObj.origNode, true);
  new_elt.style.position = "absolute";
  new_elt.style.left = obj_pos.x + "px";
  new_elt.style.top = obj_pos.y + "px";
  new_elt.style.width = dragObj.origNode.offsetWidth + "px";
  new_elt.style.height = dragObj.origNode.offsetHeight + "px";
  new_elt.style.opacity = 0.7;

  // Hold off adding the element until this is clearly a drag.
  dragObj.addedNode = false;
  dragObj.tableContainer = dragObj.table.parentNode || document.body;
  dragObj.elNode = new_elt;

  // Save starting positions of cursor and element.
  dragObj.cursorStartX = pos.x;
  dragObj.cursorStartY = pos.y;
  dragObj.elStartLeft  = parseInt(dragObj.elNode.style.left, 10);
  dragObj.elStartTop   = parseInt(dragObj.elNode.style.top,  10);

  if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = 0;
  if (isNaN(dragObj.elStartTop))  dragObj.elStartTop  = 0;

  // Update element's z-index.
  dragObj.elNode.style.zIndex = ++dragObj.zIndex;

  // Capture mousemove and mouseup events on the page.
  if (browser.isIE) {
    document.attachEvent("onmousemove", dragtable.dragMove);
    document.attachEvent("onmouseup",   dragtable.dragEnd);
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  } else {
    document.addEventListener("mousemove", dragtable.dragMove, true);
    document.addEventListener("mouseup",   dragtable.dragEnd, true);
    event.preventDefault();
  }
},

// Move the floating column header with the mouse
// TODO: Reorder columns as the mouse moves for a more interactive feel.
dragMove: function(event) {
  var x, y;
  var dragObj = dragtable.dragObj;

  // Get cursor position with respect to the page.
  var pos = dragtable.eventPosition(event);

  var dx = dragObj.cursorStartX - pos.x;
  var dy = dragObj.cursorStartY - pos.y;
  if (!dragObj.addedNode && dx * dx + dy * dy > dragtable.dragRadius2) {
    dragObj.tableContainer.insertBefore(dragObj.elNode, dragObj.table);
    dragObj.addedNode = true;
  }

  // Move drag element by the same amount the cursor has moved.
  var style = dragObj.elNode.style;
  style.left = (dragObj.elStartLeft + pos.x - dragObj.cursorStartX) + "px";
  style.top  = (dragObj.elStartTop  + pos.y - dragObj.cursorStartY) + "px";

  if (dragtable.browser.isIE) {
    window.event.cancelBubble = true;
    window.event.returnValue = false;
  } else {
    event.preventDefault();
  }
},

// Stop capturing mousemove and mouseup events.
// Determine which (if any) column we're over and shuffle the table.
dragEnd: function(event) {
  if (dragtable.browser.isIE) {
    document.detachEvent("onmousemove", dragtable.dragMove);
    document.detachEvent("onmouseup", dragtable.dragEnd);
  } else {
    document.removeEventListener("mousemove", dragtable.dragMove, true);
    document.removeEventListener("mouseup", dragtable.dragEnd, true);
  }

  // If the floating header wasn't added, the mouse didn't move far enough.
  var dragObj = dragtable.dragObj;
  if (!dragObj.addedNode) {
    return;
  }
  dragObj.tableContainer.removeChild(dragObj.elNode);

  // Determine whether the drag ended over the table, and over which column.
  var pos = dragtable.eventPosition(event);
  var table_pos = dragtable.absolutePosition(dragObj.table);
  if (pos.y < table_pos.y ||
      pos.y > table_pos.y + dragObj.table.offsetHeight) {
    return;
  }
  var targetCol = dragtable.findColumn(dragObj.table, pos.x);
  if (targetCol != -1 && targetCol != dragObj.startCol) {
    dragtable.moveColumn(dragObj.table, dragObj.startCol, targetCol);
    if (dragObj.table.id && dragtable.cookiesEnabled() &&
					dragObj.table.className.search(/\bforget-ordering\b/) == -1) {
      dragtable.rememberDrag(dragObj.table.id, dragObj.startCol, targetCol);
    }
  }
},

// Which column does the x value fall inside of? x should include scrollLeft.
findColumn: function(table, x) {
  var header = table.tHead.rows[0].cells;
  for (var i = 0; i < header.length; i++) {
    //var left = header[i].offsetLeft;
    var pos = dragtable.absolutePosition(header[i]);
    //if (left <= x && x <= left + header[i].offsetWidth) {
    if (pos.x <= x && x <= pos.x + header[i].offsetWidth) {
      return i;
    }
  }
  return -1;
},

// Move a column of table from start index to finish index.
// Based on the "Swapping table columns" discussion on comp.lang.javascript.
// Assumes there are columns at sIdx and fIdx
moveColumn: function(table, sIdx, fIdx) {
  var row, cA;
  var i=table.rows.length;
  while (i--){
    row = table.rows[i]
    var x = row.removeChild(row.cells[sIdx]);
    if (fIdx < row.cells.length) {
      row.insertBefore(x, row.cells[fIdx]);
    } else {
      row.appendChild(x);
    }
  }

  // For whatever reason, sorttable tracks column indices this way.
  // Without a manual update, clicking one column will sort on another.
  var headrow = table.tHead.rows[0].cells;
  for (var i=0; i<headrow.length; i++) {
    headrow[i].sorttable_columnindex = i;
  }
},

// Are cookies enabled? We should not attempt to set cookies on a local file.
cookiesEnabled: function() {
  return (window.location.protocol != 'file:') && navigator.cookieEnabled;
},

// Store a column swap in a cookie for posterity.
rememberDrag: function(id, a, b) {
  var cookieName = "dragtable-" + id;
  var prev = dragtable.readCookie(cookieName);
  var new_val = "";
  if (prev) new_val = prev + ",";
  new_val += a + "/" + b;
  dragtable.createCookie(cookieName, new_val, dragtable.cookieDays);
},

	// Replay all column swaps for a table.
	replayDrags: function(table) {
		if (!dragtable.cookiesEnabled()) return;
		var dragstr = dragtable.readCookie("dragtable-" + table.id);
		if (!dragstr) return;
		var drags = dragstr.split(',');
		for (var i = 0; i < drags.length; i++) {
			var pair = drags[i].split("/");
			if (pair.length != 2) continue;
			var a = parseInt(pair[0]);
			var b = parseInt(pair[1]);
			if (isNaN(a) || isNaN(b)) continue;
			dragtable.moveColumn(table, a, b);
		}
	},

// Cookie functions based on http://www.quirksmode.org/js/cookies.html
// Cookies won't work for local files.
cookiesEnabled: function() {
  return (window.location.protocol != 'file:') && navigator.cookieEnabled;
},

createCookie: function(name,value,days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
  }
  else var expires = "";

		var path = document.location.pathname;
  document.cookie = name+"="+value+expires+"; path="+path
},

readCookie: function(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
},

eraseCookie: function(name) {
  dragtable.createCookie(name,"",-1);
}

}

/* ******************************************************************
 Supporting functions: bundled here to avoid depending on a library
 ****************************************************************** */

//Dean Edwards/Matthias Miller/John Resig
//has a hook for dragtable.init already been added? (see below)
var dgListenOnLoad = false;

/* for Mozilla/Opera9 */
if (document.addEventListener) {
dgListenOnLoad = true;
document.addEventListener("DOMContentLoaded", dragtable.init, false);
}

/* for Internet Explorer */
/*@cc_on @*/
/*@if (@_win32)
dgListenOnLoad = true;
document.write("<script id=__dt_onload defer src=//0)><\/script>");
var script = document.getElementById("__dt_onload");
script.onreadystatechange = function() {
  if (this.readyState == "complete") {
    dragtable.init(); // call the onload handler
  }
};
/*@end @*/

/* for Safari */
if (/WebKit/i.test(navigator.userAgent)) { // sniff
dgListenOnLoad = true;
var _dgtimer = setInterval(function() {
  if (/loaded|complete/.test(document.readyState)) {
    dragtable.init(); // call the onload handler
  }
}, 10);
}

/* for other browsers */
/* Avoid this unless it's absolutely necessary (it breaks sorttable) */
if (!dgListenOnLoad) {
window.onload = dragtable.init;
}

//Dean's forEach: http://dean.edwards.name/base/forEach.js
/*
forEach, version 1.0
Copyright 2006, Dean Edwards
License: http://www.opensource.org/licenses/mit-license.php
*/

//array-like enumeration
if (!Array.forEach) { // mozilla already supports this
Array.forEach = function(array, block, context) {
  for (var i = 0; i < array.length; i++) {
    block.call(context, array[i], i, array);
  }
};
}

//generic enumeration
Function.prototype.forEach = function(object, block, context) {
for (var key in object) {
  if (typeof this.prototype[key] == "undefined") {
    block.call(context, object[key], key, object);
  }
}
};

//character enumeration
String.forEach = function(string, block, context) {
Array.forEach(string.split(""), function(chr, index) {
  block.call(context, chr, index, string);
});
};

//globally resolve forEach enumeration
var forEach = function(object, block, context) {
if (object) {
  var resolve = Object; // default
  if (object instanceof Function) {
    // functions have a "length" property
    resolve = Function;
  } else if (object.forEach instanceof Function) {
    // the object implements a custom forEach method so use that
    object.forEach(block, context);
    return;
  } else if (typeof object == "string") {
    // the object is a string
    resolve = String;
  } else if (typeof object.length == "number") {
    // the object is array-like
    resolve = Array;
  }
  resolve.forEach(object, block, context);
}
};
/*
SortTable
version 2
7th April 2007
Stuart Langridge, http://www.kryogenix.org/code/browser/sorttable/

Instructions:
Download this file
Add <script src="sorttable.js"></script> to your HTML
Add class="sortable" to any table you'd like to make sortable
Click on the headers to sort

Thanks to many, many people for contributions and suggestions.
Licenced as X11: http://www.kryogenix.org/code/browser/licence.html
This basically means: do what you want with it.
*/


var stIsIE = /*@cc_on!@*/false;

sorttable = {
init: function() {
  // quit if this function has already been called
  if (arguments.callee.done) return;
  // flag this function so we don't do the same thing twice
  arguments.callee.done = true;
  // kill the timer
  if (_timer) clearInterval(_timer);
  
  if (!document.createElement || !document.getElementsByTagName) return;
  
  sorttable.DATE_RE = /^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/;
  
  forEach(document.getElementsByTagName('table'), function(table) {
    if (table.className.search(/\bsortable\b/) != -1) {
      sorttable.makeSortable(table);
    }
  });
  
},

makeSortable: function(table) {
  if (table.getElementsByTagName('thead').length == 0) {
    // table doesn't have a tHead. Since it should have, create one and
    // put the first table row in it.
    the = document.createElement('thead');
    the.appendChild(table.rows[0]);
    table.insertBefore(the,table.firstChild);
  }
  // Safari doesn't support table.tHead, sigh
  if (table.tHead == null) table.tHead = table.getElementsByTagName('thead')[0];
  
  if (table.tHead.rows.length != 1) return; // can't cope with two header rows
  
  // Sorttable v1 put rows with a class of "sortbottom" at the bottom (as
  // "total" rows, for example). This is B&R, since what you're supposed
  // to do is put them in a tfoot. So, if there are sortbottom rows,
  // for backwards compatibility, move them to tfoot (creating it if needed).
  sortbottomrows = [];
  for (var i=0; i<table.rows.length; i++) {
    if (table.rows[i].className.search(/\bsortbottom\b/) != -1) {
      sortbottomrows[sortbottomrows.length] = table.rows[i];
    }
  }
  if (sortbottomrows) {
    if (table.tFoot == null) {
      // table doesn't have a tfoot. Create one.
      tfo = document.createElement('tfoot');
      table.appendChild(tfo);
    }
    for (var i=0; i<sortbottomrows.length; i++) {
      tfo.appendChild(sortbottomrows[i]);
    }
    delete sortbottomrows;
  }
  
  // work through each column and calculate its type
  headrow = table.tHead.rows[0].cells;
  for (var i=0; i<headrow.length; i++) {
    // manually override the type with a sorttable_type attribute
    if (!headrow[i].className.match(/\bsorttable_nosort\b/)) { // skip this col
      mtch = headrow[i].className.match(/\bsorttable_([a-z0-9]+)\b/);
      if (mtch) { override = mtch[1]; }
	      if (mtch && typeof sorttable["sort_"+override] == 'function') {
	        headrow[i].sorttable_sortfunction = sorttable["sort_"+override];
	      } else {
	        headrow[i].sorttable_sortfunction = sorttable.guessType(table,i);
	      }
	      // make it clickable to sort
	      headrow[i].sorttable_columnindex = i;
	      headrow[i].sorttable_tbody = table.tBodies[0];
	      dean_addEvent(headrow[i],"click", function(e) {

        if (this.className.search(/\bsorttable_sorted\b/) != -1) {
          // if we're already sorted by this column, just 
          // reverse the table, which is quicker
          sorttable.reverse(this.sorttable_tbody);
          this.className = this.className.replace('sorttable_sorted',
                                                  'sorttable_sorted_reverse');
          this.removeChild(document.getElementById('sorttable_sortfwdind'));
          sortrevind = document.createElement('span');
          sortrevind.id = "sorttable_sortrevind";
          sortrevind.innerHTML = stIsIE ? '&nbsp<font face="webdings">5</font>' : '&nbsp;&#x25B4;';
          this.appendChild(sortrevind);
          return;
        }
        if (this.className.search(/\bsorttable_sorted_reverse\b/) != -1) {
          // if we're already sorted by this column in reverse, just 
          // re-reverse the table, which is quicker
          sorttable.reverse(this.sorttable_tbody);
          this.className = this.className.replace('sorttable_sorted_reverse',
                                                  'sorttable_sorted');
          this.removeChild(document.getElementById('sorttable_sortrevind'));
          sortfwdind = document.createElement('span');
          sortfwdind.id = "sorttable_sortfwdind";
          sortfwdind.innerHTML = stIsIE ? '&nbsp<font face="webdings">6</font>' : '&nbsp;&#x25BE;';
          this.appendChild(sortfwdind);
          return;
        }
        
        // remove sorttable_sorted classes
        theadrow = this.parentNode;
        forEach(theadrow.childNodes, function(cell) {
          if (cell.nodeType == 1) { // an element
            cell.className = cell.className.replace('sorttable_sorted_reverse','');
            cell.className = cell.className.replace('sorttable_sorted','');
          }
        });
        sortfwdind = document.getElementById('sorttable_sortfwdind');
        if (sortfwdind) { sortfwdind.parentNode.removeChild(sortfwdind); }
        sortrevind = document.getElementById('sorttable_sortrevind');
        if (sortrevind) { sortrevind.parentNode.removeChild(sortrevind); }
        
        this.className += ' sorttable_sorted';
        sortfwdind = document.createElement('span');
        sortfwdind.id = "sorttable_sortfwdind";
        sortfwdind.innerHTML = stIsIE ? '&nbsp<font face="webdings">6</font>' : '&nbsp;&#x25BE;';
        this.appendChild(sortfwdind);

	        // build an array to sort. This is a Schwartzian transform thing,
	        // i.e., we "decorate" each row with the actual sort key,
	        // sort based on the sort keys, and then put the rows back in order
	        // which is a lot faster because you only do getInnerText once per row
	        row_array = [];
	        col = this.sorttable_columnindex;
	        rows = this.sorttable_tbody.rows;
	        for (var j=0; j<rows.length; j++) {
	          row_array[row_array.length] = [sorttable.getInnerText(rows[j].cells[col]), rows[j]];
	        }
	        /* If you want a stable sort, uncomment the following line */
	        //sorttable.shaker_sort(row_array, this.sorttable_sortfunction);
	        /* and comment out this one */
	        row_array.sort(this.sorttable_sortfunction);
	        
	        tb = this.sorttable_tbody;
	        for (var j=0; j<row_array.length; j++) {
	          tb.appendChild(row_array[j][1]);
	        }
	        
	        delete row_array;
	      });
	    }
  }
},

guessType: function(table, column) {
  // guess the type of a column based on its first non-blank row
  sortfn = sorttable.sort_alpha;
  for (var i=0; i<table.tBodies[0].rows.length; i++) {
    text = sorttable.getInnerText(table.tBodies[0].rows[i].cells[column]);
    if (text != '') {
      if (text.match(/^-?[£$¤]?[\d,.]+%?$/)) {
        return sorttable.sort_numeric;
      }
      // check for a date: dd/mm/yyyy or dd/mm/yy 
      // can have / or . or - as separator
      // can be mm/dd as well
      possdate = text.match(sorttable.DATE_RE)
      if (possdate) {
        // looks like a date
        first = parseInt(possdate[1]);
        second = parseInt(possdate[2]);
        if (first > 12) {
          // definitely dd/mm
          return sorttable.sort_ddmm;
        } else if (second > 12) {
          return sorttable.sort_mmdd;
        } else {
          // looks like a date, but we can't tell which, so assume
          // that it's dd/mm (English imperialism!) and keep looking
          sortfn = sorttable.sort_ddmm;
        }
      }
    }
  }
  return sortfn;
},

getInnerText: function(node) {
  // gets the text we want to use for sorting for a cell.
  // strips leading and trailing whitespace.
  // this is *not* a generic getInnerText function; it's special to sorttable.
  // for example, you can override the cell text with a customkey attribute.
  // it also gets .value for <input> fields.
  
  hasInputs = (typeof node.getElementsByTagName == 'function') &&
               node.getElementsByTagName('input').length;
  
  if (node.getAttribute("sorttable_customkey") != null) {
    return node.getAttribute("sorttable_customkey");
  }
  else if (typeof node.textContent != 'undefined' && !hasInputs) {
    return node.textContent.replace(/^\s+|\s+$/g, '');
  }
  else if (typeof node.innerText != 'undefined' && !hasInputs) {
    return node.innerText.replace(/^\s+|\s+$/g, '');
  }
  else if (typeof node.text != 'undefined' && !hasInputs) {
    return node.text.replace(/^\s+|\s+$/g, '');
  }
  else {
    switch (node.nodeType) {
      case 3:
        if (node.nodeName.toLowerCase() == 'input') {
          return node.value.replace(/^\s+|\s+$/g, '');
        }
      case 4:
        return node.nodeValue.replace(/^\s+|\s+$/g, '');
        break;
      case 1:
      case 11:
        var innerText = '';
        for (var i = 0; i < node.childNodes.length; i++) {
          innerText += sorttable.getInnerText(node.childNodes[i]);
        }
        return innerText.replace(/^\s+|\s+$/g, '');
        break;
      default:
        return '';
    }
  }
},

reverse: function(tbody) {
  // reverse the rows in a tbody
  newrows = [];
  for (var i=0; i<tbody.rows.length; i++) {
    newrows[newrows.length] = tbody.rows[i];
  }
  for (var i=newrows.length-1; i>=0; i--) {
     tbody.appendChild(newrows[i]);
  }
  delete newrows;
},

/* sort functions
   each sort function takes two parameters, a and b
   you are comparing a[0] and b[0] */
sort_numeric: function(a,b) {
  aa = parseFloat(a[0].replace(/[^0-9.-]/g,''));
  if (isNaN(aa)) aa = 0;
  bb = parseFloat(b[0].replace(/[^0-9.-]/g,'')); 
  if (isNaN(bb)) bb = 0;
  return aa-bb;
},
sort_alpha: function(a,b) {
  if (a[0]==b[0]) return 0;
  if (a[0]<b[0]) return -1;
  return 1;
},
sort_ddmm: function(a,b) {
  mtch = a[0].match(sorttable.DATE_RE);
  y = mtch[3]; m = mtch[2]; d = mtch[1];
  if (m.length == 1) m = '0'+m;
  if (d.length == 1) d = '0'+d;
  dt1 = y+m+d;
  mtch = b[0].match(sorttable.DATE_RE);
  y = mtch[3]; m = mtch[2]; d = mtch[1];
  if (m.length == 1) m = '0'+m;
  if (d.length == 1) d = '0'+d;
  dt2 = y+m+d;
  if (dt1==dt2) return 0;
  if (dt1<dt2) return -1;
  return 1;
},
sort_mmdd: function(a,b) {
  mtch = a[0].match(sorttable.DATE_RE);
  y = mtch[3]; d = mtch[2]; m = mtch[1];
  if (m.length == 1) m = '0'+m;
  if (d.length == 1) d = '0'+d;
  dt1 = y+m+d;
  mtch = b[0].match(sorttable.DATE_RE);
  y = mtch[3]; d = mtch[2]; m = mtch[1];
  if (m.length == 1) m = '0'+m;
  if (d.length == 1) d = '0'+d;
  dt2 = y+m+d;
  if (dt1==dt2) return 0;
  if (dt1<dt2) return -1;
  return 1;
},

shaker_sort: function(list, comp_func) {
  // A stable sort function to allow multi-level sorting of data
  // see: http://en.wikipedia.org/wiki/Cocktail_sort
  // thanks to Joseph Nahmias
  var b = 0;
  var t = list.length - 1;
  var swap = true;

  while(swap) {
      swap = false;
      for(var i = b; i < t; ++i) {
          if ( comp_func(list[i], list[i+1]) > 0 ) {
              var q = list[i]; list[i] = list[i+1]; list[i+1] = q;
              swap = true;
          }
      } // for
      t--;

      if (!swap) break;

      for(var i = t; i > b; --i) {
          if ( comp_func(list[i], list[i-1]) < 0 ) {
              var q = list[i]; list[i] = list[i-1]; list[i-1] = q;
              swap = true;
          }
      } // for
      b++;

  } // while(swap)
}  
}

/* ******************************************************************
 Supporting functions: bundled here to avoid depending on a library
 ****************************************************************** */

//Dean Edwards/Matthias Miller/John Resig

/* for Mozilla/Opera9 */
if (document.addEventListener) {
  document.addEventListener("DOMContentLoaded", sorttable.init, false);
}

/* for Internet Explorer */
/*@cc_on @*/
/*@if (@_win32)
  document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
  var script = document.getElementById("__ie_onload");
  script.onreadystatechange = function() {
      if (this.readyState == "complete") {
          sorttable.init(); // call the onload handler
      }
  };
/*@end @*/

/* for Safari */
if (/WebKit/i.test(navigator.userAgent)) { // sniff
  var _timer = setInterval(function() {
      if (/loaded|complete/.test(document.readyState)) {
          sorttable.init(); // call the onload handler
      }
  }, 10);
}

/* for other browsers */
window.onload = sorttable.init;

//written by Dean Edwards, 2005
//with input from Tino Zijdel, Matthias Miller, Diego Perini

//http://dean.edwards.name/weblog/2005/10/add-event/

function dean_addEvent(element, type, handler) {
	if (element.addEventListener) {
		element.addEventListener(type, handler, false);
	} else {
		// assign each event handler a unique ID
		if (!handler.$$guid) handler.$$guid = dean_addEvent.guid++;
		// create a hash table of event types for the element
		if (!element.events) element.events = {};
		// create a hash table of event handlers for each element/event pair
		var handlers = element.events[type];
		if (!handlers) {
			handlers = element.events[type] = {};
			// store the existing event handler (if there is one)
			if (element["on" + type]) {
				handlers[0] = element["on" + type];
			}
		}
		// store the event handler in the hash table
		handlers[handler.$$guid] = handler;
		// assign a global event handler to do all the work
		element["on" + type] = handleEvent;
	}
};
//a counter used to create unique IDs
dean_addEvent.guid = 1;

function removeEvent(element, type, handler) {
	if (element.removeEventListener) {
		element.removeEventListener(type, handler, false);
	} else {
		// delete the event handler from the hash table
		if (element.events && element.events[type]) {
			delete element.events[type][handler.$$guid];
		}
	}
};

function handleEvent(event) {
	var returnValue = true;
	// grab the event object (IE uses a global event object)
	event = event || fixEvent(((this.ownerDocument || this.document || this).parentWindow || window).event);
	// get a reference to the hash table of event handlers
	var handlers = this.events[event.type];
	// execute each event handler
	for (var i in handlers) {
		this.$$handleEvent = handlers[i];
		if (this.$$handleEvent(event) === false) {
			returnValue = false;
		}
	}
	return returnValue;
};

function fixEvent(event) {
	// add W3C standard event methods
	event.preventDefault = fixEvent.preventDefault;
	event.stopPropagation = fixEvent.stopPropagation;
	return event;
};
fixEvent.preventDefault = function() {
	this.returnValue = false;
};
fixEvent.stopPropagation = function() {
this.cancelBubble = true;
}

//Dean's forEach: http://dean.edwards.name/base/forEach.js
/*
	forEach, version 1.0
	Copyright 2006, Dean Edwards
	License: http://www.opensource.org/licenses/mit-license.php
*/

//array-like enumeration
if (!Array.forEach) { // mozilla already supports this
	Array.forEach = function(array, block, context) {
		for (var i = 0; i < array.length; i++) {
			block.call(context, array[i], i, array);
		}
	};
}

//generic enumeration
Function.prototype.forEach = function(object, block, context) {
	for (var key in object) {
		if (typeof this.prototype[key] == "undefined") {
			block.call(context, object[key], key, object);
		}
	}
};

//character enumeration
String.forEach = function(string, block, context) {
	Array.forEach(string.split(""), function(chr, index) {
		block.call(context, chr, index, string);
	});
};

//globally resolve forEach enumeration
var forEach = function(object, block, context) {
	if (object) {
		var resolve = Object; // default
		if (object instanceof Function) {
			// functions have a "length" property
			resolve = Function;
		} else if (object.forEach instanceof Function) {
			// the object implements a custom forEach method so use that
			object.forEach(block, context);
			return;
		} else if (typeof object == "string") {
			// the object is a string
			resolve = String;
		} else if (typeof object.length == "number") {
			// the object is array-like
			resolve = Array;
		}
		resolve.forEach(object, block, context);
	}
};
