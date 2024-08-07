<?php


if (
    $requestData->context->block === "list"
) {
    foreach ( $response[ "data" ] as $key => $item ) {

        $item = (array) $item;
        $manufacturer = $item[ "manufacturer" ];

        if ( empty( $manufacturer ) ) continue;
        $manufacturerDetails = $API->DB->from( "providers" )
            ->where( "id", $manufacturer )
            ->fetch();

        $item[ "price" ] = $item[ "price" ] * $manufacturerDetails[ "exchange_rate" ] * $manufacturerDetails[ "coefficient" ];
        $response[ "data" ][ $key ] = $item;

    }
}

