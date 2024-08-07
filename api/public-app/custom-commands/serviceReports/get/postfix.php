<?php

/**
 * @file
 * Список "продажа услуг
 */

/**
 * Сформированный список
 */
$returnServices = [];


/**
 * Фильтр Услуг
 */
$servicesFilter = [];


/**
 * Формирование фильтров
 */

$salesFilter[ "salesList.status" ] = "done";
$salesFilter[ "salesList.action" ] = "sell";

if ( $requestData->start_price ) $salesFilter[ "salesList.summary >= ?" ] = $requestData->start_price;
if ( $requestData->end_price ) $salesFilter[ "salesList.summary <= ?" ] = $requestData->end_price;
if ( $requestData->start_at ) $salesFilter[ "salesList.created_at >= ?" ] = $requestData->start_at . " 00:00:00";
if ( $requestData->end_at ) $salesFilter[ "salesList.created_at <= ?" ] = $requestData->end_at . " 23:59:59";
if ( $requestData->store_id ) $salesFilter[ "salesList.store_id" ] = $requestData->store_id;


foreach ( $response[ "data" ] as $service ) {

    /**
     * Количество услуги в продажах
     */
    $count = 0;

    /**
     * Сумма услуги в продажах
     */
    $sum = 0;

    $salesFilter[ "salesProductsList.product_id" ] = $service[ "id" ];


    /**
     * Получение продаж
     */
    $salesList = $API->DB->from( "salesList" )
        ->leftJoin( "salesProductsList ON salesProductsList.sale_id = salesList.id" )
        ->select( null )->select( [
            "salesList.id",
            "salesProductsList.product_id",
            "salesProductsList.cost",
            "salesProductsList.amount"
        ] )
        ->where( $salesFilter )
        ->orderBy( "salesList.created_at desc" )
        ->limit( 0 );

    /**
     * Обход Продаж
     */
    foreach ( $salesList as $sale ) {

        /**
         * Проверка наличия услуги в продажах
         */
        $count += $sale[ "amount" ];
        $sum += $sale[ "amount" ] * $sale[ "cost" ];

    } // foreach .$salesList

    /**
     * Проверка на наличие услуги в продажах
     */
    $returnServices[] = [
        "id" => $service[ "id" ],
        "title" => $service[ "title" ],
        "count" => $count,
        "date" => $service[ "date" ],
        "sum" => $sum
    ];


} // foreach. $response[ "data" ]

$response[ "data" ] = $returnServices;