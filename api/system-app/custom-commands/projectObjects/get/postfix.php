<?php

$objects = array_merge(
    array_diff( scandir( $API::$configs[ "paths" ][ "system_object_schemes" ] ), [ "..", "." ] ) ?? [],
    array_diff( scandir( $API::$configs[ "paths" ][ "public_object_schemes" ] ), [ "..", "." ] ) ?? []
);

$objects = array_unique( $objects );
$response = [
    "status" => 200
];

foreach ( $objects as $object ) {

    $nameParts = explode( '.', $object );
    $nameParts = array_splice( $nameParts, 0, count( $nameParts ) - 1 );

    if ( count( $nameParts ) == 0 ) continue;
    $object = join( '.', $nameParts );

    $response[ "data" ][] = [
        "title" => $object,
        "value" => $object
    ];

}