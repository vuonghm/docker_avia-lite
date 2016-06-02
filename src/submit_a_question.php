<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
session_set_cookie_params("3600",'/');
ini_set('session.cookie_secure',1);
ini_set("session.cookie_httponly", 1);
ini_set('session.use_only_cookies',1);
session_start();
?>
<meta name="keywords" content="" />
<meta name="description" content="" />
<?php 
// Place all of your additional css and js here
?>
<link href="/css/styles.css" rel="stylesheet" type="text/css" />
<link href="/css/jqueryslidemenu.css" rel="stylesheet" type="text/css" />
<link href="/css/jquery-ui.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/javascripts.js"></script>
<script type="text/javascript" src="js/jQuery-UI/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jqueryslidemenu.js "></script>
<script type="text/javascript">
function checkBrowser()
{		
	if (/MSIE (\d+\.\d+);/.test(navigator.userAgent)){ //test for MSIE x.x;
		alert("Microsoft IE does not fully support all the features on this site.  Please use Mozilla firefox instead.");
	}
}
</script>
<!-- <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="0" /> -->
</head>
<div id="container">
    <h1><?php echo $title; ?></h1>

    <div id="body-container">
		<div id="logo-container" style="width:360px;min-height:82px">
		  <div class="logo-left"  style="height:82px;">
			  <a href="/" title="AVIA home page"><img src="images/AVIA.png" width="360px" height="82px" alt="AVIA logo" /></a>
			</div>
		</div>
  
    <div id="page-top-search">
    	
   </div>
<?php 

//This is your top navigation menu
include("xml/navigation.php");
//This is your side navigation menu
include("xml/side_nav.php");
//This is the content of your page
echo "<div id=\"column-two\">"
?>
Simple table to get some information from the user
<form name="upload_question">
<table>
<tr><td>Enter your name:</td><td> <input type="text" /></td></tr>
<tr><td>Enter your question:</td><td> <textarea >Enter question here</textarea></td></tr>
</table>
<input type="button" value="Submit" />
</form>


<?php
//Do not modify below this line!!!
echo "</div>";//End div tag to container
include("xml/footer.php");
?>
</div>

</body>
</html>




