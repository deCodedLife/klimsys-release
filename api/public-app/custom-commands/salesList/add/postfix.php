<?php

foreach ( $requestData->products as $product ) {

    $product->sale_id = $saleID;
    $API->returnResponse( $product, 500 );

    $API->DB->insertInto( "salesProductsList" )
        ->values( $product )
        ->execute();

} // foreach. $requestData->pay_object as $service
