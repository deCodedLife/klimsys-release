<?php

$userDetails = $API->DB->from( "users" )
    ->where( "id", $requestData->context->user_id )
    ->fetch();

if ( $userDetails[ "role_id" ] == 8 ) {

    $response[ "data" ][ 1 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 1 ][ "fields" ][ 5 ][ "search" ] = "services";

}

$response[ "data" ][ 1 ][ "settings" ][ "areas" ][ 1 ][ "blocks" ][ 0 ][ "fields" ][ 1 ][ "is_required" ] = true;