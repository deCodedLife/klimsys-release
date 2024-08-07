<?php

/**
 * @file
 * Отчет "продажа услуг
 */


/**
 * Детальная информация об отчете
 */
$reportStatistic = [

    /**
     * Сумма продаж
     */
    "services_sum" => 0,

];

/**
 * Фильтр продаж
 */
$salesFilter = [];

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
if ( $requestData->id ) $salesFilter[ "salesProductsList.product_id" ] = $requestData->id;


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
 * Обход продаж
 */
foreach ( $salesList as $sale ) {

    $service = $API->DB->from( "services" )
        ->where( "id", $sale[ "product_id" ] )
        ->limit( 1 )
        ->fetch();

    /**
     * Проверка наличия фильтра
     */
    if ( $requestData->category_id && $requestData->category_id != null ) {

        if ( $service[ "category_id" ] == $requestData->category_id) {

            $reportStatistic[ "services_sum" ] += $sale[ "amount" ] * $sale[ "cost" ];

        }

    } else {

        $reportStatistic[ "services_sum" ] += $sale[ "amount" ] * $sale[ "cost" ];

    } // if ( $requestData->category_id && $requestData->category_id != null )

} // foreach .$salesList

$API->returnResponse(

    [

        [
            "value" => number_format( $reportStatistic[ "services_sum" ], 0, '.', ' ' ),
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























//
///**
// * Фильтр услуг в посещениях
// */
//$visitServicesFilter = [];
//
///**
// * Фильтр для услуг
// */
//$servicesFilter = [];
//
//
///**
// * Формирование фильтров
// */
//
//if ( $requestData->start_at ) $visitServicesFilter[ "date >= ?" ] = $requestData->start_at . " 00:00:00";
//if ( $requestData->end_at ) $visitServicesFilter[ "date <= ?" ] = $requestData->end_at . " 23:59:59";
//if ( $requestData->store_id ) $visitServicesFilter[ "store_id" ] = $requestData->store_id;
//
//if ( $requestData->category_id ) $servicesFilter[ "category_id" ] = $requestData->category_id;
//if ( $requestData->id ) $servicesFilter[ "id" ] = $requestData->id;
//
//$servicesFilter[ "is_active" ] = "Y";
//
//
///**
// * Получение услуг
// */
//$services = $API->DB->from( "services" )
//    ->where( $servicesFilter );
//
///**
// * Получение услуг в посещениях
// */
//$visitsServices = $API->DB->from( "visits_services" )
//    ->where( $visitServicesFilter );
//
//
///**
// * Фильтр по сотрудникам
// */
//if ( $requestData->user_id ) {
//
//    /**
//     * Получение посещений сотрудника
//     */
//    $visitsUsers = $API->DB->from( "visits_users" )
//        ->where( "user_id", $requestData->user_id );
//
//
//    /**
//     * Фильтр услуг в посещениях по сотруднику
//     */
//
//    $filteredVisitsServices = [];
//
//    foreach ( $visitsServices as $visitsService ) {
//
//        $isContinue = true;
//
//        foreach ( $visitsUsers as $visitsUser )
//            if ( $visitsUser[ "visit_id" ] == $visitsService[ "visit_id" ] ) $isContinue = false;
//
//        if ( $isContinue ) continue;
//
//
//        $filteredVisitsServices[] = $visitsService;
//
//    } // foreach. $visitsServices
//
//
//    /**
//     * Обновление услуг в посещениях
//     */
//    $visitsServices = $filteredVisitsServices;
//
//} // if. $requestData->user_id
//
//
///**
// * Обработка услуг
// */
//
//foreach ( $services as $service ) {
//
//    /**
//     * Кол-во оказанных услуг
//     */
//    $count = 0;
//
//
//    /**
//     * Подсчет оказанных услуг
//     */
//    foreach ( $visitsServices as $visitsService )
//        if ( $visitsService[ "service_id" ] == $service[ "id" ] )
//            $count++;
//
//
//    $reportStatistic[ "visits_sum" ] += $service[ "price" ] * $count;
//
//} // foreach. $services

