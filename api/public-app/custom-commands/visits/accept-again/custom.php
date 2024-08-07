<?php

/**
 * @file
 *  Повторный вызов клиента
 */


$API->DB->update( "visits" )
    ->set([
        "is_alert" => "N"
    ])
    ->where([
        "id" => $requestData->id
    ])
    ->execute();

$visitDetail = $API->DB->from( "visits" )
    ->where( "id", $requestData->id)
    ->limit( 1 )
    ->fetch();


$API->addLog( [
    "table_name" => "visits",
    "description" => "Клиент повторно зашел в кабинет (" . date("d.m.Y H:i") . ")",
    "row_id" => $visitDetail[ "id" ]
], $requestData );