<?php

/**
 * Кастомизация выпадающего списка клиентов
 */

/**
 * Сформированный массив клиентов
 */

$clients = $API->DB->from( "clients" )
    ->innerJoin( "visits_clients on visits_clients.client_id = clients.id" )
    ->where( "visits_clients.visit_id", $pageDetail[ "row_id" ] );

foreach ( $clients as $client ) {

    $clientDetail = $API->DB->from( "clients" )
        ->where("id", $client[ "id" ])
        ->limit(1)
        ->fetch();

    if ( $clientDetail[ "phone" ] ) {
//            $API->returnResponse($clientDetail[ "phone" ] );

        $phoneFormat = ", +" . sprintf("%s (%s) %s-%s-%s",
                substr($clientDetail[ "phone" ], 0, 1),
                substr($clientDetail[ "phone" ], 1, 3),
                substr($clientDetail[ "phone" ], 4, 3),
                substr($clientDetail[ "phone" ], 7, 2),
                substr($clientDetail[ "phone" ], 9)
            );

    } else {

        $phoneFormat = ", +" . sprintf("%s (%s) %s-%s-%s",
                substr($clientDetail[ "second_phone" ], 0, 1),
                substr($clientDetail[ "second_phone" ], 1, 3),
                substr($clientDetail[ "second_phone" ], 4, 3),
                substr($clientDetail[ "second_phone" ], 7, 2),
                substr($clientDetail[ "second_phone" ], 9)
            );

    }

    $clientsInfo[] = [
        "link" => "clients/card/{$client[ "id" ]}",
        "title" => "№{$client[ "id" ]} {$client[ "last_name" ]} {$client[ "first_name" ]} {$client[ "patronymic" ]}$phoneFormat"
    ];

} // foreach. $clients


/**
 * Переназначение значений списка
 */
$generatedTab[ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 1 ][ "fields" ][ 3 ][ "is_visible" ] = true;
$generatedTab[ "settings" ][ "areas" ][ 0 ][ "blocks" ][ 1 ][ "fields" ][ 3 ][ "value" ] = $clientsInfo;
$generatedTab[ "components" ][ "buttons" ][ 0 ][ "settings" ][ "context" ][ "owner_id" ] = $pageDetail[ "row_detail" ][ "user_id" ]->value;


//$API->returnResponse( $generatedTab );