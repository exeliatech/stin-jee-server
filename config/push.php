<?php

//config file for Davibennun\LaravelPushNotification\Facades\PushNotification;

return array(

    'ios'     => array(
        'environment' =>'production',
        'certificate' =>base_path().'/config/st.pem',
        'passPhrase'  =>'',
        'service'     =>'apns'
    ),
    'android' => array(
        'environment' =>'production',
        'apiKey'      =>'AIzaSyCcmDlZi7rqkAWyJ3b0pEehmzdRXo3vpMI',
        'service'     =>'gcm'
    )

);
