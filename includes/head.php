<?php
    require_once("../includes/braintree_init.php");
    require_once('config.php');
    ?>
<head>
    <meta charset="UTF-8">
    <title><?php if(isset($storename)){
  		echo preg_replace("/[^a-zA-Z]/", " ", $storename) . " - Payment was Successfully " ;} ?></title>
    <link rel=stylesheet type=text/css href="css/app.css">
    <link rel=stylesheet type=text/css href="css/overrides.css">
</head>
