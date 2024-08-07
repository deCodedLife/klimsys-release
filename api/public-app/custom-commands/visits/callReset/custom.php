<?php

/**
 * @file
 * Отмена записи к врачу
 */

$API->DB->update( "visits" )
    ->set( [
        "is_called" => "N"
    ] )
    ->where( [
        "id" => $requestData->id
    ] )
    ->execute();
