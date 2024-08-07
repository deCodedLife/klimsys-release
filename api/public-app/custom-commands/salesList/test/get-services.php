<?php

/**
 * Получение детальной информации об услуге
 *
 * @param $serviceID
 * @return mixed
 */

function getServiceDetails( $serviceID ) {

    global $API;
    return $API->DB->from( "services" )
        ->where( "id", $serviceID )
        ->fetch();

} // function. getServiceDetails $serviceID



/**
 * Получение информации обо всех услугах в посещениях
 */

foreach ( $saleVisits as $saleVisit ) {

    $visitServices = $API->DB->from( "visits_services" )
        ->where( "visit_id", $saleVisit[ "id" ] );

    foreach ( $visitServices as $visitService ) {
        $saleServices[] = getServiceDetails($visitService["service_id"]);
        $allServices[] = end( $saleServices );
    }

} // foreach. $saleVisits as $saleVisit



/**
 * Если тип операции - "возврат", тогда собирается информация только о
 * прикреплённых к данной операции услугах
 */

if ( $is_return ) {

    $saleServices = [];

    $soldSales = $API->DB->from( "salesServices" )
        ->where( "sale_id", $requestData->id );

    foreach ( $requestData->pay_object as $index => $saleID ) {

        $details = getServiceDetails( $saleID );

        foreach ( $soldSales as $soldSale ) {

            if ( $saleID != $soldSale[ "service_id" ] ) continue;
            $details[ "price" ] = $soldSale[ "price" ];

        }

        $saleServices[] = $details;

    }

    foreach ( $allServices as $index => $sale ) {

        foreach ( $soldSales as $soldSale ) {

            if ( $sale[ "id" ] != $soldSale[ "service_id" ] ) continue;
            $sale[ "price" ] = $soldSale[ "price" ];
            $allServices[ $index ] = $sale;

        }

    }

} // if. $is_return