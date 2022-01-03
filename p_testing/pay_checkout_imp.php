<?php
$storename =$_GET['s'];
require_once("../includes/braintree_init.php");
require_once('config.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
//
if(isset($_COOKIE['storename'])) {
    $storename= $_COOKIE['storename'];
}
/* function templating the GET requests sent through this generator */
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
trigger_error('checkout page starts '.json_encode($_POST).'--- <br>', E_USER_NOTICE);

//creator key
$creatorKey="8e9640c1f4b7e8e3443fd95d7c16b7e6";

    if(isset($_POST))
    {
        // Customer details
            $customer_id            = $_POST['creatorcustomerid'];
            // $braintree_cust_id    = $_POST['BraintreecustId'];
            $tax_exempt             = $_POST['tax_exempt'];
            $companyname            = $_POST['Bcompanyname'];
            $quotecost              = $_POST['quotecost'];
            $shippingcost           = $_POST['shippingcost'];

        // Customer Billing address
            $customer_firstname   = $_POST['customer_firstname'];
            $customer_lastname    = $_POST['customer_lastname'];
            $customer_email       = $_POST['customer_email'];
            $customer_phonenumber = $_POST['customer_phonenumber'];
            $Billaddress1  = $_POST['Baddress1'];
            $Billaddress2  = $_POST['Baddress2'];
            $Billcity      = $_POST['Bcity'];
            $Billstate     = $_POST['Bstate'];
            $Billpostcode  = $_POST['Bpostcode'];
            $Billcountry   = $_POST['Bcountry'];

        // Customer Shipping address
            $Shipfirstname   = $_POST['Ship_firstname'];
            $Shiplastname    = $_POST['Ship_lastname'];
            $Shipemail       = $_POST['Ship_email'];
            $Shipphonenumber = $_POST['Ship_phonenumber'];
            $Shipcompanyname = $_POST['S_company'];
            $Shipaddress1  = $_POST['Saddress1'];
            $Shipaddress2  = $_POST['Saddress2'];
            $Shipcity      = $_POST['Scity'];
            $Shipstate     = $_POST['Sstate'];
            $Shippostcode  = $_POST['Spostcode'];
            $Shipcountry   = $_POST['Scountry'];
            $Shipnotes   = $_POST['S_notes'];
            $Additional_Shipping_Charge = $_POST['addtionalshipping'];
            $quote = $_POST['quote'];
            $amount = $_POST['amount'];
            $Store     = $_POST['store'];
            $currency    = $_POST['currency'];
            $payrecdidencode =  $_POST['payrecdid'] ;
            $taxclass =  $_POST['Tax_Class'];
            $taxcad =  $_POST['Tax_CAD'] ;
            $payrecdid = base64_decode($payrecdidencode);

            $paymentgateway = "braintree";

            // if($currency == "USD" && $_GET['s'] == "Acudor_Access_Panels")
            // {
            //     $BraintreeMerchatntId="acudoraccesspanelscomUSD";
            // }
            // else if($currency == "USD")
            // {
            //     $BraintreeMerchatntId="devsoft";
            // }
            // else if($currency == "CAD")
            // {
            //     $BraintreeMerchatntId="prashanthCAD";
            // }

                //for store details
                $StoreData = curl_get_contents("https://creator.zoho.com/api/json/quotes/view/All_Stores?authtoken=".$creatorKey."&scope=creatorapi&zc_ownername=zoho_zoho1502&raw=true&Store_Name=".$storename);

                    /* Sanitizing the Store details received from zoho creator */
                    $decodedStoreText = html_entity_decode($StoreData);
                    $myStoreArray = json_decode($decodedStoreText, true);
                    $Storedataresponse = $myStoreArray['Stores'];
                    foreach($Storedataresponse as $item) {
                        $Storedetails = $item;
                    }
                    //data formations
                    $storeid = $Storedetails['ID'];
                    $StoreWebsite=$Storedetails['WebsiteText'];
                }

        $nonce = $_POST['payment_method_nonce'];
        $result = $gateway->transaction()->sale([
            'orderId'  => $quote,
            'amount' => $amount,
            'paymentMethodNonce' => $nonce,
            'customer' => [
                'id' => $customer_id,
                'firstName' => $customer_firstname,
                'lastName'  => $customer_lastname,
                'phone'     => $customer_phonenumber,
                'email'     => $customer_email,
            ],
            'billing' => [
                'firstName'         => $customer_firstname,
                'lastName'          => $customer_lastname,
                'company'           => $companyname,
                'streetAddress'     => $Billaddress1,
                'extendedAddress'   => $Billaddress2,
                'locality'          => $Billcity,
                'region'            => $Billstate,
                'postalCode'        => $Billpostcode,
                'countryName'      => $Billcountry
            ],
            'shipping' => [
                'firstName'         => $Shipfirstname,
                'lastName'          => $Shiplastname,
                'company'           => $Shipcompanyname,
                'streetAddress'     => $Shipaddress1,
                'extendedAddress'   => $Shipaddress2,
                'locality'          => $Shipcity,
                'region'            => $Shipstate,
                'postalCode'        => $Shippostcode,
                'countryName'       => $Shipcountry
            ],
            'options' => [
                'submitForSettlement'=> true,
                'storeInVaultOnSuccess' => true,
                'storeShippingAddressInVault' => true,
                'addBillingAddressToPaymentMethod' => true,
                'skipCvv' => false
            ]
        ]);

    // }


  trigger_error('checkout page transcation response data ' .print_r (json_encode($result),true).'--- <br>', E_USER_NOTICE);


//for further details in transaction
if ($result->success || !is_null($result->transaction))
{
         $transaction = $result->transaction;

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

		    $creditCard = $transaction->creditCard;
        $paymentmethod = $creditCard['cardType'];
        if (isset($transaction->paypal))
        {
          $paymentmethod ="PayPal";
        }

      $CustAddressString ="------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"authtoken\"\r\n\r\n".$creatorKey."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"scope\"\r\n\r\ncreatorapi\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Quoteno\r\n\r\n".$quote."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=First_Name\r\n\r\n".$customer_firstname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Last_Name\r\n\r\n".$customer_lastname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Email\r\n\r\n".$customer_email."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Phone_Number\r\n\r\n".$customer_phonenumber."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Company\r\n\r\n".$companyname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Street1\r\n\r\n".$Billaddress1."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Street2\r\n\r\n".$Billaddress2."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Town_City\r\n\r\n".$Billcity."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=State_Province\r\n\r\n".$Billstate."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=ZipCode\r\n\r\n".$Billpostcode."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Country\r\n\r\n".$Billcountry."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Firstname\r\n\r\n".$Shipfirstname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Shipping_Charge\r\n\r\n".$shippingcost."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Quote_Amount\r\n\r\n".$quotecost."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Total_Amount\r\n\r\n".$amount."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Stores\r\n\r\n".$storename."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Tax_Class\r\n\r\n".$taxclass ."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Tax_CAD\r\n\r\n".$taxcad ."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;   name=Currency\r\n\r\n".$currency ."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Lastname\r\n\r\n".$Shiplastname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Email\r\n\r\n".$Shipemail."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Phone_Number\r\n\r\n".$Shipphonenumber."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Company\r\n\r\n".$Shipcompanyname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Street1\r\n\r\n".$Shipaddress1."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Street2\r\n\r\n".$Shipaddress2."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Town_City\r\n\r\n".$Shipcity."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_State_Province\r\n\r\n".$Shipstate."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Additional_Shipping_Charge\r\n\r\n".$Additional_Shipping_Charge."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Generate_Payment_Link_Id_String\r\n\r\n".$payrecdid."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;name=Ship_Zipcode\r\n\r\n".$Shippostcode."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Country\r\n\r\n".$Shipcountry."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Notes\r\n\r\n".$Shipnotes."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Payment_Transaction_No\r\n\r\n".$transaction->id."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Amount_Paid\r\n\r\n".$transaction->amount."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Payment_Method\r\n\r\n".$paymentmethod."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Card_Last_4_Digit\r\n\r\n".$creditCard['last4']."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition:  form-data; name=Payment_Gateway\r\n\r\n".$paymentgateway."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;name=Tax_Exempted\r\n\r\n".tax_exempt."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Transaction_Status\r\n\r\n".$transaction->status."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";
      $updatecustAddressdata = curl_init();

      curl_setopt_array($updatecustAddressdata, array(
        CURLOPT_URL => "https://creator.zoho.com/api/zoho_zoho1502/json/quotes/form/Custom_Quote_Payment/record/add",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $CustAddressString,
        CURLOPT_HTTPHEADER => array(
          "cache-control: no-cache",
          "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        ),
      ));

      $UpdateQuoteResponse = curl_exec($updatecustAddressdata);
      $UpdateQuoteErr = curl_error($updatecustAddressdata);

      curl_close($updatecustAddressdata);
      if ($UpdateQuoteErr) {
          trigger_error('checkout page: create quote address response-error ' .json_encode ($UpdateQuoteErr,true).'--- <br>', E_USER_NOTICE);
      }
      else {
          trigger_error('checkout page: create quote address response-success  ' .json_encode ($UpdateQuoteResponse,true).'--- <br>', E_USER_NOTICE);
      }

				trigger_error('checkout page transcation success --- <br>', E_USER_NOTICE);
				echo json_encode(array("info"=>$result,"error"=>false,"message"=>$transaction->status,"redirect"=>$baseUrl . "pay_transaction.php?id=".$transaction->id."&site=".$StoreWebsite));

		} else {
      $creditCard = $transaction->creditCard;
      $paymentmethod = $creditCard['cardType'];

      if (isset($transaction->paypal))
      {
        $paymentmethod ="PayPal";
      }

    $CustAddressString ="------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"authtoken\"\r\n\r\n".$creatorKey."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"scope\"\r\n\r\ncreatorapi\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Quoteno\r\n\r\n".$quote."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=First_Name\r\n\r\n".$customer_firstname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Last_Name\r\n\r\n".$customer_lastname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Email\r\n\r\n".$customer_email."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Phone_Number\r\n\r\n".$customer_phonenumber."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Company\r\n\r\n".$companyname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Street1\r\n\r\n".$Billaddress1."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Street2\r\n\r\n".$Billaddress2."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Town_City\r\n\r\n".$Billcity."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=State_Province\r\n\r\n".$Billstate."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=ZipCode\r\n\r\n".$Billpostcode."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Country\r\n\r\n".$Billcountry."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Firstname\r\n\r\n".$Shipfirstname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Shipping_Charge\r\n\r\n".$shippingcost."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Quote_Amount\r\n\r\n".$quotecost."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Total_Amount\r\n\r\n".$amount."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Stores\r\n\r\n".$storename."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Tax_Class\r\n\r\n".$taxclass ."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Tax_CAD\r\n\r\n".$taxcad ."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;   name=Currency\r\n\r\n".$currency ."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Lastname\r\n\r\n".$Shiplastname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Email\r\n\r\n".$Shipemail."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Phone_Number\r\n\r\n".$Shipphonenumber."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Company\r\n\r\n".$Shipcompanyname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;    name=Ship_Street1\r\n\r\n".$Shipaddress1."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Street2\r\n\r\n".$Shipaddress2."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Town_City\r\n\r\n".$Shipcity."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_State_Province\r\n\r\n".$Shipstate."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Additional_Shipping_Charge\r\n\r\n".$Additional_Shipping_Charge."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Generate_Payment_Link_Id_String\r\n\r\n".$payrecdid."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;name=Ship_Zipcode\r\n\r\n".$Shippostcode."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Country\r\n\r\n".$Shipcountry."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Notes\r\n\r\n".$Shipnotes."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Payment_Transaction_No\r\n\r\n".$transaction->id."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Amount_Paid\r\n\r\n".$transaction->amount."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Payment_Method\r\n\r\n".$paymentmethod."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;name=Tax_Exempted\r\n\r\n".tax_exempt."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Card_Last_4_Digit\r\n\r\n".$creditCard['last4']."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Transaction_Status\r\n\r\n".$transaction->status."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--";
    $updatecustAddressdata = curl_init();
            curl_setopt_array($updatecustAddressdata, array(
              CURLOPT_URL => "https://creator.zoho.com/api/zoho_zoho1502/json/quotes/form/Custom_Quote_Payment/record/add",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $CustAddressString,
              CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
              ),
            ));
            $UpdateQuoteResponse = curl_exec($updatecustAddressdata);
            $UpdateQuoteErr = curl_error($updatecustAddressdata);
            curl_close($updatecustAddressdata);
            if ($UpdateQuoteErr) {
                trigger_error('checkout page: payment failed  update quote address response-error ' .json_encode ($UpdateQuoteErr,true).'--- <br>', E_USER_NOTICE);
            }
            else {
                trigger_error('checkout page: payment failed update quote address response-success  ' .json_encode ($UpdateQuoteResponse,true).'--- <br>', E_USER_NOTICE);
            }

				$errorString = 'Error: Your transaction has not been processed with status ' . $transaction->status . "\n";
				echo json_encode(array("info"=>$result,"error"=>true,"message"=>$errorString));
		}

			// echo json_encode(array("info"=>$result,"message"=>'success',"redirect"=>$baseUrl . "transaction.php?id=".$transaction->id."&site=".$StoreWebsite));
} else {
    $errorString = "";

    foreach($result->errors->deepAll() as $error) {
        $errorString = 'Error: ' . $error->code . ": " . $error->message . "\n";
    }

    trigger_error('checkout page transcation error ' .json_encode (json_encode($errorString),true).'--- <br>', E_USER_NOTICE);
    $_SESSION["errors"] = $errorString;

    	// header("Location: " . $baseUrl . "exception.php?message=".$errorString);

		 // some action goes here under php
		 echo json_encode(array("info"=>$result,"error"=> true,"message"=>$errorString));
}
