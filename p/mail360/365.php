<?php
require_once('/usr/share/php/Mail.php');


$from = "John Smith <hello@bestaccessdoors.com>";
$to = "Nancy Smith <anthony@bestaccessdoors.com>";
$bcc = '';
$subject = "Hi!";
$body = "Hi,\n\nLooks like it worked.";

$host = 'smtp.office365.com';
$port = '587';
$username = 'hello@bestaccessdoors.com'; ##e.g. test@yourdomain.com you do not need on.microsoft.com or anything here. Use your real email address you use for authentication.
$password = 'Qux07367';

$headers = array(
 'Port'          => $port,
 'From'          => $from,
 'To'            => $to,
 'Subject'       => $subject,
'Content-Type'  => 'text/html; charset=UTF-8'
);

$recipients = $to.", ".$bcc;

$smtp = Mail::factory('smtp',
 array ('host' => $host,
 'auth' => true,
 'username' => $username,
 'password' => $password));

$mail = $smtp->send($recipients, $headers, $body);

echo "test";

if (PEAR::isError($mail)) {
   echo("<p>" . $mail->getMessage() . "</p>");
} else {
   echo("<p>Message successfully sent!</p>");
}
?>