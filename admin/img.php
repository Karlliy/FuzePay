<?php
Header("Content-type:image/PNG"); 
session_start(); 
$auth_num=""; 
$Character = array ("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
$_SESSION['auth_num']; 
$im=imagecreate(108,41); 
srand((double)microtime()*1000000); 
$black = ImageColorAllocate($im, 0,0,0); 
$white = ImageColorAllocate($im, 255,255,255); 
$gray = ImageColorAllocate($im, 255,255,255); 
imagefill($im,0,0,$gray); 
//while(($auth_num=rand()%100000)<10000);

//將四位整數驗證碼繪入圖片 
//imagestring($im, 5, 10, 3, $auth_num,$black); 

/*for($i=0;$i<60;$i++) //?入干擾象素 
{ 
	$randcolor = ImageColorallocate($im,rand(100,255),rand(100,255),rand(100,255));
	//imagesetpixel($im, rand()%75 , rand()%30 , $randcolor); 
	$x = rand()%108;
	$y = rand()%41;
	for($j=1; $j<25; $j++){
		imagearc($im, $x, $y, $j, $j, 0, 360, $randcolor);
	}

} */

//imagesetthickness($im, 10);
//imageline ($im, 0, 20, 108, 15, imagecolorallocatealpha($im, 60, 60, 60, 7));
//$transparency = imagecolorallocatealpha($im, 0, 0, 0, 127);
//imagefill($im, 0, 0, $transparency);

for ($i = 0; $i < 5; $i++) {
	$RandChar = "";
	$RandChar = $Character[rand(0, 9)];
	srand(make_seed());
	if (rand() % 2 == 0 ) {
		srand(make_seed());
		ImageTTFText ($im, rand(15, 24), rand(10, 35)%35, (10 + 20*$i), 30, $black, dirname(__FILE__)."\\arial.ttf", $RandChar); 
	}else {
		srand(make_seed());
		ImageTTFText ($im, rand(15, 24), (rand(10, 35)%35 * -1) , (10 + 20*$i), 30, $black, dirname(__FILE__)."\\arial.ttf", $RandChar); 
	}
	$auth_num .= $RandChar;
}

//imagesetthickness($im, 2);
//imageline($im, 0, 0, 108, 41, imagecolorallocate($im, 0x00, 0x00, 0x00));

$_SESSION['auth_num'] = $auth_num;
imagePNG($im); 
ImageDestroy($im);  
function make_seed()
{
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
}
?>