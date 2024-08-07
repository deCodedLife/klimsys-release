<?php

shell_exec( "crontab -l > tasks" );
$tasks = file_get_contents( "tasks" );
$tasks = explode( "\n", $tasks );

$taskDetails = $API->DB->from( "cronTasks" )
    ->where( "id", $requestData->id )
    ->fetch();

foreach ( $tasks as $key => $task ) {

    $task = explode( "{$API::$configs[ "company" ]} {$taskDetails[ "id" ]}", $task );
    if ( count( $task ) == 1 ) continue;
    unset( $tasks[ $key ] );

}
file_put_contents( "tasks", join( "\n", $tasks ) );
shell_exec( "crontab < tasks | rm tasks" );

$execCommand = [
    "/opt/php83/bin/php",
    "{$_SERVER[ "DOCUMENT_ROOT" ]}/index.php",
    $_SERVER[ "DOCUMENT_ROOT" ],
    $API::$configs[ "company" ],
    $taskDetails[ "id" ],
    $_SERVER[ "HTTP_HOST" ],
    $API->request->jwt
];
$execCommand = join( ' ', $execCommand );

$minutes = $taskDetails[ "minutes" ] ?? "*";
$hours = $taskDetails[ "hours" ] ?? "*";
$days = $taskDetails[ "days" ] ?? "*";
$month = $taskDetails[ "month" ] ?? "*";
$weekdays =  "*";

shell_exec( "crontab -l > tasks" );
$cronJobs = file_get_contents( "tasks" );
$cronJobs .= "$minutes $hours $days $month $weekdays     $execCommand\n";
file_put_contents( "tasks", $cronJobs );
shell_exec( "crontab < tasks | rm tasks" );