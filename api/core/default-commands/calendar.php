<?php

/**
 * Сформированный список событий
 */
$eventsList = [];

/**
 * Фильтр событий
 */
if ( !isset( $eventFilter ) ) $eventFilter = [];


/**
 * Формирование фильтра событий
 */

$eventFilter[ "event_from >= ?" ] = date( "Y-m-01" ) . " 00:00:00";
$eventFilter[ "event_from <= ?" ] = date( "Y-m-t" ) . " 23:59:59";

if ( $requestData->event_from ) $eventFilter[ "event_from >= ?" ] = $requestData->event_from . " 00:00:00";
if ( $requestData->event_to ) $eventFilter[ "event_from <= ?" ] = $requestData->event_to . " 23:59:59";


/**
 * Формирование списка событий
 */

$events = $API->DB->from( $API->request->object )
    ->where( $eventFilter );

foreach ( $events as $event ) {

    /**
     * Дата события
     */
    $eventDate = date( "Y-m-d", strtotime( $event[ "event_from" ] ) );

    /**
     * Время события
     */
    $eventTime_from = date( "H:i", strtotime( $event[ "event_from" ] ) );
    $eventTime_to = date( "H:i", strtotime( $event[ "event_to" ] ) );


    $eventsList[ $eventDate ][] = [
        "id" => $event[ "id" ],
        "title" => "$eventTime_from - $eventTime_to",
        "from" => $eventTime_from,
        "to" => $eventTime_to,
        "background" => "primary"
    ];

} // foreach. $events


$response[ "data" ] = $eventsList;