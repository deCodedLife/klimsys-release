<?php

shell_exec( "crontab -l > tasks" );
$tasks = file_get_contents( "tasks" );
$tasks = explode( "\n", $tasks );

foreach ( $tasks as $key => $task ) {

    $task = explode( "{$API::$configs[ "company" ]} $requestData->id", $task );
    if ( count( $task ) == 1 ) continue;
    unset( $tasks[ $key ] );

}
file_put_contents( "tasks", join( "\n", $tasks ) );
shell_exec( "crontab < tasks | rm tasks" );