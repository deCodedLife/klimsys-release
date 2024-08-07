<?php

if ( $API::$userDetail->role_id == 1 ) {
    unset( $response[ "data" ][ 1 ] );
}

$response[ "data" ] = array_values( $response[ "data" ] );