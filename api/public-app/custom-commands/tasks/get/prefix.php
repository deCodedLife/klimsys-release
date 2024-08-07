<?php

/**
 * Фильтры
 */

if ( $requestData->deadline ) {

    $requestSettings[ "filter" ][ "deadline >= ?" ] = $requestData->deadline . " 00:00:00";
    $requestSettings[ "filter" ][ "deadline <= ?" ] = $requestData->deadline . " 23:59:59";

    unset( $requestData->deadline );

} // if. $requestData->deadline

$requestData->sort_by = "id";
$requestData->sort_order = "desc";