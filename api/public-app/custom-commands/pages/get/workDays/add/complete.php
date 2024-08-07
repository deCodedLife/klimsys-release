<?php

/**
 * Получение филиалов, привязанных к сотруднику
 */

$users_stores = $API->DB->from( "users_stores" )
    ->where( "user_id", $requestData->context->row_id );

$stores = [];
$cabinets = [];

foreach ( $users_stores as $user_store ) {

    $storeDetail = $API->DB->from( "stores" )
        ->where( "id", $user_store[ "store_id" ] )
        ->limit( 1 )
        ->fetch();


    $stores[] = [
        "title" => $storeDetail[ "title" ],
        "value" => $storeDetail[ "id" ]
    ];

} // foreach. $users_stores

foreach ( $response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 1 ][ "blocks" ][ 0 ][ "fields" ][ 2 ][ "list" ] as $cabinet ) {

    $workDays = $API->DB->from( "workDays" )
        ->where( "cabinet_id", $cabinet[ "value" ] )
        ->limit( 1 )
        ->fetch();

    $cabinetDetail = $API->DB->from( "cabinets" )
        ->where( "id", $cabinet[ "value" ] )
        ->limit( 1 )
        ->fetch();

    if ( $workDays[ "cabinet_id" ] != NULL ){

        $title = "№ " . $cabinet[ "title" ] . " ( " . date('d.m',  strtotime($workDays[ "event_from" ]) ) . " " . date('H:i',  strtotime($workDays[ "event_from" ] )) . " - " .  date('H:i',  strtotime($workDays[ "event_to" ]) ) .  " )";

    } else {

        $title = $cabinet[ "title" ];

    }

    if ( $cabinetDetail[ "is_operating" ] == "N" ) {

        $cabinets[] = [
            "title" => $cabinet[ "title" ],
            "value" => $cabinet[ "value" ],
            "menu_title" => $title,
            "joined_field_value" => $cabinet[ "joined_field_value" ],
        ];

    }

} // foreach. $response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 1 ][ "blocks" ][ 0 ][ "fields" ][ 2 ][ "list" ]


if ( !empty( $stores ) ) {

    $response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 1 ][ "blocks" ][ 0 ][ "fields" ][ 1 ][ "list" ] = $stores;
    $response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 1 ][ "blocks" ][ 0 ][ "fields" ][ 1 ][ "value" ] = $stores[ 0 ][ "value" ];

}

$response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 1 ][ "blocks" ][ 0 ][ "fields" ][ 2 ][ "list" ] = $cabinets;
