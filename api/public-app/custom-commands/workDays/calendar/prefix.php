<?php

/**
 * Фильтр по Сотруднику
 */
$eventFilter[ "user_id" ] = $requestData->user_id;

if ( $requestData->store_id ) $eventFilter[ "store_id" ] = $requestData->store_id;
if ( $API->isPublicAccount() ) {

    $scheduleRules = $API->DB->from( "workDays" )
        ->where(
            "(
                ( event_from >= :from and event_from < :to ) OR 
                ( event_to > :from and event_to < :to ) OR 
                ( event_from < :from and event_to >= :to )
            )",
            [
                ":from" => $requestData->event_from,
                ":to" => $requestData->event_to
            ])
        ->fetchAll( "id" );

    $requestData->id = array_keys( $scheduleRules );

    $requestData->event_from = date( "Y-m-01", strtotime( $requestData->event_from ) );
    $requestData->event_to = date( "Y-m-t", strtotime( $requestData->event_to ) );

}