<?php

/**
 * Кастомизация выпадающего списка клиентов
 */

/**
 * Сформированный массив клиентов
 */

$clients = $API->DB->from( "clients" )
    ->where( "id", $pageDetail[ "row_detail" ][ "client_id" ]->value );

foreach ( $clients as $client ) {

    if ( $client[ "phone" ] ) {

        $phoneFormat = ", +" . sprintf("%s (%s) %s-%s-%s",
                substr($client["phone"], 0, 1),
                substr($client["phone"], 1, 3),
                substr($client["phone"], 4, 3),
                substr($client["phone"], 7, 2),
                substr($client["phone"], 9)
            );

    } else {

        $phoneFormat = "";

    }

    $clientsInfo[] = [
        "link" => "clients/card/{$client[ "id" ]}",
        "title" => "№{$client[ "id" ]} {$client[ "last_name" ]} {$client[ "first_name" ]} {$client[ "patronymic" ]} $phoneFormat"
    ];

} // foreach. $clients


/**
 * Переназначение значений списка
 */
$generatedTab[ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 1 ][ "fields" ][ 3 ][ "is_visible" ] = true;
$generatedTab[ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 1 ][ "fields" ][ 3 ][ "value" ] = $clientsInfo;
$generatedTab[ "components" ][ "buttons" ][ 0 ][ "settings" ][ "context" ][ "owner_id" ] = $pageDetail[ "row_detail" ][ "user_id" ]->value;


//$API->returnResponse( $generatedTab );