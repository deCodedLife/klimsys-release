<?php

if (
    !empty( $requestData ) &&
    property_exists( $requestData, "context" ) &&
    property_exists( $requestData->context, "block") &&
    $requestData->context->block === "list"
) {
    foreach ( $response[ "data" ] as $key => $item ) {

        $item = (array) $item;
        $manufacturer = $item[ "manufacturer" ][ "value" ];

        if ( empty( $manufacturer ) ) continue;
        $manufacturerDetails = $API->DB->from( "providers" )
            ->where( "id", $manufacturer )
            ->fetch();

        $item[ "category_type" ] = $item[ "product_type" ][ "value" ];
        unset( $item[ "product_type" ] );
        $item[ "price" ] = $item[ "price" ] * $manufacturerDetails[ "exchange_rate" ] * $manufacturerDetails[ "coefficient" ];
        $response[ "data" ][ $key ] = $item;

    }
}

