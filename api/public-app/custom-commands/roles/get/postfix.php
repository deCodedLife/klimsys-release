<?php

if ( $requestData->context->block === "chat" ) {

    $chatContext = [];

    foreach ( $response[ "data" ] as $role ) {

        $chatContext[] = [
            "id" => $role[ "id" ],
            "title" => $role[ "title" ]
        ];

    }

    $response[ "data" ] = $chatContext;

}