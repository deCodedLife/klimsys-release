<?php

global $insertId;

$execCommand = [
    "/opt/php83/bin/php",
    "{$_SERVER[ "DOCUMENT_ROOT" ]}/index.php",
    $_SERVER[ "DOCUMENT_ROOT" ],
    $API::$configs[ "company" ],
    $insertId,
    $_SERVER[ "HTTP_HOST" ],
    $API->request->jwt
];
$execCommand = join( ' ', $execCommand );

$requestData->minutes = $requestData->minutes ?? "*";
$requestData->hours = $requestData->hours ?? "*";
$requestData->days = $requestData->days ?? "*";
$requestData->month = $requestData->month ?? "*";
$requestData->weekdays = $requestData->weekdays ?? "*";

shell_exec( "crontab -l > tasks" );
$cronJobs = file_get_contents( "tasks" );
$cronJobs .= "$requestData->minutes $requestData->hours $requestData->days $requestData->month $requestData->weekdays     $execCommand\n";
file_put_contents( "tasks", $cronJobs );
shell_exec( "crontab < tasks | rm tasks" );