<?php

/**
 * @file
 * Получение кол-ва непрочитанных сообщений
 */


/**
 * Фильтр сообщений
 */

$filter = [
    "is_read" => "N"
];

if ( $requestData->filter_object && $requestData->filter_value )
    $filter[ $requestData->filter_object ] = $requestData->filter_value;


/**
 * Получение непрочитанных сообщений
 */
$unreadMessages = $API->DB->from( $requestData->messages_object )
    ->select( null )->select( [ "id" ] )
    ->where( $filter );

$API->returnResponse( count( $unreadMessages ) );