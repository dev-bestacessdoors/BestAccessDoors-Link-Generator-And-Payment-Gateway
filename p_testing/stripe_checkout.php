<?php
    
require_once("/home/doorpay/public_html/vendor/autoload.php");
require_once("/home/doorpay/public_html/vendor/stripe/stripe-php/init.php");

ini_set('display_errors', 1);
error_reporting(~0);

$baseUrl = stripslashes(dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = $baseUrl == '/' ? $baseUrl : $baseUrl . '/';

date_default_timezone_set('Asia/Kolkata');
$today_Date = date("Y-m-d h:i:sa");
$start = "\n\n Started Execution @ $today_Date ";
error_log($start."\n\n".json_encode($_POST), 3, "logs/stripe/stripe-log".date("d-m-Y").".log"); 
 
		$storename = $_GET['s'];
		if(isset($_COOKIE['storename'])) {
				$storename= $_COOKIE['storename'];
 }
 

$creatorKey="8e9640c1f4b7e8e3443fd95d7c16b7e6";


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
        // echo "<br> curl error : " . $err;

        // error_handler('', $err, 'error', '');
        // log_entry("CURL Error Response : ".$err);
        return array('error'=> $err);
    }
    else
    {
        // log_entry("CURL Error Response : ".$response);

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
    // error_log(function_called."\n\n".$creatorurl, 3, "logs/stripe/stripe-log".date("d-m-Y").".log"); 
    $zoho_auth = json_decode(generatetoken(), true); 
    // error_log(before_function_called."\n\n".$zoho_auth, 3, "logs/stripe/stripe-log".date("d-m-Y").".log"); 
    $json = curl($creatorurl, "GET", "", $zoho_auth['creator']);  
    //  error_log(After_function_called."\n\n".json_encode($json), 3, "logs/stripe/stripe-log".date("d-m-Y").".log"); 
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

// $StoreData = curl_get_contents("https://creator.zoho.com/api/json/quotes/view/All_Stores?authtoken=".$creatorKey."&scope=creatorapi&zc_ownername=zoho_zoho1502&raw=true&Store_Name=".$storename);


  $Storeurl = "https://creator.zoho.com/api/v2/zoho_zoho1502/quotes/report/All_Stores?Store_Name=".$storename."&raw=true";
  
//   error_log($store_url."\n\n".$Storeurl, 3, "logs/stripe/stripe-log".date("d-m-Y").".log"); 
  $Storedataresponse = getcreatordata($Storeurl);

/* Sanitizing the Store details received from zoho creator */
// $decodedStoreText = html_entity_decode($StoreData);
// $myStoreArray = json_decode($decodedStoreText, true);
// $Storedataresponse = $myStoreArray['Stores'];
foreach($Storedataresponse as $item) {
  $Storedetails = $item;
}

// error_log($store_response."\n\n".json_encode($Storedetails), 3, "logs/stripe/stripe-log".date("d-m-Y").".log"); 

//data formations
$StoreWebsite=$Storedetails['WebsiteText'];
$TCLink=$Storedetails['T_C_Link'];
$store_id = $Storedetails['ID'];
if ($Storedetails['Payment_gateway_Mode'] == "Test Mode") {
 $stpauth = $Storedetails['Secret_Key_Test'];
}else {
  $stpauth = $Storedetails['Secret_Key_Live'];
}

$message ='';
try{
\Stripe\Stripe::setApiKey($stpauth);
$checkauth = 0;
 
}catch(Exception $e){
    $checkauth = 1;
    $message = $e->getError()->message; 
} 

 

     if(isset($_POST) && $checkauth == 0 )
        { 
         
          $customer_firstname   = $_POST['customer_firstname'];
          $customer_lastname    = $_POST['customer_lastname'];
          $customer_email       = $_POST['customer_email'];
          $customer_phonenumber = $_POST['customer_phonenumber'];
          $braintree_cust_id    = $_POST['Stripe_custid'];
    
          // Customer Billing address
          $Billaddress1  = $_POST['Baddress1'];
          $Billaddress2  = $_POST['Baddress2'];
          $Billcity      = $_POST['Bcity'];
          $Billstate     = $_POST['Bstate'];
          $Billpostcode  = $_POST['Bpostcode'];
          $Billcountry   = $_POST['Bcountry'];
          $companyname   = $_POST['Bcompanyname'];
    
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
    
          //quote
    	  $quotecost = $_POST['quotecost'];
          $Additional_Shipping_Charge = $_POST['addtionalshipping'];
    	  $overal_subtotal = $_POST['overal_subtotal'];
    	  $shippingcost = $_POST['shippingcost'];
    
          $quote = $_POST['quote'];
          $amount = round((float)$_POST['amount'], 2);
          $Store     = $_POST['store'];
          $currency    = $_POST['currency'];
          $taxclass   = $_POST['Tax_Class'] ?: '';
          $token = $_POST['token'];
          $stripe_custid = $_POST['Stripe_custid'];
          $payrecdidencode =  $_POST['payrecdid'] ;
          $payrecdid = base64_decode($payrecdidencode);
          $taxcad =  $_POST['Tax_CAD'];
	            
	            //////////Get Card details////////////////
	            
	    if($token != ""){
	        
	    $tokenres = \Stripe\Token::retrieve($token);
	     
	    $chargeid = $token;
		$last4 = $tokenres['card']['last4'];
		$Card_Type = $tokenres['card']['brand']; 
		$paymentgateway = "Stripe";
		$trans_status = "payment_failed";
	        
	    }
        
	            
	            
	            
	            
	            
	            
	            
		// check the customer - if stripe id exists
		if($stripe_custid != "") {
		     try{
                $customer = \Stripe\Customer::retrieve($stripe_custid);
                $customercheck = 0;
                }catch(Exception $e){
                    $message = $e->getError()->message;
                    $customercheck = 1; 
                    } 

		      error_log("\n\n Retrieved contact response: ".$customer, 3, "logs/stripe/stripe-log".date("d-m-Y").".log");
        
              if($customercheck == 0){
                  $stripe_customerid = $customer['id'];
              }else { 
          
                      try{
                      $customer = \Stripe\Customer::create([
    		            'description' => $companyname,
    		            'email' => $customer_email,
    		            'source' => $token,
    		            'address' => array(
    		              'city'=> $Billcity,
    		              'country'=> $Billcountry,
    		              'line1'=> $Billaddress1,
    		              'line2'=> $Billaddress2,
    		              'postal_code'=> $Billpostcode,
    		              'state'=> $Billstate
    		            ),
    		            'email'=> $customer_email,
    		            'name'=> $customer_firstname.' '.$customer_lastname,
    		            'phone'=> $customer_phonenumber,
    		            'shipping' => array(
    		                'address'=> array(
    		                  'city'=> $Shipcity,
    		                  'country'=> $Shipcountry,
    		                  'line1'=> $Shipaddress1,
    		                  'line2'=> $Shipaddress2,
    		                  'postal_code'=> $Shippostcode,
    		                  'state'=> $Shipstate
    		                ),
    		                'name'=> $Shipfirstname.' '.$Shiplastname,
    		                'phone'=> $Shipphonenumber
    		              )
    		          ]);
		          $stripe_customerid = $customer['id']; 
                  error_log("\n\nCustomer id not exists in stripe - creation response: ".$customer, 3, "logs/stripe/stripe-log".date("d-m-Y").".log");
                  }catch(Exception $e){
                      $message = $e->getError()->message; 
                      error_log("\nCustomer creation Catch: ".$message, 3, "logs/stripe/stripe-log".date("d-m-Y").".log");
                  }
              }
         }else {
            
              try{
              $customer = \Stripe\Customer::create([
    		        'description' => $companyname,
    		        'email' => $customer_email,
    		        'source' => $token,
    		        'address' => array(
    		          'city'=> $Billcity,
    		          'country'=> $Billcountry,
    		          'line1'=> $Billaddress1,
    		          'line2'=> $Billaddress2,
    		          'postal_code'=> $Billpostcode,
    		          'state'=> $Billstate
    		        ),
    		        'email'=> $customer_email,
    		        'name'=> $customer_firstname.' '.$customer_lastname,
    		        'phone'=> $customer_phonenumber,
    		        'shipping' => array(
    		            'address'=> array(
    		              'city'=> $Shipcity,
    		              'country'=> $Shipcountry,
    		              'line1'=> $Shipaddress1,
    		              'line2'=> $Shipaddress2,
    		              'postal_code'=> $Shippostcode,
    		              'state'=> $Shipstate
    		            ),
    		            'name'=> $Shipfirstname.' '.$Shiplastname,
    		            'phone'=> $Shipphonenumber
    		          )
    		      ]);
    		     $stripe_customerid = $customer['id']; 
                  error_log("\n\nCustomer id not exists  in stripe - creation response: ".$customer, 3, "logs/stripe/stripe-log".date("d-m-Y").".log");
                  }catch(Exception $e){
                      $message = $e->getError()->message; 
                      error_log("\nCustomer creation Else  Catch: ".$message, 3, "logs/stripe/stripe-log".date("d-m-Y").".log");
                  }
                 }
    		 
        
		
        if($stripe_customerid != ""){
		// create charge
		try {
		  $charge = \Stripe\Charge::create([
		    'amount' => $amount*100 ,
		    'currency' => strtolower($currency),
		    'description' => $quote,
		     'metadata' => ['Quote_No' => $quote],
		     'customer'=> $stripe_customerid,
		       'shipping' => array(
		           'address'=> array(
		             'city'=> $Shipcity,
		             'country'=> $Shipcountry,
		             'line1'=> $Shipaddress1,
		             'line2'=> $Shipaddress2,
		             'postal_code'=> $Shippostcode,
		             'state'=> $Shipstate
		           ),
		           'name'=> $Shipfirstname.' '.$Shiplastname,
		           'phone'=> $Shipphonenumber,
		           'carrier'=> 'FedeEx'
		         )
		  ]);
		$message = '';
		$chargecheck =0;
		} catch (Exception $e) {
        $message =  $e->getError()->message; 
        error_log("\n\Charge Creation catch Response:".$message, 3, "logs/stripe/stripe-log".date("d-m-Y").".log");
        $chargecheck = 1;
        } 
        
        if ($chargecheck == 0) {
		  $chargeid = $charge['id'];
		  $last4 = $charge['payment_method_details']['card']['last4'];
		  $Card_Type = $charge['payment_method_details']['card']['brand'];
		  $transactionmode= $charge['payment_method_details']['type'];
		  $trans_status = $charge['status'];
		} 
    } 
 
          $creator_obj["First_Name"] =  $customer_firstname;
            $creator_obj["Last_Name"] =  $customer_lastname;
            $creator_obj["Email"] =  $customer_email;
            $creator_obj["Quoteno"] =  $quote;
            $creator_obj["Phone_Number"] =  $customer_phonenumber;
            $creator_obj["Company"] =  $companyname;
            $creator_obj["Street1"] =  $Billaddress1;
            if($Billaddress2 != "")
            {
             $creator_obj["Street2"] =  $Billaddress2;
            }
            $creator_obj["Town_City"] =  $Billcity;
            $creator_obj["State_Province"] =  $Billstate;
            $creator_obj["ZipCode"] =  $Billpostcode;
            $creator_obj["Country"] =  $Billcountry;
            $creator_obj["Ship_Firstname"] =  $Shipfirstname;
            $creator_obj["Shipping_Charge"] =  $shippingcost;
            $creator_obj["Quote_Amount"] =  $quotecost;
            $creator_obj["Ship_Lastname"] =  $Shiplastname;
            $creator_obj["Ship_Email"] =  $Shipemail;
            $creator_obj["Total_Amount"] = (string)$amount;
            $creator_obj["Stores"] =  $store_id;
            if($taxclass != "")
            {
             $creator_obj["Tax_Class"] =  $taxclass;
            }
            if($taxcad != "")
            {
             $creator_obj["Tax_CAD"] =  $taxcad;
            }
            $creator_obj["Currency"] =  $currency;
            $creator_obj["Ship_Phone_Number"] =  $Shipphonenumber;
            $creator_obj["Ship_Company"] =  $Shipcompanyname;
            $creator_obj["Ship_Street1"] =  $Shipaddress1;
            if($Shipaddress2 != '')
            {
                $creator_obj["Ship_Street2"] =  $Shipaddress2;
            }
            $creator_obj["Ship_Town_City"] =  $Shipcity;
            $creator_obj["Ship_State_Province"] =  $Shipstate;
            $creator_obj["Additional_Shipping_Charge"] =  explode(',', $Additional_Shipping_Charge);
            $creator_obj["Generate_Payment_Link_Id_String"] =  $payrecdid;
            $creator_obj["Ship_Zipcode"] =  $Shippostcode;
            $creator_obj["Ship_Country"] =  $Shipcountry;
            if($Shipnotes != '')
            {
                $creator_obj["Ship_Notes"] =  $Shipnotes;
            }
            if($trans_status != null)
            {
                $creator_obj["Transaction_Status"] =  $trans_status;
            }
            if($Card_Type != null)
            {
                $creator_obj["Payment_Method"] =  $Card_Type ?? '';
            }
            if($paymentgateway != null)
            {
                $creator_obj["Payment_Gateway"] =  $paymentgateway;
            }
            if($last4 != null)
            {
                $creator_obj["Card_Last_4_Digit"] =  $last4 ?? '';
            }
            if($chargeid != null)
            {
                $creator_obj["Payment_Transaction_No"] =  $chargeid ?? '';
            }
            if($amount != null)
            {
                $creator_obj["Amount_Paid"] = $amount ?? '';
            }
            $creator_obj["Decline_Reason"] = $message ?? '';
            $reqbody['data'] = $creator_obj;
            
            
 
        error_log("checkout page: create quote request".json_encode($reqbody)."\n\n", 3, "logs/stripe/stripe-log".date("d-m-Y").".log");
        
// 		$CustAddressString ="------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"authtoken\"\r\n\r\n".$creatorKey."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"scope\"\r\n\r\ncreatorapi\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Quoteno\r\n\r\n".$quote."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=First_Name\r\n\r\n".$customer_firstname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Last_Name\r\n\r\n".$customer_lastname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Email\r\n\r\n".$customer_email."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Phone_Number\r\n\r\n".$customer_phonenumber."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Company\r\n\r\n".$companyname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Street1\r\n\r\n".$Billaddress1."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Street2\r\n\r\n".$Billaddress2."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Town_City\r\n\r\n".$Billcity."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=State_Province\r\n\r\n".$Billstate."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=ZipCode\r\n\r\n".$Billpostcode."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Country\r\n\r\n".$Billcountry."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Firstname\r\n\r\n".$Shipfirstname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Shipping_Charge\r\n\r\n".$shippingcost."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Quote_Amount\r\n\r\n".$quotecost."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Total_Amount\r\n\r\n".$amount."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Stores\r\n\r\n".$storename."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Tax_Class\r\n\r\n".$taxclass ."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Tax_CAD\r\n\r\n".$taxcad ."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;   name=Currency\r\n\r\n".$currency ."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Lastname\r\n\r\n".$Shiplastname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Email\r\n\r\n".$Shipemail."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Phone_Number\r\n\r\n".$Shipphonenumber."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;  name=Ship_Company\r\n\r\n".$Shipcompanyname."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Street1\r\n\r\n".$Shipaddress1."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Street2\r\n\r\n".$Shipaddress2."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Town_City\r\n\r\n".$Shipcity."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_State_Province\r\n\r\n".$Shipstate."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Additional_Shipping_Charge\r\n\r\n".$Additional_Shipping_Charge."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Generate_Payment_Link_Id_String\r\n\r\n".$payrecdid."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;name=Ship_Zipcode\r\n\r\n".$Shippostcode."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Country\r\n\r\n".$Shipcountry."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Ship_Notes\r\n\r\n".$Shipnotes."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Payment_Transaction_No\r\n\r\n".$chargeid."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Amount_Paid\r\n\r\n".$amount."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Payment_Method\r\n\r\n".$Card_Type."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Card_Last_4_Digit\r\n\r\n".$last4."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Payment_Gateway\r\n\r\n".$paymentgateway."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=Decline_Reason\r\n\r\n".$message."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition:form-data; name=Transaction_Status\r\n\r\n".$trans_status."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--"; 
		$updatecustAddressdata = curl_init();
		
	    array_push($zoho_auth['creator'], "Content-Type:application/json");
	    
    
        //  error_log("checkout page: create quote request".json_encode($zoho_auth['creator'])."\n\n", 3, "logs/stripe/stripe-log".date("d-m-Y").".log");
		curl_setopt_array($updatecustAddressdata, array(
			CURLOPT_URL => "https://creator.zoho.com/api/v2/zoho_zoho1502/quotes/form/Custom_Quote_Payment",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($reqbody),
			CURLOPT_HTTPHEADER => $zoho_auth['creator']
		));

		$UpdateQuoteResponse = curl_exec($updatecustAddressdata);
		$UpdateQuoteErr = curl_error($updatecustAddressdata);

		curl_close($updatecustAddressdata);
		if ($UpdateQuoteErr) {
			error_log("checkout page: create quote address response-error ".$UpdateQuoteResponse."\n\n", 3, "logs/stripe/stripe-log".date("d-m-Y").".log");
		}
		else {
			error_log("checkout page: create quote address response-success ".$UpdateQuoteResponse."\n\n", 3, "logs/stripe/stripe-log".date("d-m-Y").".log");
		}
		
	}

		if (isset($charge['id'])) {
			echo json_encode(array("info"=>$charge,"error"=>false,"message"=>$charge['status'],"redirect"=>$baseUrl."pay_transaction_imp.php?id=".$charge['id']."&site=".$StoreWebsite."&quote=".$quote."&s=".$storename."&scope=stripe"."&tc=".$TCLink));
		}else{
			$errorString .= 'Error: Your transaction has not been processed with the error message of ' . $message;
			$_SESSION["errors"] = $errorString;
			echo json_encode(array("info"=>$charge,"error"=> true,"message"=>$errorString));
		} 

$today_Date = date("Y-m-d h:i:sa");
$end= "\n\nWebhook Execution End @ $today_Date ";
error_log($end."\n\n", 3, "logs/stripe/stripe-log".date("d-m-Y").".log");

?>
