<?php


$today_Date = date("Y-m-d h:i:sa");
ini_set('display_errors', 1);
error_reporting(~0);
$myfile2 = fopen("logs/updateprovince.log", "a") or die("Unable to open file!");
$txt2 = "\n\nStarted Execution @ $today_Date \n";
fwrite($myfile2, $txt2);
// $creatorKey="8e9640c1f4b7e8e3443fd95d7c16b7e6";
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

function generatezohoauthtoken(){
     return curl_get_contents("https://1.door-pay.com/zoho_auth.php?platform=all&authbyT=93181c2a53fd65c1d7da3a86554dd44c64a4150c03e25634f15306626cf973c9");
}

/* Get provinces for paymentpage */
function getprovinces()
{
    $zoho_auth = json_decode(generatezohoauthtoken(), true);
    $url = "https://creator.zoho.com/api/v2/zoho_zoho1502/quotes/report/All_Province";
    $json = curl($url, "GET", "", $zoho_auth['creator']);
    return $json['data'];
}



// $json = curl_get_contents("https://creator.zoho.com/api/json/quotes/view/All_Province?authtoken=".$creatorKey."&scope=creatorapi&zc_ownername=zoho_zoho1502&raw=true");
// $myArray = json_decode($json, true);
// $Province = $myArray['Province'];
$Province = getprovinces();
// print_r($Province);
// fwrite($myfile2, $Province);
$provincedata  = array();
foreach ($Province as $value) {
  if (!array_key_exists($value['Country'],$provincedata))
  {
    $provincedata[$value['Country']] = array();
    array_push($provincedata[$value['Country']],$value);
  }
  else
  {
      array_push($provincedata[$value['Country']],$value);
  }
}
if(count($provincedata) > 0 ){
   file_put_contents('Province.json', json_encode($provincedata));
   echo "\nData Was Updated Successfully in 1.Door-Pay";
}else {
  echo "\nData Was Not Updated in 1.Door-Pay, Please Check with Administration";
}

// $today_Date = date("Y-m-d h:i:sa");
// $myfile2 = fopen("logs/updateprovince.log", "a") or die("Unable to open file!");
// $txt2 = "\n\nStarted Execution @ $today_Date \n";
// fwrite($myfile2, $txt2);
// $creatorKey="8e9640c1f4b7e8e3443fd95d7c16b7e6";
// function curl_get_contents($url)
// {
//   $ch = curl_init();
//   $timeout = 5;
//   curl_setopt($ch, CURLOPT_URL, $url);
//   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//   curl_setopt($ch, CURLOPT_HEADER, false);
//   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
//   $data = curl_exec($ch);
//   curl_close($ch);
//   return $data;
// }
// // if (isset($_GET['count'])) {
// //   $reccount = $_GET['count'];
// //   $reccount = intval($reccount)/200;
// //   if ($reccount < 1) {
// //     $reccount = 1;
// //   }
// //   echo "counter".$reccount;
// // }
// // for ($i=0; $i < $reccount; $i++) {

// $json = curl_get_contents("https://creator.zoho.com/api/json/quotes/view/All_Province?authtoken=".$creatorKey."&scope=creatorapi&zc_ownername=zoho_zoho1502&raw=true");
// $myArray = json_decode($json, true);
// $Province = $myArray['Province'];
// $provincedata  = array();
// foreach ($Province as $value) {
//   if (!array_key_exists($value['Country'],$provincedata))
//   {
//     $provincedata[$value['Country']] = array();
//     array_push($provincedata[$value['Country']],$value);
//   }
//   else
//   {
//       array_push($provincedata[$value['Country']],$value);
//   }
// }
// if(count($provincedata) > 0 ){
//   file_put_contents('Province.json', json_encode($provincedata));
//   echo "Data Was Updated Successfully in Doorpay";
// }else {
//   echo "Data Was Not Updated in Doorpay, Please Check with Administration";
// }

///////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////// For Single Data Update Code ///////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////
// echo "Alldata".json_encode($Province);
// $getdata = $_POST;
// $province_Json = file_get_contents("Province.json");
// echo json_encode($getdata);
// // }

// foreach ($getdata as $key => $value) {
//   // code...
// }
// $Country = $getdata['Country'];
// $Province = $getdata['Province'];
// $province_Json = json_decode($province_Json,true);
// $existing_data = array();
// $i =0;
// foreach ($province_Json[$Country] as $each_Province){
//   if ($Province == $each_Province['Province'] ) {
//     echo json_encode($each_Province);
//     $province_Json[$Country][$i]['HST'] = $getdata['HST'];
//     $province_Json[$Country][$i]['PST'] = $getdata['PST'] ;
//     $province_Json[$Country][$i]['GST'] = $getdata['GST'] ;
//     $province_Json[$Country][$i]['Total_Tax_Rate'] = $getdata['Total_Tax_Rate'] ;
//     $province_Json[$Country][$i]['Type'] = $getdata['Type'] ;
//     file_put_contents('Province.json', json_encode($province_Json));
//     array_push($existing_data,$each_Province);
//   }
//   $i = $i++;
// }
// if (count($existing_data) <= 0 ) {
//   echo "Province Not Exists";
//   $new_province_Json['Country'] =$getdata['Country'];
//   $new_province_Json['Province'] =$getdata['Province'];
//   $new_province_Json['ID'] =$getdata['ID'];
//   $new_province_Json['HST'] =$getdata['HST'];
//   $new_province_Json['PST'] =$getdata['PST'] ;
//   $new_province_Json['GST'] =$getdata['GST'] ;
//   $new_province_Json['Total_Tax_Rate'] =$getdata['Total_Tax_Rate'];
//   $new_province_Json['Type'] =$getdata['Type'] ;
//   fwrite($myfile2, "\n".json_encode($new_province_Json));
//   array_push($province_Json[$Country],$new_province_Json);
//   file_put_contents('Province.json', json_encode($province_Json));
// }

 ?>
