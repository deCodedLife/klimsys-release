<?php

/**
 * @file
 * Отмена записи к врачу
 */

$visitDetails = $API->DB->from( "visits" )
    ->where( "id", $requestData->id )
    ->fetch();

$API->DB->update( "visits" )
    ->set( [
        "is_active" => "N",
        "reason_id" => $requestData->reason_id,
        "cancelledDate" => date( "Y-m-d H:i:s" ),
        "operator" => $API::$userDetail->id
    ] )
    ->where( [
        "id" => $requestData->id
    ] )
    ->execute();

$userDetail = $API->DB->from( "users" )
    ->where( "id", $API::$userDetail->id )
    ->fetch();

$visit_id = $requestData->id;
unset( $requestData->id );

$requestData->user_id = $visitDetails[ "user_id" ];
$requestData->client_id = $visitDetails[ "client_id" ];


$date = date( "d.m.Y H:i" , strtotime( $visitDetails[ "start_at" ] ) );

$API->addLog( [
    "table_name" => $objectScheme[ "table" ],
    "description" => "Посещение на $date №$visit_id удалено пользователем " . $userDetail[ "last_name" ],
    "row_id" => $visit_id
], $requestData );


$API->addEvent( "schedule" );
$API->addEvent( "day_planning" );
