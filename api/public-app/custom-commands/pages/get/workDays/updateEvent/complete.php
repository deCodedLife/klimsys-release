<?php

$eventStart = explode( " ", $response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 0 ][ "fields" ][ 2 ][ "value" ] );
$eventEnd = explode( " ", $response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 0 ][ "fields" ][ 3 ][ "value" ] );

$cabinets = [];
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

/**
 * Обновление состава полей
 */

$response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 1 ][ "blocks" ][ 0 ][ "fields" ][ 2 ][ "list" ] = $cabinets;
$response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 0 ][ "fields" ][ 0 ][ "value" ] = $eventStart[ 0 ];
$response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 0 ][ "fields" ][ 1 ][ "value" ] = $eventEnd[ 0 ];
$response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 0 ][ "fields" ][ 2 ][ "value" ] = $eventStart[ 1 ];
$response[ "data" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 0 ][ "fields" ][ 3 ][ "value" ] = $eventEnd[ 1 ];
