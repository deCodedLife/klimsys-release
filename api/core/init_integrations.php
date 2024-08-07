<?php

/**
 * Подключение интеграций
 */

if ( $API::$configs[ "integrations" ] ) {

    foreach ( $API::$configs[ "integrations" ] as $integration ) {

        if ( file_exists( $API::$configs[ "paths" ][ "core" ] . "/integrations/$integration.php" ) ) {

            require_once( $API::$configs[ "paths" ][ "core" ] . "/integrations/$integration.php" );

        } else {

            $API->returnResponse( "Интеграция $integration не отвечает", 500 );

        } // if. file_exists. /integrations/$integration.php

    } // foreach. $API::$configs[ "integrations" ]

} // if. $API::$configs[ "integrations" ]