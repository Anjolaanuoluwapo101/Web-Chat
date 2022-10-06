<?php
// the message
//$msg = "First line of text\nSecond line of text";

// use wordwrap() if lines are longer than 70 characters
//$msg = wordwrap($msg,70);
$headers = "Reply-To: Web Chat <anjolaakinsoyinu@gmail.com>\r\n"; 
  $headers .= "Return-Path: Web Chat <anjolaakinsoyinu@gmail.com>\r\n"; 
  $headers .= "From: Web Chat <anjolaakinsoyinu@gmail.com>\r\n";  
  $headers .= "Organization: Web Chat Organization\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
  $headers .= "X-Priority: 3\r\n";
  $headers .= "X-Mailer: PHP". phpversion() ."\r\n" ;

$msg = '
<html>
<body>
  <a href="www.google.com"> Click here </a>
</body>
</html>

';
// send email
mail("anjolaakinsoyinu@gmail.com","My subject",$msg,$headers);
?>