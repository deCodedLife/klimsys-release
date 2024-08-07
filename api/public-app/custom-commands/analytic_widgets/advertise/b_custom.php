<?php

/**
 * @file
 * Отчет "Рекламные источники"
 */


/**
 * Статистика клиента
 */
$advertiseStatistic = [

    /**
     * Прибыль
     */
    "cacheFlow" => 0,

];

/**
 * Получение списка статистики рекламных источников
 */
$advertises = $API->sendRequest( "advertiseClients", "get", $requestData );

/**
 * Обод спискка
 */

/**
 * Сформированный список
 */
$returnAdvertises = [];


/**
 * Фильтр источников
 */
$advertisesFilter = [];

/**
 * Фильтр посещений
 */
$visitsFilter = [];

/**
 * Формирование фильтров
 */
if ( $requestData->id ) $advertisesFilter[ "id" ] = $requestData->id;

if ( $requestData->start_price ) $visitsFilter[ "price >= ?" ] = $requestData->start_price;
if ( $requestData->end_price ) $visitsFilter[ "price <= ?" ] = $requestData->end_price;
if ( $requestData->store_id ) $visitsFilter[ "visits.store_id" ] = $requestData->store_id;
if ( $requestData->start_at ) $visitsFilter[ "end_at >= ?" ] = $requestData->start_at . " 00:00:00";
if ( $requestData->end_at ) $visitsFilter[ "end_at <= ?" ] = $requestData->end_at . " 23:59:59";
$visitsFilter[ "visits.is_payed" ] = "Y";

/**
 * Получение рекламных источников
 */
$advertises = $API->DB->from( "advertise" )
    ->where( $advertisesFilter );

/**
 * Обход рекламных источников
 */
foreach ( $advertises as $advertise ) {

    /**
     * Колличество посещений
     */
    $visitsCount = 0;

    /**
     * Колличество посещений
     */
    $visitsPrice = 0;

    /**
     * Колличество записаных Клиентов
     */
    $recordedCount = 0;

    /**
     * Колличество получивших услугу Клиентов
     */
    $extantCount = 0;

    /**
     * Колличество недошедших Клиентов
     */
    $underdoneCount = 0;

    /**
     * Получение клиентов
     */
    $clients = $API->DB->from( "clients" )
        ->where( "advertise_id", $advertise [ "id" ] );

    foreach ( $clients as $client ) {

        /**
         * Фильтрация посещений по клиенту
         */
        $visitsFilter[ "visits_clients.client_id" ] = $client[ "id" ];

        /**
         * Получение посещений Клиента
         */
        $clientVisits = $API->DB->from( "visits" )
            ->leftJoin( "visits_clients ON visits_clients.visit_id = visits.id" )
            ->select( null )->select( [ "visits.id", "visits.status", "visits.start_at", "visits.store_id", "visits.price", "visits.status", "visits.is_payed" ] )
            ->where( $visitsFilter )
            ->orderBy( "visits.start_at desc" )
            ->limit( 0 );

        if ( $clientVisits ) {

            $recordedCount++;

        } else {

            $underdoneCount++;

        }
        /**
         * Обход посещений клиента
         */
        foreach ( $clientVisits as $clientVisit ) {

            if ( $clientVisit[ "status" ] == "ended" ) {

                $extantCount++;

            }
            $visitsCount++;
            $visitsPrice += $clientVisit[ "price" ];


        } // foreach. $userVisits

    } // foreach. $clients

    $returnAdvertises[] = [

        "id" => $advertise["id"],
        "title" => $advertise["title"],
        "clientsCount" => count( $clients ),
        "recordedCount" => $recordedCount,
        "extantCount" => $extantCount,
        "underdoneCount" => $underdoneCount,
        "visitsCount" => $visitsCount,
        "price" => $visitsPrice

    ];

}

foreach ( $returnAdvertises as $advertise ) {

    $advertiseStatistic[ "cacheFlow" ] += $advertise[ "price" ];

}


$API->returnResponse(

    [
        [
            "value" => number_format( intval( $advertiseStatistic[ "cacheFlow" ] ), 0, '.', ' ' ),
            "description" => "Прибыль",
            "icon" => "",
            "prefix" => "₽",
            "postfix" => [
                "icon" => "",
                "value" => "",
                "background" => ""
            ],
            "type" => "char",
            "background" => "",
            "detail" => []
        ]
    ]

);
