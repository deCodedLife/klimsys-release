<?php

if ( !$requestData->object ) $API->returnResponse( [] );

$system = scandir( $API::$configs[ "paths" ][ "system_command_schemes" ] . "/$requestData->object" );
if ( !$system ) $system = [];

$public = scandir( $API::$configs[ "paths" ][ "public_command_schemes" ] . "/$requestData->object" );
if ( !$public ) $public = [];

$commands = array_merge(
    array_diff( $system, [ "..", "." ] ) ?? [],
    array_diff( $public, [ "..", "." ] ) ?? []
);

$objects = array_unique( $commands );
$response = [
    "status" => 200
];

foreach ( $commands as $object ) {

    $nameParts = explode( '.', $object );
    $nameParts = array_splice( $nameParts, 0, count( $nameParts ) - 1 );

    if ( count( $nameParts ) == 0 ) continue;
    $object = join( '.', $nameParts );

    $response[ "data" ][] = [
        "title" => $object,
        "value" => $object
    ];

}