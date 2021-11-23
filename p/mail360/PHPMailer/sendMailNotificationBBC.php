<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$today_Date = date("Y-m-d h:i:sa");

function log_message($message) {
    $today_Date = date("d-m-Y");
    error_log($message."\n", 3, 'logs/emailNotification'.$today_Date.'.log');
}

function success_response($status) {
  if($status === "Success")
  {
    $data = array("Code"=>"200","Message"=>"Request Accepted Processing Data I am AWS1");
  }
  else if($status === "Failed")
  {
    $data = array("Code"=>"500","Message"=>"Invalid Data");
    $mail->Debugoutput='error_log'; 
  }else{
      $data = array("Code"=>"200","Message"=>$status);
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
   
  log_message("200: Authentication Success");
//   require 'src/PHPMailer.php';  

 
require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';



  $mail_array=$json['MailArray'];
  foreach($mail_array as $mail_data)
  {
      echo "\nInsed foreach ";
    try {
        echo "\nInsed try ";
    	$mail = new PHPMailer(true); //New instance, with exceptions enabled 
    	$mail->isSMTP();                           // tell the class to use SMTP
    	$mail->SMTPAuth   = true;                  // enable SMTP authentication
    	//$mail->AuthType = 'LOGIN';
    	$mail->Port       = 587;                    // set the SMTP server port
    	$mail->SMTPSecure = 'tls';
    	$mail->SMTPOptions = array(
        'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
        )
        );
        $mail->SMTPDebug = 6;

    	$mail->Host       = "smtp.office365.com"; // SMTP server
    	$mail->Username   = "hello@bestaccessdoors.com";     // SMTP server username
    	$mail->Password   = "Qux07367";            // SMTP server password
    	$mail->From       = "hello@bestaccessdoors.com";
    	$mail->FromName   = "Best Access Doors"; 
    	foreach ($mail_data['MailTo'] as $address)
      {
        $mail->AddAddress($address);
      }
      foreach ($mail_data['BCC'] as $BCCaddress)
      {
        $mail->AddBCC($BCCaddress);
      }
    	$mail->AddBCC('tharmendheran@bizappln.com');
    	$mail->Subject = $mail_data['Subject'];
    	$mail->Body = $mail_data['Message'];
    	 
    	$mail->IsHTML(true);
      $mail->Send();
      $mail = null;
      echo "\nAfter send mail";
    	log_message('Notification Sent Successfully: '.json_encode($mail_data));
    	success_response("Success".json_encode($mail_data));
    }
    catch (phpmailerException $e)
    { 
    	log_message("PHPMailer Error: ".$e->errorMessage());
    	 
    	 success_response("PHPMailer Error: ".$e->errorMessage());
    }catch (Exception $e) { 
        echo $e->getMessage(); //Boring error messages from anything else!
        success_response("Exception Error: ".$e->errorMessage());
        }catch (Error $e) { 
        echo $e->getMessage(); //Boring error messages from anything else!
        success_response("eee Error: ".$e->errorMessage());
        }
  }
  
  echo "After foreach";
}
else {
     echo "Auth else";
 
  $error = "500: Unauthorized Request";
  log_message($error);
}

$today_Date = date("Y-m-d h:i:sa");
log_message("**********Function Execution - Ending @ $today_Date**********");

 ini_set('display_errors', '1');
?>
