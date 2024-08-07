<?php

require_once $API::$configs[ "paths" ][ "public_app" ] . "/custom-libs/sales/promotions/index.php";
use Sales\Modifier as Modifier;



$promotion_id = $API->DB->insertInto( "promotions" )
    ->values( [
        "title" => $requestData->title,
        "promotion_type" => $requestData->promotion_type,
        "value" => $requestData->value ?? 0,
        "min_order" => $requestData->min_order,
        "begin_at" => $requestData->begin_at ?? date( 'Y-m-d' ),
        "end_at" => $requestData->end_at,
        "comment" => $requestData->comment,
        "min_check" => $requestData->min_check,
        "valid_period" => $requestData->valid_period,
        "bonus_sum" => $requestData->bonus_sum,
        "type" => $requestData->type,
    ] )
    ->execute();

$stores = $requestData->stores_id ?? [];

foreach ( $stores as $store ) {

    $API->DB->insertInto( "promotionStores" )
        ->values( [
            "promotion_id" => $promotion_id,
            "store_id" => $store
        ] )
        ->execute();

}


foreach ( $requestData->services as $service )
    Modifier::writeModifier( $promotion_id, new Modifier( $service, "services" ) );

foreach ( $requestData->servicesGroups as $serviceGroup )
    Modifier::writeModifier( $promotion_id, new Modifier( $serviceGroup, "services", true ) );

foreach ( $requestData->requiredServices as $service )
    Modifier::writeModifier( $promotion_id, new Modifier( $service, "services", false, true ) );

foreach ( $requestData->requiredServicesGroups as $serviceGroup )
    Modifier::writeModifier( $promotion_id, new Modifier( $serviceGroup, "services", true, true ) );

foreach ( $requestData->excludedServices as $service )
    Modifier::writeModifier( $promotion_id, new Modifier( $service, "services", false, false, true ) );

foreach ( $requestData->excludedServicesGroups as $serviceGroup )
    Modifier::writeModifier( $promotion_id, new Modifier( $serviceGroup, "services", true, false, true ) );

foreach ( $requestData->clientsGroups as $clientGroup )
    Modifier::writeModifier( $promotion_id, new Modifier( $clientGroup, "clients", true ) );


$API->returnResponse();
