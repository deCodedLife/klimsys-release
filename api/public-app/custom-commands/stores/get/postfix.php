<?php

$role = $API->DB->from( "roles" )
    ->where( "id", $API::$userDetail->role_id )
    ->limit(1)
    ->fetch()[ "article" ];

if ( $role == "public" ) {

    $siteStores = [];

    foreach ( $response[ "data" ] as $store ) {

        $siteStores[] = [

            "id" => $store[ "id" ],
            "title" => $store[ "title" ],
            "schedule_to" => $store[ "schedule_to" ],
            "schedule_from" => $store[ "schedule_from" ],

        ];

    }

    $response[ "data" ] = $siteStores;

}