<?php

/**
 * @file
 * Хуки на Добавление правила
 */
$formFieldsUpdate = [];

if ( $requestData->is_weekend === 'Y' ) {

    $formFieldsUpdate[ "event_from" ][ "is_visible" ] = false;
    $formFieldsUpdate[ "event_to" ][ "is_visible" ] = false;

} else {

    $formFieldsUpdate[ "event_from" ][ "is_visible" ] = true;
    $formFieldsUpdate[ "event_to" ][ "is_visible" ] = true;

} // if ( $requestData->is_weekend === 'Y' )


$API->returnResponse( $formFieldsUpdate );
