<?php

/**
 * @file
 * Завершение записи к врачу
 */

$API->DB->update( "visits" )
    ->set( "status", "ended" )
    ->where( [
        "id" => $requestData->id
    ] )
    ->execute();

$visitDetail = $API->DB->from( "visits" )
    ->where( [
        "id" => $requestData->id,
    ] )
    ->limit( 1 )
    ->fetch();

$services = $API->DB->from( "visits_services" )
    ->where( [
        "visit_id" => $requestData->id,
    ] );

$visitDetail = $API->DB->from( "visits" )
    ->where( "id", $requestData->id)
    ->limit( 1 )
    ->fetch();


$API->addLog( [
    "table_name" => "visits",
    "description" => "Клиент вышел из кабинета (" . date("d.m.Y H:i") . ")",
    "row_id" => $visitDetail[ "id" ]
], $requestData );

foreach ( $services as $service ) {

    /**
     * Получение расходников для услуги
     */
    $services_consumables = $API->DB->from( "services_consumables" )
        ->where( "row_id", $service[ "service_id" ] );

    foreach ( $services_consumables as $service_consumable ) {


        $warehouse = $API->DB->from( "warehouses" )
            ->where( [
                "store_id" => $visitDetail[ "store_id" ],
                "consumable_id" => $service_consumable[ "consumable_id" ]
            ] )
            ->limit( 1 )
            ->fetch();

        $API->DB->update( "warehouses" )
            ->set( "count", $warehouse[ "count" ] - $service_consumable[ "count" ] )
            ->where( [
                "store_id" => $visitDetail[ "store_id" ],
                "consumable_id" => $service_consumable[ "consumable_id" ]
            ] )
            ->execute();

    }

}

$API->addEvent( "schedule" );
$API->addEvent( "day_planning" );
