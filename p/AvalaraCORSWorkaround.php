<?php

$zipcode = $_GET["zipcode"];
$Shipcity = $_GET["Shipcity"];
$shipstate = $_GET["shipstate"];

// echo "ZipCode: ".$zipcode."<br> Shipcity: ".$Shipcity."<br> Shipstate: ".$shipstate."<br>";

$url = 'https://1.door-pay.com/api/avalatax/byzip/'.$zipcode.'/'.urlencode($Shipcity).'::'.$shipstate;

// echo $url."<br>";

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
