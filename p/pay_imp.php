<?php
date_default_timezone_set('Asia/Kolkata');
$today_Date = date("Y-m-d h:i:sa");
$start = "\n\n Started Execution @ $today_Date ";

ob_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
if (isset($_GET['h'])) {
  $quotenumberhash = $_GET['h'];
  $quotenumber = base64_decode($quotenumberhash);
}
if (isset($_GET['s'])) {
  $storename =  $_GET['s'];
}

if (isset($_GET['q'])) {
  $quotenumber = $_GET['q'];
  error_log($start . "\n\n pay.com page starts for -" . $quotenumber . "------------", 3, "logs/pay/pay-log" . date("d-m-Y") . ".log");
}

function curl_get_contents($url)
{
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}
function curl($url, $method, $body, $header)
{
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => $header,
  ));

  $response = curl_exec($curl);
  $err = curl_error($curl);
  curl_close($curl);

  if ($err) {
    echo "<br> curl error : " . $err;
    return array('error' => $err);
  }
  return json_decode($response, true);
}

function generatetoken()
{
  return curl_get_contents("https://1.door-pay.com/zoho_auth.php?platform=all&authbyT=93181c2a53fd65c1d7da3a86554dd44c64a4150c03e25634f15306626cf973c9");
}

$zoho_auth = json_decode(generatetoken(), true);
function getcreatordata($creatorurl)
{
  $zoho_auth = json_decode(generatetoken(), true); 
  $json = curl($creatorurl, "GET", "", $zoho_auth['creator']);
  return $json;
}

$creatorbaseurl = 'https://creator.zoho.com/api/v2/zoho_zoho1502/quotes/';
$paymentformrecordid = null;
if ($quotenumber != "") {
  $customquoteurl = $creatorbaseurl . "report/All_Custom_Quote_Payments?Quoteno=" . urlencode($quotenumber) . "&raw=true&Is_Active=true";
  $json = getcreatordata($customquoteurl); 
  if ($json['code'] == 3000) {
    $finalquote = $json[0];
    $storename = $finalquote['Stores']['display_value'];
    if (isset($finalquote['Generate_Payment_Link_Id_String'])) {
      $paymentformrecordid =  $finalquote['Generate_Payment_Link_Id_String'];
    }
  } else {
    $Allpaymentlinkurl = $creatorbaseurl . "report/All_Payment_Links?Quoteno=" . urlencode($quotenumber) . "&raw=true;";
    $json = getcreatordata($Allpaymentlinkurl);
    // echo "<br>".$Allpaymentlinkurl.json_encode($json);
    if ($json['code'] == 3000) {
      $finalquote = $json['data'][0];
      $storename = $finalquote['Stores']['display_value'];
      $sales_person = $finalquote['Employee_Email'];
      $paymentformrecordid = $finalquote['ID'];
    } else {
      error();
    }
  }
  error_log($start . "\n\n pay.php - quotenumber is not null " . $quotenumber . "------------", 3, "logs/pay/pay-log" . date("d-m-Y") . ".log");
}
unset($_COOKIE['storename']);
setcookie('storename', $storename, time() + 3600);
require_once("../includes/braintree_init.php");
require_once('config.php');
include 'sendnotification.php';
// $controllerurl = $creatorbaseurl."report/On_Off_Controller_Report?raw=true";
// $fetchonoffctl = getcreatordata($controllerurl);
// if ($fetchonoffctl['code'] == 3000) {
//   $controller = $json['data'][0];
//   $mail_controll = json_decode($controller['Dont_Send_Payment_link_Open_Notification']);
// 	$sales_person = "\"".$sales_person."\",\"payments@bestaccessdoors.com\"";
// 	if ($mail_controll == true) {
// 	open_email_notification($quotenumber, $sales_person);
//   }
// }

