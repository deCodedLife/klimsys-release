<?php

$productsList = [];
$summary = 0;

foreach ( $requestData->context->object->services_id as $service ) {

    $service = visits\getFullService(
        $service->value,
        $requestData->context->object->user_id->value
    );
    $productsList[] = $service;
    $summary += $service[ "price" ];

}

//$sale_id = $API->DB->from( "saleVisits" )
//    ->where( "visit_id", $requestData )

$saleDetails = $API->DB->from( "salesList" )
    ->innerJoin( "saleVisits on saleVisits.sale_id = salesList.id" )
    ->where( [
        "saleVisits.visit_id" => $requestData->id,
        "salesList.action" => "sell"
    ] )
    ->orderBy( "saleVisits.id DESC" )
    ->fetch();

if ( !$saleDetails ) $API->returnResponse( [
    "services_id" => $productsList,
    "price" => $summary
] );


$saleProducts = $API->DB->from( "salesProductsList" )
    ->where( "sale_id", $saleDetails[ "id" ] );

$productsList = [];

foreach ( $saleProducts as $saleProduct ) {

    $service = visits\getFullService( $saleProduct[ "product_id" ] );

    $service[ "title" ] = $saleProduct[ "title" ];
    $service[ "price" ] = $saleProduct[ "cost" ];
    $service[ "discount" ] = round( $saleProduct[ "discount" ], 2 );
    $service[ "with_discount" ] = round( $saleProduct[ "cost" ] - $saleProduct[ "discount" ], 2 );

    $productsList[] = $service;

}

$API->returnResponse( [
    "services_id" => $productsList,
    "price" => $saleDetails[ "sum_deposit" ] + $saleDetails[ "sum_card" ] + $saleDetails[ "sum_entity" ] + $saleDetails[ "sum_cash" ]
] );