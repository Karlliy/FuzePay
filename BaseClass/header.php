<?php 
ini_set('SHORT_OPEN_TAG',"On"); 
ini_set('display_errors',"On"); 
ini_set('error_reporting',E_ALL & ~E_NOTICE);
ini_set('post_max_size',"256M");
ini_set('memory_limit',"256M");
ini_set('max_execution_time',"1200");
ini_set('max_input_time',"1200");
ini_set('upload_max_filesize',"200M");
set_time_limit( 1200 );

header('Content-Type: text/html; charset=utf-8');
?>