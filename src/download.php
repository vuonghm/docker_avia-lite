<?php
if (isset($_GET['file'])){
	$file = htmlentities($_GET['file']);
	if (!preg_match("/\.\./",$file)){
       $dirname=dirname($file);
       $filename=basename($file);
       $upload_dir=$_SERVER['DOCUMENT_ROOT']. "/data";
       if (file_exists("$upload_dir/$file")) {
               chdir("$upload_dir/$dirname");
           header('Content-Description: File Transfer');
           header('Content-Type: application/octet-stream');
           header('Content-Disposition: attachment; filename='.$filename);
           header('Expires: 0');
           header('Cache-Control: must-revalidate');
           header('Pragma: public');
           header('Content-Length: ' . filesize($filename));
           readfile($filename);
           exit;
       }else{
               echo "$upload_dir/$file does not exist on server!<br />";
       }
   }
}
?>
