<?php

$ch = curl_init();

curl_setopt( $ch, CURLOPT_URL,"https://yazdorov.docacrm.com" );
curl_setopt( $ch, CURLOPT_POST, 1 );
curl_setopt( $ch, CURLOPT_POSTFIELDS,
    "object=hardReports&command=update"
);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

curl_exec( $ch );
curl_close( $ch );