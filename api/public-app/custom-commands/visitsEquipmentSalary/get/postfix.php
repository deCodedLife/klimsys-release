<?php

$sqlFilter = [];
$visits_ids = [];
$relations = [];

foreach ( $response[ "data" ] as $visit ) $visits_ids[] = $visit[ "id" ];
if ( empty( $visits_ids ) ) $API->returnResponse( [] );

if ( $requestData->category ) $sqlFilter[ "salesProductsList.product_id" ] = visits\getServicesIds( $requestData->category );
if ( $requestData->service )  $sqlFilter[ "salesProductsList.product_id" ] = $requestData->service;
$sqlFilter[ "salesEquipmentVisits.visit_id" ] = $visits_ids;
$sqlFilter[ "salesList.action" ] = "sell";
$sqlFilter[ "salesList.status" ] = "done";

$allServices = $API->DB->from( "salesProductsList" )
    ->innerJoin( "salesList on salesList.id = salesProductsList.sale_id" )
    ->select( [ "salesList.summary" ] )
    ->innerJoin( "salesEquipmentVisits on salesEquipmentVisits.sale_id = salesList.id" )
    ->select( [ "salesEquipmentVisits.visit_id as visit_id" ] )
    ->where( $sqlFilter );
$sqlFilter = [];



foreach ( $allServices as $visitsService ) {

    $relations[ $visitsService[ "visit_id" ] ][] = $visitsService;

}


$sales_percent = [];
$sales_fixed = [];

/**
 * Получение списка kpi по услугам
 */
$userServices = $API->DB->from( "services_user_percents" )
    ->where( "row_id", $requestData->context->user_id );


foreach ( $userServices as $service ) {

    if ( $service[ "percent" ] ) {

        $sales_percent[ intval( $service[ "service_id" ] ) ] = intval( $service[ "percent" ] );
        continue;

    }

    if ( $service[ "fix_sum" ] ) {

        $sales_fixed[ intval( $service[ "service_id" ] ) ] = intval( $service[ "fix_sum" ] );

    }

}

//$API->returnResponse( [ $sales_percent, $sales_fixed ] );


foreach ( $response[ "data" ] as $key => $visit ) {

    $visit[ "period" ] = date( 'Y-m-d H:i', strtotime( $visit[ "start_at" ] ) ) . " - " . date( "H:i", strtotime( $visit[ "end_at" ] ) );

    $services = $relations[ intval( $visit[ "id" ] ) ];
//    $API->returnResponse( $visit );

    if ( !$services ) {
        $visit[ "summary" ] = $visit[ "price" ];
        $visit[ "percent" ] = 0;
        $response[ "data" ][ $key ] = $visit;
        continue;

//        $sula = $API->DB->from( "saleVisits" )
//            ->innerJoin( "salesProductsList on salesProductsList.sale_id = saleVisits.sale_id" )
//            ->select( "salesProductsList.title as title" )
//            ->where( "saleVisits.visit_id", $visit[ "id" ] );
//
//        foreach ( $sula as $row ) $data[] = $row;
//        $API->returnResponse( $data ?? $visit[ "id" ] );
//
//        $serviceDetail = $API->DB->from( "services" )
//            ->where( "id", $visit[ "service_id" ][ "value" ] )
//            ->fetch();
//
//        if ( !$serviceDetail ) continue;
//        $services[] = [
//            "title" => $serviceDetail[ "title" ],
//            "product_id" => $serviceDetail[ "id" ],
//            "cost" => intval( $serviceDetail[ "price" ] ),
//            "amount" => 1,
//            "summary" => intval( $serviceDetail[ "price" ] )
//        ];

    }

    $total = 0;
    $summary = $services[ 0 ][ "summary" ];
    $servicesList = [];

    foreach ( $services as $service ) {

        $serviceID = intval( $service[ "product_id" ] );

        $servicesList[] = [
            "title" => $service[ "title" ],
            "value" =>  $serviceID
        ];



        if ( isset( $sales_percent[ $serviceID ] ) ) {

            $servicePercent = $sales_percent[ $serviceID ];
            $servicesDetail = visits\getFullService( $service[ "product_id" ], $user_id );

            $price = $servicesDetail[ "price" ]; //intval( $service[ "cost" ] * $service[ "amount" ] );
            $total += $price / 100 * $servicePercent;
            continue;

        }

        if ( isset( $sales_fixed[ $serviceID ] ) ) {

            $servicePercent = $sales_fixed[ $serviceID ];
            $total += $servicePercent;

        }

    }

//    $visit[ "service_id" ] = $visit[ "service_id" ];
    $visit[ "summary" ] = $summary;
    $visit[ "percent" ] = $total;

    $response[ "data" ][ $key ] = $visit;

}