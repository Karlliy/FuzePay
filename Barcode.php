<?php

include "Barcode39.php"; 
$bc = new Barcode39($_SERVER['QUERY_STRING']); 

// set text size 
$bc->barcode_text_size = 5; 

// set barcode bar thickness (thick bars) 
$bc->barcode_bar_thick = 4; 

// set barcode bar thickness (thin bars) 
$bc->barcode_bar_thin = 2; 
$bc->draw();