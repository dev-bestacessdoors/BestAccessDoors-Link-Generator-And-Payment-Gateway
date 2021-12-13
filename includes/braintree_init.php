<?php
session_start();
require_once("../vendor/autoload.php");

if(isset($_COOKIE['storename'])) {
    $storename= $_COOKIE['storename'];
}

if(file_exists(__DIR__ . "/../.env")) {
    $dotenv = new Dotenv\Dotenv(__DIR__ . "/../");
    $dotenv->load();
}

/*** currently we are not using Braintree   */

// if ($storename == "Access_Doors_Canada") {
//   $gateway = new Braintree\Gateway([
//       'environment' => getenv('ADC_BT_ENVIRONMENT'),
//       'merchantId' => getenv('ADC_BT_MERCHANT_ID'),
//       'publicKey' => getenv('ADC_BT_PUBLIC_KEY'),
//       'privateKey' => getenv('ADC_BT_PRIVATE_KEY')
//   ]);
// }elseif ($storename == "Acudor_Access_Panels") {
//   $gateway = new Braintree\Gateway([
//       'environment' => getenv('AAP_BT_ENVIRONMENT'),
//       'merchantId' => getenv('AAP_BT_MERCHANT_ID'),
//       'publicKey' => getenv('AAP_BT_PUBLIC_KEY'),
//       'privateKey' => getenv('AAP_BT_PRIVATE_KEY')
//   ]);
// }elseif ($storename == "Access_Doors_And_Panels") {
//   $gateway = new Braintree\Gateway([
//       'environment' => getenv('ADP_BT_ENVIRONMENT'),
//       'merchantId' => getenv('ADP_BT_MERCHANT_ID'),
//       'publicKey' => getenv('ADP_BT_PUBLIC_KEY'),
//       'privateKey' => getenv('ADP_BT_PRIVATE_KEY')
//   ]);
// }elseif ($storename == "Best_Access_Doors") {
//   $gateway = new Braintree\Gateway([
//       'environment' => getenv('BAD_BT_ENVIRONMENT'),
//       'merchantId' => getenv('BAD_BT_MERCHANT_ID'),
//       'publicKey' => getenv('BAD_BT_PUBLIC_KEY'),
//       'privateKey' => getenv('BAD_BT_PRIVATE_KEY')
//   ]);
// }elseif ($storename == "Best_Access_Doors_Canada") {
//   $gateway = new Braintree\Gateway([
//       'environment' => getenv('BADC_BT_ENVIRONMENT'),
//       'merchantId' => getenv('BADC_BT_MERCHANT_ID'),
//       'publicKey' => getenv('BADC_BT_PUBLIC_KEY'),
//       'privateKey' => getenv('BADC_BT_PRIVATE_KEY')
//   ]);
// }elseif ($storename == "California_Access_Doors") {
//   $gateway = new Braintree\Gateway([
//       'environment' => getenv('CAD_BT_ENVIRONMENT'),
//       'merchantId' => getenv('CAD_BT_MERCHANT_ID'),
//       'publicKey' => getenv('CAD_BT_PUBLIC_KEY'),
//       'privateKey' => getenv('CAD_BT_PRIVATE_KEY')
//   ]);
// }elseif ($storename == "Max_Supply") {
//   $gateway = new Braintree\Gateway([
//       'environment' => getenv('MAX_BT_ENVIRONMENT'),
//       'merchantId' => getenv('MAX_BT_MERCHANT_ID'),
//       'publicKey' => getenv('MAX_BT_PUBLIC_KEY'),
//       'privateKey' => getenv('MAX_BT_PRIVATE_KEY')
//   ]);
// }elseif ($storename == "Public_Furniture") {
//   $gateway = new Braintree\Gateway([
//       'environment' => getenv('PF_BT_ENVIRONMENT'),
//       'merchantId' => getenv('PF_BT_MERCHANT_ID'),
//       'publicKey' => getenv('PF_BT_PUBLIC_KEY'),
//       'privateKey' => getenv('PF_BT_PRIVATE_KEY')
//   ]);
// }elseif ($storename == "Best_Roof_Hatches") {
//   $gateway = new Braintree\Gateway([
//       'environment' => getenv('PF_BT_ENVIRONMENT'),
//       'merchantId' => getenv('PF_BT_MERCHANT_ID'),
//       'publicKey' => getenv('PF_BT_PUBLIC_KEY'),
//       'privateKey' => getenv('PF_BT_PRIVATE_KEY')
//   ]);
// }else {
//   echo "Invalid Store Name";
// }

if ($storename == "Access_Doors_Canada") {
  $logourl = 'logo/adc.jpg';
  }elseif ($storename == "Acudor_Access_Panels") {
  $logourl = 'logo/aap.jpg';
  }elseif ($storename == "Access_Doors_And_Panels") {
  $logourl = 'logo/adap.jpg';
  }elseif ($storename == "Best_Access_Doors") {
  $logourl = 'logo/bad.jpg';
  }elseif ($storename == "Best_Access_Doors_Canada") {
  $logourl = 'logo/badc.jpg';
  }elseif ($storename == "California_Access_Doors") {
  $logourl = 'logo/cad.jpg';
  }elseif ($storename == "Max_Supply") {
  $logourl = 'logo/max.jpg';
  }elseif ($storename == "Public_Furniture") {
  $logourl = 'logo/pub.jpg';
  }elseif ($storename == "Best_Roof_Hatches") {
  $logourl = 'logo/brh.jpg';
  }
  else {
  $logourl = '<h5> Invalid Store Name. Please Contact Sales Person . . . </h5>';
  }

$baseUrl = stripslashes(dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = $baseUrl == '/' ? $baseUrl : $baseUrl . '/';
