<?php

foreach ( $response[ "data" ] as $key => $log ) {

    $API->returnResponse( $log );

    if ( empty( $log[ "user_id" ] ) ) {

        unset( $response[ "data" ][ $key ] );
        continue;

    }

    if ( empty( $log[ "user_id" ][ "value" ] ) || $log[ "user_id" ][ "value" ] == 0 ) {

        unset( $response[ "data" ][ $key ] );

    }

}