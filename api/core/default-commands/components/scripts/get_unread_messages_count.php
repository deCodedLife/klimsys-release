<?php

/**
 * @file
 * Получение кол-ва непрочитанных сообщений
 */

foreach ( $response[ "data" ] as $rowKey => $row ) {

    $unreadMessages = $API->DB->from( $objectScriptSettings[ "messages_object" ] )
        ->select( null )->select( [ "id" ] )
        ->where( [
            "is_read" => "N",
            $objectScriptSettings[ "messages_filter" ] => $row[ "id" ]
        ] );

    $response[ "data" ][ $rowKey ][ "unread_messages_count" ] = count( $unreadMessages );

} // foreach. $response[ "data" ]