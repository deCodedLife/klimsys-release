<?php

if ( count( $argv ?? [] ) > 1 ) {

    $cronTask = $API->DB->from( "cronTasks" )
        ->where( "id", intval( $argv[ 3 ] ) )
        ->fetch();

    $_SERVER[ "HTTP_HOST" ] = $argv[ 4 ];

    if ( $cronTask ) {

        $cronArgv = $API->DB->from( "cronArgv" )
            ->where( "row_id", intval( $argv[ 3 ] ) )
            ->fetchAll();

        foreach ( $cronArgv as $property )
            $request[ "data" ][ $property[ "property" ] ] = $property[ "value" ];

        $API->request = (object) [];
        $API->request->jwt = $argv[ 5 ];

        $API->sendRequest( $cronTask[ "object" ], $cronTask[ "command" ], $request ?? [] );
        exit( "done" );

    }

}
