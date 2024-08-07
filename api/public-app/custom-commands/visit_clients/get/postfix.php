<?php

/**
 * @file
 * Формирование списка клиентов посещавшие специалистов
 */

if ( !$requestData->user_id ) $API->returnResponse( [] );

foreach ( $response[ "data" ] as $key => $visit ) {

    $visit[ "period" ] = date( 'Y-m-d H:i', strtotime( $visit[ "start_at" ] ) ) . " - " . date( "H:i", strtotime( $visit[ "end_at" ] ) );
    
    $response[ "data" ][ $key ] = $visit;

}

