<?php


/**
 * Получение детальной информации о сотруднике
 */


$userDetail = $API->DB->from( "users" )
    ->where( "id", $requestData->employee_id )
    ->limit( 1 )
    ->fetch();

$userName = $userDetail[ "last_name" ] . " " . mb_substr( $userDetail[ "first_name" ], 0, 1 ) . ". " . mb_substr( $userDetail[ "patronymic" ], 0, 1 ) . ".";



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
$visitID = $requestData->visits_ids[ 0 ] ?? 0;
$summary = $requestData->summary;

$logDescription = "Сотрудник $userName инициировал оплату посещения [$visitID] на сумму $summary в $created_at.";