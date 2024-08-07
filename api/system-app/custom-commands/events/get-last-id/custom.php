<?php

/**
 * @file
 * ID последнего события
 */


/**
 * Получение последнего события
 */

$lastEvent = mysqli_fetch_array(
    mysqli_query(
        $API->DB_connection,
        "SELECT * FROM `events` WHERE table_name = '$requestData->table_name' AND ( user_id = '$requestData->user_id' OR user_id IS NULL ) LIMIT 1"
    )
);
if ( !$lastEvent ) $API->returnResponse( 0 );


$API->returnResponse( (int) $lastEvent[ "id" ] );