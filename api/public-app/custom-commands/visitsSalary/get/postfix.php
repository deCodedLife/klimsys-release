<?php

//ini_set( "display_errors", true );

$sqlFilter = [];
$visits_ids = [];

foreach ( $response[ "data" ] as $visit ) $visits_ids[] = $visit[ "id" ];
if ( empty( $visits_ids ) ) $API->returnResponse( [] );

if ( $requestData->category ) $sqlFilter[ "salesProductsList.product_id" ] = visits\getServicesIds( $requestData->category );
if ( $requestData->service )  $sqlFilter[ "salesProductsList.product_id" ] = $requestData->service;
$sqlFilter[ "saleVisits.visit_id" ] = $visits_ids;
$sqlFilter[ "salesList.action" ] = "sell";
$sqlFilter[ "salesList.status" ] = "done";

$relations = $API->DB->from( "salesProductsList" )
    ->innerJoin( "salesList on salesList.id = salesProductsList.sale_id" )
    ->select( [ "salesList.summary" ] )
    ->innerJoin( "saleVisits on saleVisits.sale_id = salesList.id" )
    ->select( [ "saleVisits.visit_id as visit_id" ] )
    ->where( $sqlFilter )
    ->fetchAll( "visit_id[]" );

$sqlFilter = [];

$visitServices = $API->DB->from( "visits_services" )
    ->select( null )
    ->select( [ "service_id", "visit_id" ] )
    ->where( "visit_id", $visits_ids )
    ->fetchAll( "visit_id[]" );

foreach ( $visitServices as $key => $serviceVisits ) {

    $services = array_map( fn( $item ) => $item[ "service_id" ], $serviceVisits );
    $visitServices[ $key ] = $services;

}


if ( $requestData->service ) $sqlFilter[ "services.id" ] = $requestData->service;
if ( $requestData->category ) $sqlFilter[ "services.category_id" ] = $requestData->category;
$sqlFilter[ "visits_services.visit_id" ] = $visits_ids;

$sales_percent = [];
$sales_fixed = [];

/**
 * Получение списка kpi по услугам
 */
$userServices = $API->DB->from( "services_user_percents" )
    ->where( "row_id", $requestData->context->user_id );

foreach ( $userServices as $service ) {

    if ( $service[ "percent" ] ) {

        $sales_percent[ $service[ "service_id" ] ] = intval( $service[ "percent" ] );
        continue;

    }

    if ( $service[ "fix_sum" ] ) {

        $sales_fixed[ $service[ "service_id" ] ] = intval( $service[ "fix_sum" ] );

    }

}




foreach ( $response[ "data" ] as $key => $visit ) {

    $visit[ "period" ] = date( 'Y-m-d H:i', strtotime( $visit[ "start_at" ] ) ) . " - " . date( "H:i", strtotime( $visit[ "end_at" ] ) );

    $services = $relations[ $visit[ "id" ] ];
    $userServices = $visitServices[ $visit[ "id" ] ];
    $limit = 0;

    /**
     * Фильтр услуг в чеке к услугам посещения
     */
    foreach ( $services as $index => $service ) {

        if ( in_array( $service[ "product_id" ], $userServices ) ) {
            /**
             * Ограничение на дублирование одинаковых услуг
             */
            if ( $limit < count( $userServices ) )
            {
                $limit++;
                continue;
            }
        };
        unset( $services[ $index ] );

    }

    if ( !$services ) {
        $visit[ "summary" ] = $visit[ "price" ];
        $visit[ "percent" ] = 0;
        $response[ "data" ][ $key ] = $visit;
        continue;
    }

    $total = 0;
    $summary = $services[ 0 ][ "summary" ];
    $servicesList = [];

    foreach ( $services as $service ) {

        $serviceID = intval( $service[ "product_id" ] );

        if (
            property_exists( $requestData, "service" ) &&
            !empty( $requestData->service ) &&
            $serviceID != $requestData->service[ 0 ]
        ) continue;


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

    $visit[ "services_id" ] = $servicesList;
    $visit[ "summary" ] = $summary;
    $visit[ "percent" ] = $total;

    $response[ "data" ][ $key ] = $visit;

}