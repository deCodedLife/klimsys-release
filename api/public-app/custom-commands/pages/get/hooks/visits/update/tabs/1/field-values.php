<?php

/**
 * Получение информации об услугах
 */

if ( $API->validatePermissions( [ "manager_schedule", "director_schedule" ], true ) ) {

    $servicesInfo = [];
    $visitServices = [];
    $isPayed = false;
    $visitsList = [];


    /**
     * Заполнение описаний полей "Списать бонусов" и "Списать с депозита"
     */

    $client = $API->DB->from( "clients" )
        ->where( "id", $pageDetail[ "row_detail" ][ "clients_id" ][0]->value )
        ->fetch();


    $generatedTab[ "settings" ][ "areas" ][ 1 ][ "blocks" ][ 0 ][ "fields" ][ 2 ][ "description" ] = "Ваш баланс: " . number_format( $client[ "deposit" ] , 0, '.', ' ' ) . " бонусов";
    $generatedTab[ "settings" ][ "areas" ][ 1 ][ "blocks" ][ 0 ][ "fields" ][ 1 ][ "description" ] = "Ваш баланс: " . number_format( $client[ "bonuses" ], 0, '.', ' ' ) . " ₽";


    /**
     * Получение информации о юр лице клиента
     */

    $clientEntity = $API->DB->from( "legal_entity_clients" )
        ->where( "client_id", $client[ "id" ] )
        ->fetch();

    if ( $clientEntity ) {

        $legalEntity = $API->DB->from( "legal_entities" )
            ->where( "id", $clientEntity[ "legal_entity_id" ] )
            ->fetch();

        $generatedTab[ "settings" ][ "areas" ][ 1 ][ "blocks" ][ 0 ][ "fields" ][ 3 ][ "description" ] = "{$client[ "title" ]}: " . number_format( $client[ "balance" ], 0, '.', ' ' ) . " ₽";

    } // if ( $clientEntity )

}