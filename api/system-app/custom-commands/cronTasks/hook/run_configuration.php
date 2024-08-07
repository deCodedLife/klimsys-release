<?php

$formFieldsUpdate[ "start_at" ][ "is_visible" ] = false;
$formFieldsUpdate[ "days" ][ "is_visible" ] = false;
$formFieldsUpdate[ "hours" ][ "is_visible" ] = false;
$formFieldsUpdate[ "minutes" ][ "is_visible" ] = false;
$formFieldsUpdate[ "month" ][ "is_visible" ] = false;

switch ( $requestData->run_configuration ) {

    case "once":
        $formFieldsUpdate[ "start_at" ][ "is_visible" ] = true;
        break;

    case "period":
        $formFieldsUpdate[ "days" ][ "is_visible" ] = true;
        $formFieldsUpdate[ "hours" ][ "is_visible" ] = true;
        $formFieldsUpdate[ "minutes" ][ "is_visible" ] = true;
        $formFieldsUpdate[ "month" ][ "is_visible" ] = true;
        break;

}

$API->returnResponse( $formFieldsUpdate );