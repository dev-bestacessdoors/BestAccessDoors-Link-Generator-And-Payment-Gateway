<?php

if(isset($_POST['verify'])){
$value = $_POST['inputPassword'];
if ($value ==  "bestpay") {
session_start();
error_reporting(0);
if($_SESSION['login']!=''){
$_SESSION['login']='';
}?>

<style>
.button {
  background-color: #e7e7e7; /* Green */  border: none;
  color: Blue;
  padding: 10px 15px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 4px 2px;
  cursor: pointer;
	border-radius: 8px;
}
</style>

<div align="left"  style=" margin-top: 10px;margin-left: 12%;"><button onclick="goLoad()" class="button">Refresh Page</button><a href="logout.php"><button class="button">Logout</button></a></div>
<iframe height='100%' width='77%' style="margin-left: 12%;border-radius: 5px;" name="creatorifm" frameborder='0' allowTransparency='true' scrolling='auto' src='https://creator.zohopublic.com/zoho_zoho1502/quotes/form-embed/Generate_Payment_Link_State_Less/QUx96AjqSV8XMSsNtUAJDjWs8HB9TExyS5fAZzagrpC372mYHRyDJmTx3G1zQ2A6GMb3rR5545EMm0M7B78hz1Q9NbNXjTddTs9t'></iframe>
<script>
document.getElementsByClassName('form-header').style.display = "none";
</script>

<script type="text/javscript">

function AvoidSpace(event) {
    var k = event ? event.which : window.event.keyCode;
    if (k == 32) return false;
}
</script>

<?
$hide = "style=display:none";
}else {
?><?php
echo "<script>alert('Invalid password');</script>";
}
}?>

<?if(!$_SESSION['login']!='')
{?>
<div <? echo (isset($hide) ? $hide : "");?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="refresh" content="900" >
	<title> Generate Quote Payment Link</title>
	<link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.slim.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
        <div class="card card-signin my-5">
          <div class="card-body">
            <h4 class="card-title text-center">Enter Password to Access <br> Payment Generation Page </h4>
            <form class="form-signin" method="post">
              <div class="form-label-group">
                <input type="password" id="inputPassword" name= "inputPassword" class="form-control" placeholder="Password" required>
                <label for="inputPassword">Password</label>
              </div>

              <button class="btn btn-lg btn-primary btn-block text-uppercase" name="verify" type="submit">Verify</button>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
  function goLoad() {
    window.location.reload(true);
}
  </script>
</body>



</div>


<?}else{
	echo "string";
}?>
