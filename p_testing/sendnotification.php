<?php

set_time_limit(0);

function open_email_notification($Quote_number, $sales_person)
{
  // echo "Processing";
  date_default_timezone_set('US/Eastern');
  $currenttime = date('h:i:s:u');
    $date = date("Y-m-d h:i:sa");
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://door-pay.com/p/mail360/sendMailNotification.php",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "{\n    \"Authtoken\": \"59AACEAFB2CE53271A6048D2D385D4C800587C0D047F8B07958934F710AE5D7891FB4EC28BB2FA684F638630E9EF76F7D19D22EE9A20EE892625DC2FCEDCD976\",\n    \"MailArray\": [\n        {\n            \"MailTo\": [\n                ".$sales_person."\n            ],\n            \"Subject\": \"Payment url opened by customer. Quote #".$Quote_number."\",\n            \"Message\": \"<b>".$Quote_number."</b> was open opened by the custmer at <b>".$date."</b>.\"\n        }\n    ]\n}",
      CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json",
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      // echo "cURL Error #:" . $err;
      return $err;
    } else {
      // echo $response;
      return $response;

    }
}
