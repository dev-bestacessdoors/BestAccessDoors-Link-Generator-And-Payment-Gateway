<?php

$today_Date = date("Y-m-d h:i:sa");

function log_message($message) {
    $today_Date = date("d-m-Y");
    error_log($message."\n", 3, 'Log/EmailNotification/emailNotification'.$today_Date.'.log');
}

function success_response($status) {
  if($status === "Success")
  {
    $data = array("Code"=>"200","Message"=>"Request Accepted Processing Data");
  }
  else if($status === "Failed")
  {
    $data = array("Code"=>"500","Message"=>"Invalid Data");
  }
  header('Content-Type: application/json');
  echo json_encode($data);
}

log_message("**********Function Execution - Starts @ $today_Date**********");

$json = json_decode(file_get_contents("php://input"),true);

$sha512_Auth_key = "59AACEAFB2CE53271A6048D2D385D4C800587C0D047F8B07958934F710AE5D7891FB4EC28BB2FA684F638630E9EF76F7D19D22EE9A20EE892625DC2FCEDCD976";

// fwrite($myfile2, json_encode($json));

$auth=$json['Authtoken'];
$json['Authtoken'] = "Kept Secret";

log_message("Request Body: ".json_encode($json));

if($auth === $sha512_Auth_key)
{
  success_response("Success");
  log_message("200: Authentication Success");
  require 'PHPMailer/class.phpmailer.php';
  $mail_array=$json['MailArray'];
  foreach($mail_array as $mail_data)
  {
    try {
    	$mail = new PHPMailer(true); //New instance, with exceptions enabled
    	$mail->IsSMTP();                           // tell the class to use SMTP
    	$mail->SMTPAuth   = true;                  // enable SMTP authentication
    	$mail->Port       = 587;                    // set the SMTP server port
    	$mail->SMTPSecure = 'tls';
    	$mail->Host       = "smtp.gmail.com"; // SMTP server
    	$mail->Username   = "programmer01best@gmail.com";     // SMTP server username
    	$mail->Password   = "V4zqKsGl1t2q";            // SMTP server password
    	$mail->From       = "programmer01best@gmail.com";
    	$mail->FromName   = "Best Access Doors";

    	foreach ($mail_data['MailTo'] as $address)
      {
        $mail->AddAddress($address);
      }
      foreach ($mail_data['BCC'] as $BCCaddress)
      {
        $mail->AddBCC($BCCaddress);
      }
    	$mail->AddBCC('prashanth@bizappln.com');
    	$mail->Subject = $mail_data['Subject'];
    	$mail->Body = $mail_data['Message'];
    	$mail->IsHTML(true);
      $mail->Send();
      $mail = null;
    	log_message('Notification Sent Successfully: '.json_encode($mail_data));
    }
    catch (phpmailerException $e)
    {
    	log_message("PHPMailer Error: ".$e->errorMessage());
    }
  }
}
else {
  success_response("Failed");
  $error = "500: Unauthorized Request";
  log_message($error);
}

$today_Date = date("Y-m-d h:i:sa");
log_message("**********Function Execution - Ending @ $today_Date**********");

?>
