<?php
  echo $_POST['highlighted_days'];
  echo "<br><br>";
  print_r($_POST); 
  $payamount = $_POST['highlighted_days'];
  session_start();
  $_SESSION["payamount"] = $payamount;
  
?>
