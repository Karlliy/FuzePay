<?php 
echo "<span id=code_img><img id='LoginVerify' src='../admin/img.php?v=". rand(0, 999999)."' align=\"absmiddle\"></span>
     <span onclick=\"$('#LoginVerify').src='<img src=../admin/img.php?v=". rand(0, 999999)." align=absmiddle>'\"
     style=\"cursor : hand;font-size : 12pt ;color :#000;\"><br />看不清楚，按此 再產生驗證碼一次</span>";
?>