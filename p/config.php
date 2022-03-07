<?php
//error handler
chdir(dirname(__FILE__));	
    $logfile_dir = "./logs/";  
    $logfile = $logfile_dir . "php_" . date("y-m-d") . ".log";
    $logfile_delete_days = 10;
    function error_handler($errno, $errstr, $errfile, $errline)
    {
        global $logfile_dir, $logfile, $logfile_delete_days;

        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting, so let it fall
            // through to the standard PHP error handler
            return false;
        }

        $filename = basename($errfile);

        switch ($errno) {
            case E_USER_ERROR:
                file_put_contents($logfile, date("y-m-d H:i:s.").gettimeofday()["usec"] . " $filename ($errline): " . "ERROR >> message = [$errno] $errstr\n", FILE_APPEND | LOCK_EX);
                break;

            case E_USER_WARNING:
                file_put_contents($logfile, date("y-m-d H:i:s.").gettimeofday()["usec"] . " $filename ($errline): " . "WARNING >> message = $errstr\n", FILE_APPEND | LOCK_EX);
                break;

            case E_USER_NOTICE:
                file_put_contents($logfile, date("y-m-d H:i:s.").gettimeofday()["usec"] . " $filename ($errline): " . "NOTICE >> message = $errstr\n", FILE_APPEND | LOCK_EX);
                break;

            default:
                file_put_contents($logfile, date("y-m-d H:i:s.").gettimeofday()["usec"] . " $filename ($errline): " . "UNKNOWN >> message = $errstr\n", FILE_APPEND | LOCK_EX);
                break;
        }     

        return true;    // Don't execute PHP internal error handler
    }

    set_error_handler("error_handler");

    if ($storename == "Access_Doors_Canada") {
        $logourl = 'logo/adc.jpg';
        $logourl_new = 'logobanners/adc.jpg';
        }elseif ($storename == "Acudor_Access_Panels") {
        $logourl = 'logo/aap.jpg';
        $logourl_new = 'logobanners/aap.jpg';
        }elseif ($storename == "Access_Doors_And_Panels") {
        $logourl = 'logo/adap.jpg';
        $logourl_new = 'logobanners/adap.jpg';
        }elseif ($storename == "Best_Access_Doors") {
        $logourl = 'logo/bad.jpg';
        $logourl_new = 'logobanners/bad.jpg';
        }elseif ($storename == "Best_Access_Doors_Canada") {
        $logourl = 'logo/badc.jpg';
        $logourl_new = 'logobanners/badc.jpg';
        }elseif ($storename == "California_Access_Doors") {
        $logourl = 'logo/cad.jpg';
        $logourl_new = 'logobanners/cad.jpg';
        }elseif ($storename == "Max_Supply") {
        $logourl = 'logo/max.jpg';
        $logourl_new = 'logobanners/max.jpg';
        }elseif ($storename == "Public_Furniture") {
        $logourl = 'logo/pub.jpg';
        $logourl_new = 'logobanners/pub.jpg';
        }elseif ($storename == "Best_Roof_Hatches") {
        $logourl = 'logo/brh.jpg';
        $logourl_new = 'logobanners/brh.jpg';
        }
        else {
        $logourl = '<h5> Invalid Store Name. Please Contact Sales Person . . . </h5>';
        }
      
      $baseUrl = stripslashes(dirname($_SERVER['SCRIPT_NAME']));
      $baseUrl = $baseUrl == '/' ? $baseUrl : $baseUrl . '/';
