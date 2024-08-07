<?php

/**
 * Если тип операции - "возврат", тогда собирается информация только о
 * прикреплённых к данной операции услугах
 */

if ( $isReturn ) {

    $saleServices = [];

    $soldSales = $API->DB->from( "salesProductsList" )
        ->where( [
            "sale_id" => $requestData->id,
            "type" => "service"
        ] );

    foreach ( $requestData->return_services as $saleID ) {

        $details = $Doca->getServiceDetails( $saleID );

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

} // if isReturn