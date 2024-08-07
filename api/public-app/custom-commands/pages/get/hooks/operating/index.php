<?php

$pageScheme[ "structure" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date("Y-m-d") . " 00:00:00"
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' ) . " 23:59:59"
    ],
    [
        "property" => "cabinet",
        "value" => "operating"
    ]
];

$stores = $API->DB->from( "stores" )
    ->where( "is_operating", "Y" );

$storesList = [];

foreach ( $stores as $store ) {

    $storesList[] = [

        "title" => $store[ "title" ],
        "value" => $store[ "id" ]

    ];

}

$pageScheme[ "structure" ][ 0 ][ "components" ][ "filters" ][ 3 ][ "settings" ][ "list" ] = $stores;


