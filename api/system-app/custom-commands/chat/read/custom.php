<?php

/**
 * @file
 * Чтение сообщений
 */


$API->DB->update( $requestData->messages_object )
    ->set( [
        "is_readed" => "Y"
    ] )
    ->where( "id", $requestData->id )
    ->execute();


$API->returnResponse( true );