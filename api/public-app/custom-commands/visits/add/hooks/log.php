<?php


/**
 * Игнорирование исполнителя в логе
 */


/**
 * Получение детальной информации о клиенте
 */

$clientDetail = $API->DB->from( "clients" )
    ->where( "id", $requestData->clients_id[ 0 ] )
    ->limit( 1 )
    ->fetch();

$clientName = $clientDetail[ "last_name" ] . " " . mb_substr( $clientDetail[ "first_name" ], 0, 1 ) . ". " . mb_substr( $clientDetail[ "patronymic" ], 0, 1 ) . ".";


/**
 * Получение детальной информации об услуге
 */

$serviceDetail = $API->DB->from( "services" )
    ->where( "id", $requestData->services_id[ 0 ] )
    ->limit( 1 )
    ->fetch();


/**
 * Получение детальной информации о сотруднике
 */


$userDetail = $API->DB->from( "users" )
    ->where( "id", $requestData->user_id )
    ->limit( 1 )
    ->fetch();

$userName = $userDetail[ "last_name" ] . " " . mb_substr( $userDetail[ "first_name" ], 0, 1 ) . ". " . mb_substr( $userDetail[ "patronymic" ], 0, 1 ) . ".";



/**
 * Получение специальности сотрудника
 */

$userProfession = "врачу";

$userProfessionDetail = $API->DB->from( "users_professions" )
    ->where( "user_id", $userDetail[ "id" ] )
    ->limit( 1 )
    ->fetch();

if ( $userProfessionDetail ) $userProfessionDetail = $API->DB->from( "professions" )
    ->where( "id", $userProfessionDetail[ "profession_id" ] )
    ->limit( 1 )
    ->fetch();

if ( $userProfessionDetail ) $userProfession = mb_strtolower( $userProfessionDetail[ "title" ] );

$months = [
    "янв.",
    "фев.",
    "мар.",
    "апр.",
    "мая",
    "июн.",
    "июл.",
    "авг.",
    "сен.",
    "окт.",
    "ноя.",
    "дек."
];

$created_at = date( "d" ) . " " . $months[ date( "n" ) - 1 ] . " " . date( "Y" ) . "г" . " " . date( "H:i:s" );

$logDescription = "Добавлено посещение к $userName на $created_at, клиент №" . $requestData->clients_id[ 0 ] . " $clientName услуга " . $serviceDetail[ "title" ];