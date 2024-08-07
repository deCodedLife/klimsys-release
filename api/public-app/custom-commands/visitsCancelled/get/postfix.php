<?php


foreach ( $response[ "data" ] as $key => $row ) {

    $clientDetails = $API->DB->from( "clients" )
        ->where( "id", $row[ "client_id" ] )
        ->fetch();

    $advertise = $API->DB->from( "advertise" )
        ->where( "id", $clientDetails[ "advertise_id" ] )
        ->fetch();

    $row[ "advertise_id" ] = [
        "value" => $clientDetails[ "advertise_id" ],
        "title" => $advertise[ "title" ]
    ];
    $response[ "data" ][ $key ] = $row;

}