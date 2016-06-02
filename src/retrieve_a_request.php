<?php 
include_once("xml/header.php");
include_once("xml/navigation.php");
include_once("xml/side_nav.php");
?>
<div id="column-two">
<form method="post" name="myform" action="successful_retrieval.php">
<h2> Retrieve a request that was previously submitted to AVIA </h2>
<table id="formatted"><tr><td colspan="3"><font color="#993300"> A field with an asterisk (*) before it is a required field. </font></td></tr>
<tr>
	<td><font color="#993300">*Request Id</font></td>
	<td><input type="text" name="user_requestor_id" placeholder="Enter your AVIA-lite id" size="40"/></td>
</tr>
</table>
<br />
	<input type="submit" value="Retrieve" onClick="return validateInputs('retrieve')"/>
</form>
</div>

<?php
include_once("xml/footer.php");?>