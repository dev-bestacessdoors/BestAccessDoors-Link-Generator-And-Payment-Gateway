<?php
    require_once("../includes/braintree_init.php");
    require_once('config.php');
//     ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
?>
<html>
    <?php require_once("../includes/head.php"); ?>
<body>

<?php
    //creator key
    $creatorKey="8e9640c1f4b7e8e3443fd95d7c16b7e6";

    //receive data from url
    $site=$_GET["site"];
       if (isset($_GET["id"])) {
        $transaction = $gateway->transaction()->find($_GET["id"]);

        trigger_error('transaction page:get transaction detail from id ' .json_encode ($transaction,true).'--- <br>', E_USER_NOTICE);

        $transactionSuccessStatuses = [
            Braintree\Transaction::AUTHORIZED,
            Braintree\Transaction::AUTHORIZING,
            Braintree\Transaction::SETTLED,
            Braintree\Transaction::SETTLING,
            Braintree\Transaction::SETTLEMENT_CONFIRMED,
            Braintree\Transaction::SETTLEMENT_PENDING,
            Braintree\Transaction::SUBMITTED_FOR_SETTLEMENT
        ];


        if (in_array($transaction->status, $transactionSuccessStatuses)) {
            $header = "Sweet Success!";
            $icon = "success";
            $message = "Thank you! Your order is being processed. Order details will be sent to your email soon.";

        } else {
            $header = "Transaction Failed";
            $icon = "fail";
            $message = "Your transaction has a status of " . $transaction->status . ".";

        }

        $currency=$transaction->currencyIsoCode;
        if($currency == "USD")
        {
            $companylogo="usdlogo";
        }else if($currency == "CAD")
        {
            $companylogo="cadlogo";
        }

        //tranasaction mode
        if($transaction->paymentInstrumentType == "credit_card")
        {
            $transactionmode="Credit Card";
        }
        else{
            $transactionmode="PayPal";
        }

        $creditCard = $transaction->creditCard;

    }
?>

<div style="width: 100%;" align="center">
      <img src="<?php echo $logourl; ?>" alt="Logo" >
</div>
<div class="wrapper">
<!--get bootstrap css   -->
<link href="css/bootstrap.min.css" type="text/css" rel="stylesheet" />

  <!-- <img src="images.jpg" alt="" style="height:78px;"> -->
    <div class="response container" style="padding-right: 0px;">
        <div class="content">
            <div class="icon">
            <img src="images/<?php echo($icon)?>.svg" alt="" >
            </div>

            <h1><?php echo($header)?></h1>
            <section>
                <p style="font-size: 20px;"><?php echo($message)?></p>
            </section>

           <section>
               <center>
                 <button style="font-size:22px;" id="paynowbuttonpreloaded"  onclick="location.href='<?php echo $site;?>'" class="btn btn-success " style="float:right">Return to website</button>                </center>
           </section>
            <!-- <section>
                <center>
                    <button style="font-size:25px;" id="paynowbuttonpreloaded"  onclick="location.href='<?php echo $site;?>'" class="btn btn-info" style="float:right">Return to website</button>
                </center>
            </section> -->
        </div>
    </div>
</div>

 

</body>
</html>