$province = file_get_contents("Province.json");
if ($finalquote != "") {
  error_log($start . "\n\n pay.php - quote is not null " . json_encode($finalquote) . "------------\n", 3, "logs/pay/pay-log" . date("d-m-Y") . ".log");
  /* function templating the GET requests sent through this generator */
  if ($finalquote['Payment_Transaction_No'] != "" && $finalquote['Transaction_Status'] == "submitted_for_settlement" || $finalquote['Transaction_Status'] == "settled" || $finalquote['Transaction_Status'] == "settling" || $finalquote['Transaction_Status'] == "succeeded") {
    error_log($start . "\n\n pay.php - paymenttransacNo & Transaction_Status is not null ------------\n", 3, "logs/pay/pay-log" . date("d-m-Y") . ".log");
?>
    <html lang="en">

    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link href="css/bootstrap.min.css" type="text/css" rel="stylesheet" />
      <title>Quote Payment Status</title>
    </head>

    <body>
      <div class="container" align="center">
        <img src="<?php echo $logourl; ?>" alt="Logo" style="width: 100%;">
      </div><br>
      <div class="container" style="margin-top: 0%;">
        <!-- <div class="container" style="margin-top: -16%;"><br>
          <h1 class="text-center">You have Already Initiated the Payment</h1> -->
        <!-- <div class="alert alert-warning alert-dismissible fade show" role="alert" align="center">
          You have already initiate payment process
          </div> -->
        <div class="jumbotron" style="padding-bottom: 30px;">
          <div class="row" style="margin-top: -45px;">
            <div class="column" style="width:100%;">
              <center>
                <h4>This order has been paid on<h4><b>
                      <h2><?php echo $finalquote['Added_Time']; ?></h2>
                    </b>
              </center>
              <!-- <center>Heya thanks! <b><?php echo $finalquote['First_Name'] . " " . $finalquote['Last_Name']; ?> </b></center> -->
            </div>
          </div>
        </div>
      </div>
      <br>
    </body>

    </html>
    <style>
      h1 {
        margin: 2em 0;
      }

      * {
        box-sizing: border-box;
      }

      /* Create two equal columns that floats next to each other */
      .column {
        float: left;
        width: 33%;
        padding: 10px;

        .row:after {
          content: "";
          display: table;
          clear: both;
        }
      }

      @media screen and (max-width: 600px) {
        .column {
          width: 100%;
        }
      }
    </style>

  <?php
  } else {
    unset($_COOKIE['storename']);
    setcookie('storename', $storename, time() + 3600);
    $Storeurl = "https://creator.zoho.com/api/v2/zoho_zoho1502/quotes/report/All_Stores?Store_Name=" . $storename . "&raw=true";
    $Storedataresponse = getcreatordata($Storeurl);
    foreach ($Storedataresponse['data'] as $item) {
      $Storedetails = $item;
    }

    //ST - gateway
    if ($Storedetails['Payment_Gateway1'] == 'Stripe' && $Storedetails['Payment_gateway_Mode'] == 'Test Mode') {
      $store_publishedKey = $Storedetails['Published_Key_Test'];
      $store_secretkey = $Storedetails['Secret_Key_Test'];
      error_log($start . "\n\n pay.php - stripe -test mode ------------\n", 3, "logs/pay/pay-log" . date("d-m-Y") . ".log");
    } elseif ($Storedetails['Payment_Gateway1'] == 'Stripe' && $Storedetails['Payment_gateway_Mode'] == 'Live Mode') {
      $store_publishedKey = $Storedetails['Published_Key_Live'];
      $store_secretKey = $Storedetails['Secret_Key_Live'];
      error_log($start . "\n\n pay.php - stripe -live mode ------------\n", 3, "logs/pay/pay-log" . date("d-m-Y") . ".log");
    }

    $store_payment_gateway = $Storedetails['Payment_Gateway1'];
    $store_paymentMode = $Storedetails['Payment_gateway_Mode'];
    $currency = $Storedetails['Store_Currency'];
    $StoreWebsite = $Storedetails['WebsiteText'];
    $currency = $finalquote['Currency'];
    $quoteamount = $finalquote['Quote_Amount'];

    if ($finalquote['No_Additional_Shipping'] == true) {
      $add1 = "checked";
      $add2 = "";
      $add3 = "";
      $add4 = "";
      $add5 = "";
    } else {
      if ($finalquote['Additional_Shipping_Charge'] != '[]') {
        $string = str_replace(array('[', ']'), '', $finalquote['Additional_Shipping_Charge']);
        $additionals = explode(', ', $string);
        // print_r($additionals);
        foreach ($additionals as $additional) {
          // echo "\n<br>additionals".json_encode($additional);
          if ($additional == "Call Before Delivery + $40") {
            $add2 = "checked";
            $add1 = "";
          } else if ($additional == "Construction Site + $80") {
            $add3 = "checked";
            $add1 = "";
          } else if ($additional == "Lift Gate + $100") {
            $add4 = "checked";
            $add1 = "";
          } else if ($additional == "Delivery Appointment With 4 Hour Window + $150") {
            $add5 = "checked";
            $add1 = "";
          }
        }
      }
    }

    // Customer billing details
    $customer_firstname = $finalquote['First_Name'];
    $customer_lastname  = $finalquote['Last_Name'];
    $customer_email     = $finalquote['Email'];
    $phone = $finalquote['Phone_Number'];
    $company = $finalquote['Company'];

    //Billing address
    $Billaddress1 = $finalquote['Street1'];
    $Billaddress2 = $finalquote['Street2'];
    $Billcity     = $finalquote['Town_City'];
    $Billstate    = $finalquote['State_Province'];
    $Billpostcode = $finalquote['ZipCode'];
    $Billcountry  = $finalquote['Country'];

    //Shipping address
    $ship_firstname = $finalquote['Ship_Firstname'];
    $ship_lastname  = $finalquote['Ship_Lastname'];
    $ship_email     = $finalquote['Ship_Email'];
    $ship_phone = $finalquote['Ship_Phone_Number'];
    $shipcompany = $finalquote['Ship_Company'];
    $Shipaddress1 = $finalquote['Ship_Street1'];
    $Shipaddress2 = $finalquote['Ship_Street2'];
    $Shipcity     = $finalquote['Ship_Town_City'];
    $Shipstate    = $finalquote['Ship_State_Province'];
    $Shippostcode = $finalquote['Ship_Zipcode'];
    $Shipcountry  = $finalquote['Ship_Country'];
    $Shipnotes = $finalquote['Ship_Notes'];

    // echo $currency;
    if ($currency == "USD") {
      $ShipTocountry = "US";
      $totaltxt = "Grand Total (USD)";
    } else if ($currency == "CAD") {
      $ShipTocountry = "CA";
      $totaltxt = "Grand Total (CAD)";
    }
    $Tax = $finalquote['Tax_CAD'];
    if ($Tax != 0.00) {
      $Tax = "$ " . number_format((float)$Tax, 2);
      $Taxtxt = "Tax";
    } else {
      $Tax = "";
      $Taxtxt = "";
    }
  ?>

    <html lang="en">

    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?php if (isset($storename)) {
                echo preg_replace("/[^a-zA-Z]/", " ", $storename) . " - ";
              } ?> Checkout</title>
      <link rel="stylesheet" href="assets/css/bootstrap.min.css">
      <!-- icofont -->
      <link rel="stylesheet" href="assets/css/fontawesome.min.css">
      <!-- select 2  -->
      <link rel="stylesheet" href="assets/css/select2.min.css">
      <!-- Owl Carousel -->
      <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
      <!-- magnific popup -->
      <link rel="stylesheet" href="assets/css/magnific-popup.css">
      <!-- flaticon -->
      <link rel="stylesheet" href="assets/css/flaticon.css">
      <!-- stylesheet -->
      <link rel="stylesheet" href="assets/css/style.css">
      <!-- responsive -->
      <link rel="stylesheet" href="assets/css/responsive.css">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
      <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBSpDZgUpeGDpYRnwSKropJdPRGZtbYUxI&libraries=places"></script>
    </head>
    <style>
      .quotebox {
        font-size: 20px;
        text-align: center;
        width: 300px;
        font-weight: 400;
        border: 2px solid #bcbcbc;
        background-color: #f1f1f1;
        border-radius: 2px;
        padding: 5px;
        font-weight: 500;
      }
    </style>

    <body onload="initialize()">
      <?php require_once("../includes/header.php"); ?>
      <div class="support-bar-two bg-white home-6" style="padding-top: 0px;padding-bottom: 0px;">
        <div class="container">
          <div class="row">
            <img src="<?php echo $logourl; ?>" alt="Logo" style="width: 100%;">
          </div>
        </div>
      </div>
      <hr style="margin-top: 0px;">
      <!-- support bar area two end -->

      <!-- checkout page content area start -->
      <div class="checkout-page-content-area" style="padding-top: 0px;">
        <div class="container">
          <form method="post" name="myform" id="payment-form">
            <div class="row">
              <div class="col-lg-7">
                <div class="left-content-area">
                  <h3 class="title"><i class="fas fa-lock" style=" color: gold; font-size: 18px; "></i> Shipping Details</h3>
                  <div id="shipping" style=" margin-top: -5%; ">
                    <div class="row">
                      <div class="col-lg-12 form-element">
                      </div>
                      <div class="col-lg-6">
                        <div class="left-content-area">
                          <div class="form-element">
                            <label>Email Address<span class="base-color">*</span></label>
                            <input type="email" tabindex="1" id="S_email" name="S_email" class="input-field" onblur="validateEmail(this);" placeholder="Email address..." value="<?php echo $ship_email; ?>" required />
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>First Name <span class="base-color">*</span></label>
                            <input type="text" tabindex="3" id="S_firstname" name="S_firstname" class="input-field" placeholder="First name..." value="<?php echo $ship_firstname; ?>" required />
                            <div class="input-validation" disabled></div>
                          </div>
                          <div class="form-element">
                            <label>Street Address <span class="base-color">*</span></label>
                            <input type="text" tabindex="5" id="Shipaddress1" name="Shipaddress1" class="input-field street_number autocomplete" onblur="fieldupdate()" placeholder="Street address..." value="<?php echo $Shipaddress1; ?>" required />
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label class="blank">Street Address 2</label>
                            <input type="text" tabindex="7" id="Shipaddress2" name="Shipaddress2" class="input-field" placeholder="Apartment, suite, unit, building, floor, etc." value="<?php echo $Shipaddress2; ?>" required />
                            <span id="route" class="route"> </span>
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>Town/City <span class="base-color">*</span></label>
                            <input type="text" tabindex="9" id="Shipcity" name="Shipcity" class="input-field locality" onchange="fetchavatax('Shippostcode')" placeholder="Enter town/city..." value="<?php echo $Shipcity; ?>" required />                                                        
                            <div class="input-validation" id="Shipcity-valid"></div>
                            <div id="Shipcity_validate"> </div>
                            <!-- <select tabindex="8" id="Shipcity_validate" class="input-field" placeholder="Enter state/province..."  onchange="updatezipcode(this.options[this.selectedIndex].value, 'Shipcity')" style="display:none">                         
                            </select> -->
                            <!-- <br><div id="Shipcity_validate" style="color:red;"> </div> -->
                          </div>
                          <div class="form-element">
                            <label>ZipCode <span class="base-color">*</span></label>
                            <input type="text" tabindex="11" id="Shippostcode" name="Shippostcode" onchange="fetchavatax('Shippostcode')" class="input-field postal_code" placeholder="Zipcode..." value="<?php echo $Shippostcode; ?>" required />
                            <div class="input-validation"></div>
                            <br><div id="Shippostcode_validate" style="color:red;"> </div>
                          </div>

                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="left-content-area">
                          <div class="form-element">
                            <label>Company Name</label>
                            <input type="text" tabindex="2" id="Shipcompany" name="Shipcompany" class="input-field" value="<?php echo $shipcompany; ?>" placeholder="Company name..." required />
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>Last Name <span class="base-color">*</span></label>
                            <input type="text" tabindex="4" id="S_lastname" name="S_lastname" class="input-field" placeholder="Last name..." value="<?php echo $ship_lastname; ?>" required />
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>Phone Number <span class="base-color">*</span></label>
                            <input type="text" tabindex="6" id="S_phonenumber" name="S_phonenumber" minlength="7" maxlength="10" class="input-field" onfocusout="phonevalidate(event);" placeholder="Phone number..." value="<?php echo $ship_phone; ?>" required />
                            <div class="input-validation"></div>
                          </div>

                          <div class="form-element">
                            <label> State / Province <span class="base-color">*</span></label>
                            <select tabindex="8" id="Shipstate" name="Shipstate" onchange="fetch_province(this.options[this.selectedIndex].value, 'Shipcountry')" class="input-field administrative_area_level_1" placeholder="Enter state/province..." value="<?php echo $Shipstate; ?>" required />
                            </select>
                            <div class="input-validation"></div>
                          </div>

                          <div class="form-element">
                            <label> Country <span class="base-color">*</span></label>
                            <select tabindex="10" id="Shipcountry" name="Shipcountry" onchange="show_province(this.options[this.selectedIndex].value,'Shipstate','')" class="input-field country" value="<?php echo $Shipcountry; ?>" required />
                            <option>Choose Country</option>
                            <option value="Canada">Canada</option>
                            <option value="United States">United States</option>
                            </select>
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>Order Notes</label>
                            <textarea tabindex="12" style="height: 44px;padding:10px 30px" class="input-field" id="notes" name="notes" placeholder="" required><?php echo $Shipnotes; ?></textarea>
                            <div class="input-validation"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <h3 class="title"><i class="fas fa-lock" style=" color: gold; font-size: 18px; "></i> Billing Details</h3>
                  <div class="checkbox-element">
                    <div class="checkbox-wrapper">
                      <label class="checkbox-inner">
                        <input type="checkbox" id="samebilling" tabindex="13" onclick="SetBilling(this.checked)">
                        <span class="checkmark"></span>
                        <span id="Billingtext">Unselect to change Billing Address</span>
                      </label>
                    </div>
                  </div>
                  <div id="billing" style="display:none; margin-top : -5%;">
                    <div class="row">
                      <div class="col-lg-12 form-element">
                      </div>
                      <div class="col-lg-6">
                        <div class="left-content-area">
                          <div class="form-element">
                            <label>Email Address<span class="base-color">*</span></label>
                            <input type="email" tabindex="14" id="c_email" name="c_email" class="input-field" onblur="validateEmail(this);" placeholder="Email address..." value="<?php echo $customer_email; ?>" required />
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>First Name <span class="base-color">*</span></label>
                            <input type="text" tabindex="16" id="c_firstname" name="c_firstname" class="input-field" placeholder="First name..." value="<?php echo $customer_firstname; ?>" required />
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>Street Address <span class="base-color">*</span></label>
                            <input type="text" tabindex="18" id="address1" name="address1" class="input-field street_number autocomplete" value="<?php echo $Billaddress1; ?>" placeholder="Street address..." required />
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label class="blank"> Street Address 2</label>
                            <input type="text" tabindex="20" id="address2" name="address2" class="input-field" placeholder="Apartment, suite, unit, building, floor, etc." value="<?php echo $Billaddress2; ?>" required />
                            <span id="route" class="route"> </span>
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>Town/City <span class="base-color">*</span></label>
                            <input type="text" tabindex="22" id="city" name="city" class="input-field locality" placeholder="Enter town/city..." value="<?php echo $Billcity; ?>" required />
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>ZipCode <span class="base-color">*</span></label>
                            <input type="text" tabindex="24" id="postcode" name="postcode" class="input-field postal_code" placeholder="Zipcode..." value="<?php echo $Billpostcode; ?>" required />
                            <div class="input-validation"></div>                            
                          </div>

                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="left-content-area">
                          <div class="form-element">
                            <label>Company Name</label>
                            <input type="text" tabindex="15" id="company" name="company" class="input-field " value="<?php echo $company; ?>" placeholder="Company name..." required />
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>Last Name <span class="base-color">*</span></label>
                            <input type="text" tabindex="17" id="c_lastname" name="c_lastname" class="input-field" placeholder="Last name..." value="<?php echo $customer_lastname; ?>" required />
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label>Phone Number <span class="base-color">*</span></label>
                            <input type="text" tabindex="19" id="c_phonenumber" name="c_phonenumber" class="input-field" onfocusout="phonevalidate(event)" placeholder="Phone number..." value="<?php echo $phone; ?>" required />
                            <div class="input-validation"></div>
                          </div>

                          <div class="form-element">
                            <label> State / Province <span class="base-color">*</span></label>
                            <select tabindex="21" id="state" name="state" onchange="fetch_province(this.options[this.selectedIndex].value, 'country')" class="input-field administrative_area_level_1" placeholder="Enter state/province..." value="<?php echo $Billstate; ?>" required />
                            </select>
                            <div class="input-validation"></div>
                          </div>
                          <div class="form-element">
                            <label> Country <span class="base-color">*</span></label>
                            <select tabindex="23" id="country" name="country" onchange="show_province(this.options[this.selectedIndex].value,'state','')" class="input-field country" value="<?php echo $Billcountry; ?>" required />
                            <option>Choose Country</option>
                            <option value="Canada">Canada</option>
                            <option value="United States">United States</option>
                            </select>
                            <div class="input-validation"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- <div class="col-lg-1"></div> -->
              <div class="col-lg-5">
                <div class="right-content-area" id="quoteno" style="border: 1px solid #ebebeb;box-shadow: 0 1px 4px rgba(95,95,95,0.14);border-radius: 4px;padding: 15px;">
                  <h3 class="title" style="font-weight: 100;text-align: center;">Order Summary <span id="quotenotxt" style="display: none;"></span></h3>
                  <!-- <div align="center"><input tabindex="25" style="font-size: 20px; text-align:center; width: 300px; font-weight: 400; border: 2px solid #bcbcbc; <? echo (isset($quotenumber) ? 'background-color: #f1f1f1;' : ''); ?>" type="text" value="<?php echo (isset($quotenumber) ? $quotenumber : ''); ?>" <? echo (isset($quotenumber) ? "disabled" : ""); ?> class="form-control-custom" placeholder="Enter Quote #" id="quote"></div> -->
                  <div align="center"><input tabindex="25" class="quotebox form-control-custom" type="text" value="<?php echo $quotenumber; ?>" placeholder="Enter Quote #" disabled id="quote"></div>

                  <hr>
                  <ul class="order-list">
                    <li style="padding-bottom: 0px;">
                      <div class="single-order-list heading">
                        Authorized Amount To Charge <span class="right">Cost</span>
                      </div>
                    </li>
                    <li class="name" style="padding: 15px 0 0 0;">
                      <div class="single-order-list" style="color: black;">
                        <div class="row" style="margin-bottom: 2%;">
                          <div class="col-lg-6" style="padding-bottom: 5px;"><label class="form-element-custom">Total Quotation Cost <label></div>

                          <div class="col-lg-6" align="right">
                            <div class="input-icon">
                              <!-- <input type="text" class="form-control" tabindex="26" id="procost" type="text" onblur="appenddecimal()" pattern="\d{1,6}(\.\d{0,4})?" value="<?php echo (isset($quoteamount) ? number_format((float)$quoteamount, 2, '.', '') : ''); ?>" style="text-align: right; '<? echo (isset($quoteamount) ? "background-color: #f1f1f1;" : ""); ?>'" <? echo (isset($quoteamount) ? "disabled" : ""); ?> placeholder="0.00" onpaste="return false;" onDrag="return false" onDrop="return false" autocomplete=off> -->
                              <input type="text" class="form-control" tabindex="26" id="procost" type="text" value="<?php echo (isset($quoteamount) ? number_format((float)$quoteamount, 2, '.', '') : ''); ?>" style="text-align: right;" disabled placeholder="0.00" onpaste="return false;" onDrag="return false" onDrop="return false" autocomplete=off>
                              <i style="position: absolute;display: block;transform: translate(0, -50%);top: 50%;pointer-events: none;width: 25px;text-align: center;font-style: normal;">$</i>
                            </div>
                          </div>
                        </div>

                      </div>
                    </li>
                    <li style="padding-bottom: 0px; margin-top: -15px;">
                      <div class="single-order-list heading">
                        Additional Shipping Charges
                      </div>
                    </li>
                    <li style="padding: 10px 0 0 0;">
                      <div class="single-order-list title-bold">
                        <div class="row" style="margin-bottom: 2%;">
                          <div class="col-lg-6">
                            <input id="add1" name="addval1" tabindex="27" onchange="getaddship()" type="checkbox" value="0" placeholder="$0.00" style="margin-bottom: 15px;" <?php echo (isset($add1) ? $add1 : ""); ?>> No Additional Shipping<br>
                            <input id="add2" name="addval2" tabindex="28" onchange="getaddship()" type="checkbox" value="80" placeholder="$0.00" style="margin-bottom: 15px;" <?php echo (isset($add3) ? $add3 : ""); ?>> Construction Site + $80<br>
                          </div>
                          <div class="col-lg-6">
                            <input id="add3" name="addval3" tabindex="29" onchange="getaddship()" type="checkbox" value="40" placeholder="$0.00" style="margin-bottom: 15px;" <?php echo (isset($add2) ? $add2 : ""); ?>> Call Before Delivery + $40<br>
                            <input id="add4" name="addval4" tabindex="30" onchange="getaddship()" type="checkbox" value="100" placeholder="$0.00" style="margin-bottom:15px;" <?php echo (isset($add4) ? $add4 : ""); ?>> Lift Gate + $100<br>
                          </div>
                          <div class="col-lg-12">
                            <input id="add5" name="addval5" tabindex="31" onchange="getaddship()" type="checkbox" value="150" placeholder="$0.00" style="margin-bottom:15px;" <?php echo (isset($add5) ? $add5 : ""); ?>> Delivery Appointment With 4 Hour Window + $150
                          </div>
                        </div>
                      </div>
                    </li>


                    <li style="padding: 10px 0 0 0;">
                      <div class="single-order-list title-bold">
                        <div style="padding-bottom: 1%;"> Quote Total <span class="right normal quotecost" id="quotecost" onchange="caltotal()"></span></div>
                        <div style="padding-bottom: 3%;"> Additional Shipping Charges <span class="right normal shipingcost" id="shipingcost" onchange="caltotal()"></span></div>
                      </div>
                    </li>

                    <li>
                      <div class="single-order-list title-bold">
                        <div style="padding-bottom: 1%;"> Subtotal <span class="right normal" id="subtotal"><?php echo "$ " . number_format((float)$finalquote['Sub_Total'], 2) ?: '-'; ?></span></div>
                        <div style="padding-bottom: 1%;"><span id="taxtxt"><?php echo $Taxtxt; ?></span><span class="right normal" id="tax"><?php echo $Tax; ?></span></div>
                        <span style="padding-bottom: 1%;"><?php echo  $totaltxt ?: "Total"; ?></span> <span class="right normal" id="total" style="font-weight: 500;font-size: 22px; margin-top: -8px;"> </span>
                        <!-- <input id="amount" name="amount" type="tel" min="1" placeholder="Amount" value="<?php echo $amount; ?>" required /> -->
                      </div>
                    </li>
                  </ul>

                  <img src="images/arrow.gif" border="0" id="arrow" alt=" " /><input type="checkbox" tabindex="32" id="agree" required> Yes, I, <b><span id="fullname"> </b> agree to all terms on Quote <b> #<span id="agreequoteno"> <?php echo (isset($quotenumber) ? $quotenumber : ''); ?> </span> </b> in the amount of <b> <span id="agreetotal"> <?php echo (isset($quoteamount) ? $quoteamount : ''); ?></span> </b> and understand that production should begin after payment has been successfully completed.
                  <br>
                  <div class="placeholder" align="center" id="paymenthead" style="display: none;">
                    <!-- <hr>
                    <h3 style='font-weight: 400;font-family: "Rubik", sans-serif;margin-bottom: -10px;margin-top: -10px;'>Click Payment Option Below</h3>
                    <hr> -->
                  </div>

                  <label for="amount" id="hideamount">
                    <span class="input-label">Amount</span>
                    <div class="input-wrapper amount-wrapper">
                      <input id="amount" name="amount" type='hidden' disabled type="tel" min="1" placeholder="Amount" value="<?php echo $amount; ?>" required />
                    </div>
                  </label>
                  <input id="taxclass" type="hidden">
                  <!-- check payment method -->
                  <?php
                  if ($store_payment_gateway == 'Stripe') {
                    error_log($start . "\n\n pay.php - stripe gateway active ------------\n", 3, "logs/pay/pay-log" . date("d-m-Y") . ".log");

                  ?>

                    <script src="https://js.stripe.com/v3/"></script>
                    <script>
                      var testingData = '<?php echo $store_publishedKey; ?>';
                      //   console.log(testingData, " testingData 2");

                      var script = document.createElement('script');
                      script.onload = function() {
                        //do stuff with the script
                      };
                      script.src = "stripe/client.js";
                      document.head.appendChild(script);
                    </script>
                    <div class="form-row" id=stripe_card style="display:none;">
                      <label for="card-element" width="100%">
                        Credit or debit card
                      </label>

                      <div id="card-element" style="width:100%;">
                      </div>
                      <div id="card-errors" style="color:red" role="alert"></div>
                      <br>
                      <button class="submit-btn" id="stripepaynow" style="display:none;height:40px;margin-top: 10px;width: 100%"><span>PAY NOW</span></button>
                    </div>
                    <?php
                  }

                  if ($store_payment_gateway == 'Braintree') {
                    error_log($start . "\n\n pay.php - braintree gateway active ------------\n", 3, "logs/pay/pay-log" . date("d-m-Y") . ".log");

                  ?>

                    <div class="credit-card-area" id="paymentdiv" style="display:none;">
                      <div class="bt-drop-in-wrapper">
                        <div id="bt-dropin"></div>
                      </div>
                    </div>

                    <div id="dialog"></div>

                    <div class="btn-wrapper" align="right">
                      <input id="nonce" name="payment_method_nonce" type="hidden" />
                      <button disabled class="submit-btn" type=button id="btnverzenden2" style="display: none; background-color: gray; width: 100%;">Payment Processing <span class="fa fa-spinner fa-pulse fa-2x fa-fw" style="margin-top: 3%;"></span></button>
                      <h5 id="btnverzenden3" style="text-align: center;display: none; "> Please wait a moment. Do Not Refresh this Page </h5>
                      <button class="submit-btn" name=btnverzenden id="btnverzenden" type="submit" style="display: none; width: 100%"><span>PAY NOW</span></button>
                    </div>

                  <?php
                  }
                  ?>

                </div>
                <div class="right-content-area" style="padding: 15px;font-family: 'Rubik', sans-serif;">
                  <h4 style="font-weight: 500;text-align: center;font-family: " Rubik", sans-serif;"><i class="fas fa-lock" style=" color: #02bd65; font-size: 14px;"></i><span style="font-family: 'Rubik', sans-serif;font-weight: 500;color: #02bd65;"> SSL</span> SECURED PAYMENT<h4>
                      <p style="text-align: center;font-weight: initial;">Your information is protected by 256-bit SSL encryption </p>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <!-- checkout page content area end -->
      <!-- payment process loading -->
      <div class="container" id="loading" style="display:none;">
        <div class="modal show">
          <div class="modal-dialog">
            <div class="modal-content" style="margin-top: 35%;">
              <div class="modal-body">
                <img data-toggle="modal" data-target="#modal" class="center" src="images/loadingimg.gif" style="position: sticky;margin-top: 10%;width: 80px;height: 80px;filter: hue-rotate(90deg);">
                <h4 style="margin-top: -4%;color: green;font-weight: 600;" align="center">Payment Processing <span class="loading" style="margin-top: -4%;font-size: xx-large;"></span></h4>
                <hr>
                <h4 style="text-align: center;margin-bottom: 6%;"> Please do not refresh or close page until payment has successfully completed </h4>
              </div>
            </div>
          </div>
        </div>
        <div data-toggle="modals" data-target="#modals" style="display:block;" class="centerbg"> </div>
      </div>

      <!-- checkout page content area end -->
      <!-- <div id="overlaytext" data-toggle="modals" data-target="#modals"  style=" color: white;padding:22% 24% 16%;font-size: 20px;display:none;" class="centerbg" > Please do not refresh or close page until payment has successfully completed</div>
      <img id="loading" data-toggle="modal" style="display:none;" data-target="#modal" class="center" src="images/loadingimg.gif">
        <div id="overlay" data-toggle="modals" data-target="#modals"  class="centerbg" style="display:none;"> </div> -->

      <div class="container" id="myModal" style="display:none;">
        <div class="modal show">
          <div class="modal-dialog">
            <div class="modal-content" style="margin-top: 20%;">
              <div class="modal-header">
                <h2>Oops! Something went wrong</h3>

              </div>
              <div class="modal-body">
                <h1 style="color: #ff6161;font-weight: bold; text-align: center;"><img src="images/fail.svg" style="width: 12%;"> Payment Failure</h2>
                  <h5 style="text-align: center;margin-top: 5%;">Please call <b><?php echo $Storedetails['Store_Phone_Number']; ?></b> or email <b><?php echo $Storedetails['Store_Email']; ?></b> for further assistance -or- contact your issuing bank and try again.</h5>
                  <h6 id="tranError" style="text-align: center;margin-top: 5%;"></h6>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" id="closeErrorModal" onClick="get_id(this.id)">Try Again</button>
              </div>

            </div>
          </div>
        </div>
        <div id="modaloverlay" data-toggle="modals" data-target="#modals" class="centerbg" style="display:block;"> </div>
      </div>
      <!-- footer area one start -->
      <footer class="footer-arae-one">
        <!-- //.footer top one -->
        <div class="copyright-area blue-bg">
          <!-- copyright area -->
          <div class="container">
            <div class="row">
              <div class="col-lg-12" style="text-align: center;">
                <div class="copyright-inner">
                  <!-- copyright inner -->
                  <div class="left-content-area">
                    <span class="copyright-text">© Copyright <?php echo date("Y") . " - " . preg_replace("/[^a-zA-Z]/", " ", $storename); ?></span>
                  </div>
                </div>
                <!-- //. copyright inner -->
              </div>
            </div>
          </div>
        </div>
        <!-- //. copyright area -->

      </footer>
      <!-- footer area one end -->
      <style>
        .braintree-toggle-hide {
          display: none;
        }
      </style>

    <?php
    require('script.php');
  }
} else {
  error_log($start . "\n\n error ------------------\n\n", 3, "logs/pay/pay-log" . date("d-m-Y") . ".log");
  echo error();
}
//Not Authorized Access Message
function error()
{
    ?>
    <html lang="en">

    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
      <link href="css/bootstrap.min.css" type="text/css" rel="stylesheet" />
      <title>Authentication</title>
      <style>
        h1 {
          margin: 2em 0;
        }
      </style>
    </head>

    <body>
      <div class="container">
        <h1 class="text-center">Authentication Failure</h1>
        <div class="jumbotron" align="center" style="border-bottom:5px inset #ffb7b7 !important">
          You are not authenticated! <br />
          Please try again.
        </div>
      </div>
    </body>

    </html>
  <?php
}
ob_end_flush();
  ?>