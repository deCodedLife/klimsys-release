<?php

$userDetails = $API->DB->from( "users" )
    ->where( "id", $requestData->context->user_id )
    ->fetch();

if ( $userDetails[ "role_id" ] == 8 ) {

    $response[ "data" ][ 1 ][ "settings" ][ 0 ][ "body" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 1 ][ "fields" ][ 4 ][ "search" ] = "services";

}

$response[ "data" ][ 1 ][ "settings" ][ 2 ][ "body" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 1 ][ "fields" ][ 0 ][ "script" ] = [
    "object" => "visits",
    "command"=> "update",
    "properties" => [
        "id" => $pageDetail[ "row_detail" ][ "id" ],
        "is_called" => true
    ]
];

$response[ "data" ][ 1 ][ "settings" ][ 2 ][ "body" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 1 ][ "fields" ][ 1 ][ "script" ] = [
    "object" => "visits",
    "command"=> "update",
    "properties" => [
        "id" => $pageDetail[ "row_detail" ][ "id" ],
        "is_called" => true
    ]
];

$settings = $API->DB->from( "settings" )
    ->fetch();

if ( !$settings[ "folder_id" ] ) {

    $button = $response[ "data" ][ 1 ][ "settings" ][ 0 ][ "body" ][ 0 ][ "components" ][ "buttons" ][ 1 ];
    $button[ "type" ] = "script";
    $button[ "settings" ][ "object" ] = "visits";
    $button[ "settings" ][ "command" ] = "update";
    $button[ "settings" ][ "title" ] = "В клинике";
    $button[ "settings" ][ "data" ] = [
        "id" => $pageDetail[ "row_detail" ][ "id" ],
        "status" => "waited"
    ];

    $response[ "data" ][ 1 ][ "settings" ][ 0 ][ "body" ][ 0 ][ "components" ][ "buttons" ][ 1 ] = $button;
    $response[ "data" ][ 1 ][ "settings" ][ 0 ][ "body" ][ 0 ][ "components" ][ "buttons" ] = array_values(
        $response[ "data" ][ 1 ][ "settings" ][ 0 ][ "body" ][ 0 ][ "components" ][ "buttons" ]
    );

}

//$pageScheme[ "data" ][ 1 ][ "settings" ][ 1 ][ "body" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 0 ][ "fields" ][ 4 ][ "is_visible" ] = true;
//$pageScheme[ "data" ][ 1 ][ "settings" ][ 1 ][ "body" ][ 0 ][ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 0 ][ "fields" ][ 5 ][ "is_visible" ] = true;