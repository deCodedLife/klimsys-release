<?php

$API->DB->update( "atolOperations" )
    ->set( [
        "active" => 'N'
    ] )
    ->where( "id", $requestData->operation_id );

//$visits = $API->DB->from( "salesVisits" )
//    ->where( "sale_id", $requestData->operation_id );
//
//foreach ( $visits as $visit ) {
//
//    $API->DB->update( "visits" )
//        ->set( [
//            "is_payed" => "Y"
//        ] )
//        ->where( "id", $visit[ "visit_id" ] );
//
//}