<?php

/**
 * Изменение статуса оплаты
 */

$API->DB->update( "salesList" )
    ->set( "status", "done" )
    ->where( "id", $requestData->sale_id )
    ->execute();

/**
 * Списание бонусов и депозита
 */

$saleDetails = $API->DB->from( "salesList" )
    ->where( "id", $requestData->sale_id )
    ->fetch();

/**
 * Изменение статуса посещений
 */

$sale_visits = $API->DB->from( "saleVisits" )
    ->where( "sale_id", $requestData->sale_id );

$API->DB->update( "visits" )
    ->innerJoin( "saleVisits ON saleVisits.visit_id = visits.id" )
    ->set( "visits.is_payed", $saleDetails[ "action" ] == "sellReturn" ? 'N' : 'Y' )
    ->where( "saleVisits.sale_id", $requestData->sale_id )
    ->execute();

$API->DB->update( "equipmentVisits" )
    ->innerJoin( "salesEquipmentVisits ON salesEquipmentVisits.visit_id = equipmentVisits.id" )
    ->set( "equipmentVisits.is_payed", $saleDetails[ "action" ] == "sellReturn" ? 'N' : 'Y' )
    ->where( "salesEquipmentVisits.sale_id", $requestData->sale_id )
    ->execute();


$clientDetails = $API->DB->from( "clients" )
    ->where( "id", $saleDetails[ "client_id" ] )
    ->fetch();



if ( $saleDetails[ "action" ] == "sell" ) {

    $API->DB->update( "clients" )
        ->set( [
            "bonuses" => $clientDetails[ "bonuses" ] - ( $saleDetails[ "sum_bonus" ] ?? 0 ),
            "deposit" => $clientDetails[ "deposit" ] - ( $saleDetails[ "sum_deposit" ] ?? 0 ),
        ] )
        ->where( "id", $saleDetails[ "client_id" ] )
        ->execute();

    $API->DB->insertInto( "bonusHistory" )
        ->values( [
            "user_id" => $API::$userDetail->id ?? 1,
            "client_id" => $saleDetails[ "client_id" ],
            "action" => "Пополнение",
            "replenished" => $saleDetails[ "sum_bonus" ]
        ] )
        ->execute();

} // if ( $saleDetails[ "action" ] == "sell" )



if ( $saleDetails[ "action" ] == "deposit" ) {

    $API->DB->update( "clients" )
        ->set( [
            "deposit" => $clientDetails[ "deposit" ] + $saleDetails[ "summary" ]
        ] )
        ->where( "id", $saleDetails[ "client_id" ] )
        ->execute();

} // if ( $saleDetails[ "action" ] == "deposit" )



if ( $saleDetails[ "action" ] == "sellReturn" ) {

    $API->DB->update( "clients" )
        ->set( [
            "bonuses" => $clientDetails[ "bonuses" ] + ( $saleDetails[ "sum_bonus" ] ?? 0 ),
            "deposit" => $clientDetails[ "deposit" ] + ( $saleDetails[ "sum_deposit" ] ?? 0 )
        ] )
        ->where( "id", $saleDetails[ "client_id" ] )
        ->execute();

    $API->DB->insertInto( "bonusHistory" )
        ->values( [
            "user_id" => $API::$userDetail->id ?? 1,
            "client_id" => $saleDetails[ "client_id" ],
            "action" => "Списание",
            "replenished" => -$saleDetails[ "sum_bonus" ]
        ] )
        ->execute();

} // if ( $saleDetails[ "action" ] == "sellReturn" )


/**
 * Получение детальной информации о клиенте
 */

$clientDetail = $API->DB->from( "clients" )
    ->where( "id", $saleDetails[ "client_id" ] )
    ->limit( 1 )
    ->fetch();

$clientName = $clientDetail[ "last_name" ] . " " . mb_substr( $clientDetail[ "first_name" ], 0, 1 ) . ". " . mb_substr( $clientDetail[ "patronymic" ], 0, 1 ) . ".";

/**
 * Получение детальной информации о сотруднике
 */

$visitID = $API->DB->from( "saleVisits" )
    ->where( "sale_id", $saleDetails[ "id" ] )
    ->fetch();


$userDetail = $API->DB->from( "users" )
    ->where( "id", $saleDetails[ "employee_id" ] )
    ->limit( 1 )
    ->fetch();

$userName = $userDetail[ "last_name" ] . " " . mb_substr( $userDetail[ "first_name" ], 0, 1 ) . ". " . mb_substr( $userDetail[ "patronymic" ], 0, 1 ) . ".";
$transactionTime = date( "Y-m-d H:i:s" );

$logDescription = "Посещение {$visitID[ "visit_id" ]} оплачено в $transactionTime сотрудником $userName, клиент №{$clientDetails[ "id" ]} $clientName $clientName";


$API->addEvent( "schedule" );
$API->addEvent( "day_planning" );
$API->addEvent( "salesList" );