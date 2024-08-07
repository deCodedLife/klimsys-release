<?php

foreach ( $response[ "data" ] as $key => $visit ) {

    $visit[ "period" ] = date( 'Y-m-d H:i', strtotime( $visit[ "start_at" ] ) ) . " - " . date( "H:i", strtotime( $visit[ "end_at" ] ) );

    foreach ( $visit[ "clients_id" ] as $client_key => $item ) {

        $item[ "href" ] = "clients/update/{$item[ "value" ]}";
        $visit[ "clients_id" ][ $client_key ] = $item;

    }

    $response[ "data" ][ $key ] = $visit;

}

