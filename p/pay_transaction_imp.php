<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$storename = $_GET['s'];

require_once('config.php');
$flag = 0;
if(isset($_COOKIE['flag'])) {
  $flag= $_COOKIE['flag'];
}
setcookie('flag',1,time()+3600);

?>
<html>
<script src="assets/js/jquery.js"></script>

<body>
    <?php
  require_once("/home/doorpay/public_html/vendor/autoload.php");
  require_once("/home/doorpay/public_html/vendor/stripe/stripe-php/init.php");

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
          // error_handler('', $err, 'error', '');
          // log_entry("CURL Error Response : ".$err);
          return array('error'=> $err);
      }
      else
      {
          // echo $response;
          return json_decode($response, true);
      }


  }

  function generatetoken(){
    return curl_get_contents("https://1.door-pay.com/zoho_auth.php?platform=all&authbyT=93181c2a53fd65c1d7da3a86554dd44c64a4150c03e25634f15306626cf973c9");
  }

  $zoho_auth = json_decode(generatetoken(), true);

  function getcreatordata($creatorurl)
  {
      $zoho_auth = json_decode(generatetoken(), true);  
      $json = curl($creatorurl, "GET", "", $zoho_auth['creator']);   
      $Create_Quote = $json['data']; 
      return $Create_Quote;
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
 
  $Storeurl = "https://creator.zoho.com/api/v2/zoho_zoho1502/quotes/report/All_Stores?Store_Name=".$storename."&raw=true"; 
  $Storedataresponse = getcreatordata($Storeurl);

  /* Sanitizing the Store details received from zoho creator */
  foreach($Storedataresponse as $item) {
    $Storedetails = $item;
  } 
  $StoreWebsite=$Storedetails['WebsiteText'];
  $TCLink=$Storedetails['T_C_Link'];

  if ($Storedetails['Payment_gateway_Mode'] == "Test Mode") {
    // $stpauth = '\''.$Storedetails['Secret_Key_Test'].'\'';
    $stpauth = $Storedetails['Secret_Key_Test'];
  }
  if($Storedetails['Payment_gateway_Mode'] == "Live Mode"){
    $stpauth = $Storedetails['Secret_Key_Live'];
  }

  \Stripe\Stripe::setApiKey($stpauth);

      //receive data from url
      $site=$_GET['site'];
      $TCLink=$_GET['tc'];
      $scope = isset($_GET['scope']);

      if (isset($_GET["id"]) && isset($_GET['scope']) ) {
        $charge = \Stripe\charge::retrieve(
          $_GET['id']
        );

        if (isset($charge['id']))
        {
          $header = "Sweet Success!";
          $icon = "success";
          $message = "Thank you! Your order is being processed. Order details will be sent to your email soon.";
        } else {
          $header = "Transaction Failed";
          $icon = "fail";
          $message = "Your transaction has a status of " . $charge['status']. ".";
        }

         $id = $charge['id'];
         $amount= number_format((float)($charge['amount'] / 100), 2);
         $last4 = $charge['payment_method_details']['card']['last4'];
         $paymentmethod = $charge['payment_method_details']['card']['brand'];
         $transactionmode= $charge['payment_method_details']['type'];
      }


?>
    <title> <?php echo preg_replace("/[^a-zA-Z]/", " ", $storename)." - Payment Success" ?></title>
    <div style="width: 100%;" align="center">
        <img src="<?php echo $logourl; ?>" alt="Logo" style="width: 100%;">
    </div>
    <div class="wrapper">
        <!--get bootstrap css   -->
        <link href="css/bootstrap.min.css" type="text/css" rel="stylesheet" />
        <style>
        body {
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol" !important;
        }

        h1 {
             font-weight: 600 !important;
        }

        h2 {
             margin: 0 !important;
        }
         td {
            font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol" !important;
        }

        * {
            box-sizing: border-box;
        }

        .column {
            float: left;
            width: 50%;
            padding: 10px;
        }

        /* Clear floats after the columns */
        .row:after {
            content: "";
            display: table;
            clear: both;
        }

        /* Responsive layout - makes the two columns stack on top of each other instead of next to each other */
        @media screen and (max-width: 600px) {
            .column {
                width: 100%;
            }
        }
        </style>
        <?php require_once("../includes/head.php"); ?>
        <div class="response container" style="padding-right: 0px;" id="trans">
            <div class="content">
                <div class="icon">
                    <img src="images/<?php echo($icon)?>.svg" alt="">
                </div>

                <h1><?php echo($header)?></h1>
                <section>
                    <p style="font-size: 20px;"><?php echo($message)?></p>
                </section>
                <section>
                    <center>
                        <button class="btn btn-primary" onclick="showtrans()">View Transaction Details</button>
                    </center>
                </section>

                <section>
                    <center>
                        <button style="font-size:22px;" id="paynowbuttonpreloaded"
                            onclick="location.href='<?php echo $site;?>'" class="btn btn-success "
                            style="float:right">Return to website</button>
                    </center>
                </section>

            </div>
        </div>

        <div class="container" id="transdetail" style="display:none">
            <h1> Transaction Details</h1><br>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="row">
                    Thanks for your business! We have received your payment and your order is now being processed. You
                    will recieve an email receipt shortly.
                    </br>
                    Once your order has shipped, we will email you the tracking details.
                    </br> </br>
                    Thanks,
                    </br>
                    The Best Access Doors Team
                </div>
            </div>

            <div align="right">

            </div><br>
            <!-- ///////////////////////////////////////////////////////////////////////////////////////////////////////// -->
            <table class="table table-bordered" style="width: 90%;margin-left: 10%;margin-right: 20%;">
                <tbody>
                    <tr>
                        <th colspan="2" style="text-align: center;">
                            <h2>Payment Details</h2>
                        </th>
                    </tr>
                    <tr>
                        <td style="background-color: #f7f7f7;text-align: left; width: 50%;">Quote Number</td>
                        <td style="width: 50%;"><?php echo $_GET['quote'];?></td>
                    </tr>
                    <tr>
                        <td style="background-color: #f7f7f7;text-align: left; width: 50%;">Transaction Number</td>
                        <td style="width: 50%;"><?php echo $id; ?></td>
                    </tr>
                    <tr>
                        <td style="background-color: #f7f7f7;text-align: left;">Payment Method</td>
                        <td><?php echo $paymentmethod; ?></td>
                    </tr>
                    <tr>
                        <td style="background-color: #f7f7f7;text-align: left;">Last 4 Digits of CC</td>
                        <td><?php echo $last4; ?></td>
                    </tr>
                    <tr>
                        <td style="background-color: #f7f7f7;text-align: left;">Amount Paid</td>
                        <td>$ <?php echo $amount; ?></td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

    <style type="text/css">
    @media print {
        #myPrntbtn {
            display: none;
        }

        #download {
            display: none;
        }

        #footerhide {
            display: none;
        }

    }
    </style>
    <!-- footer area one start -->
    <footer class="footer-arae-one" style="background-color: #d2d2d2;" id="footerhide">
        <!-- //.footer top one -->
        <div class="copyright-area blue-bg">
            <!-- copyright area -->
            <div class="container" style="padding: 10px;">
                <div class="row">
                    <div class="col-lg-12" style="text-align: center;">
                        <div class="copyright-inner">
                            <!-- copyright inner -->
                            <div class="left-content-area">
                                <span class="copyright-text">© Copyright
                                    <?php echo date("Y")." - ".preg_replace("/[^a-zA-Z]/", " ", $storename); ?> - <a
                                        href=".<?php echo $TCLink?>.">View Terms and Conditions</a></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </footer>


    <script type="text/javascript">
    var flag = <?php echo $flag; ?>;
    if (flag == 1) {
        document.getElementById('trans').style.display = "none";
        document.getElementById('transdetail').style.display = 'block';
        console.log("flage:" + flag);
    }

    // var timeLeft = 20;
    // var elem = document.getElementById('download');
    // var timerId = setInterval(countdown, 1);
    // function countdown() {
    //     if (timeLeft == -1) {
    //         clearTimeout(timerId);
    //         doSomething();
    //     } else {
    //         elem.innerHTML = 'Quote PDF Available within (' +timeLeft + ' Sec)';
    //         timeLeft--;
    //     }
    // }
    function doSomething() {
        document.getElementById('download').disabled = false;
        document.getElementById('download').innerHTML = "Download Signed Quote";
    }
    // document.getElementById('back').style.display = 'none';
    function showtrans() {
        console.log("func allled");
        document.getElementById('trans').style.display = 'none';
        document.getElementById('transdetail').style.display = 'block';
        // document.getElementById('back').style.display = 'block';
    }

    function back() {
        document.getElementById('trans').style.display = 'block';
        document.getElementById('transdetail').style.display = 'none';
        // document.getElementById('back').style.display = 'none';
    }
    </script>
</body>

</html>