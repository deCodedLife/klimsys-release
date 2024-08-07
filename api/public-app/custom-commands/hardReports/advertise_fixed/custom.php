<?php

/**
 * @file
 * Отчет "Рекламные источники"
 */


/**
 * Кол-во обрабатываемых записей за этап
 */
$rowsPerStage = 200;


/**
 * Кол-во записей
 */
$reportDetail[ "recordedCount" ] = 0;

/**
 * Кол-во полученных услуг
 */
$reportDetail[ "extantCount" ] = 0;

/**
 * Кол-во недошедших клиентов
 */
$reportDetail[ "underdoneCount" ] = 0;

/**
 * Кол-во посещений
 */
$reportDetail[ "visitsCount" ] = 0;

/**
 * Сумма
 */
$reportDetail[ "price" ] = 0;


/**
 * Проверка наличия обязательных параметров
 */

if ( !$updatingOrder[ "filters" ][ "id" ] ) {

    /**
     * Обновление статуса отчета
     */
    $API->DB->update( "hardReports" )
        ->set( "status", "updated" )
        ->where( [
            "id" => $updatingOrder[ "id" ]
        ] )
        ->execute();

    $API->returnResponse( true );

} // if. !$updatingOrder[ "filters" ][ "id" ]


/**
 * Получение текущего кэша
 */

$currentCache = [
    "extantCount" => 0,
    "underdoneCount" => 0,
    "visitsCount" => 0,
    "recordedCount" => 0,
    "price" => 0
];

$currentCacheRows = $API->DB->from( "hardReports_cache" )
    ->where( "report_id", $updatingOrder[ "id" ] );

foreach ( $currentCacheRows as $currentCacheRow )
    $currentCache[ $currentCacheRow[ "property_article" ] ] = $currentCacheRow[ "property_value" ];


/**
 * Получение посещений
 */

$updatingOrder[ "offset_count" ] = (int) $updatingOrder[ "offset_count" ];

$visitsFilter = $updatingOrder[ "filters" ];
$visitsFilter[ "advert_id" ] = $visitsFilter[ "id" ];

if ( $visitsFilter[ "start_price" ] ) $visitsFilter[ "price >= ?" ] = $visitsFilter[ "start_price" ];
if ( $visitsFilter[ "end_price" ] ) $visitsFilter[ "price <= ?" ] = $visitsFilter[ "end_price" ];
if ( $visitsFilter[ "start_at" ] ) $visitsFilter[ "end_at >= ?" ] = $visitsFilter[ "start_at" ];
if ( $visitsFilter[ "end_at" ] ) $visitsFilter[ "end_at <= ?" ] = $visitsFilter[ "end_at" ];

unset( $visitsFilter[ "id" ] );
unset( $visitsFilter[ "start_price" ] );
unset( $visitsFilter[ "end_price" ] );
unset( $visitsFilter[ "start_at" ] );
unset( $visitsFilter[ "end_at" ] );

$visits = $API->DB->from( "visits" )
    ->where( $visitsFilter );

/**
 * Проверка завершения отчета
 */

if ( $visits->count() <= $updatingOrder[ "offset_count" ] ) {

    /**
     * Обновление статуса отчета
     */
    $API->DB->update( "hardReports" )
        ->set( "status", "updated" )
        ->where( [
            "id" => $updatingOrder[ "id" ]
        ] )
        ->execute();

    $API->returnResponse( true );

} // if. !count( $visits )

$visits->limit( $rowsPerStage )->offset( $updatingOrder[ "offset_count" ] );


/**
 * Обновление кол-ва обработанных записей
 */
$API->DB->update( "hardReports" )
    ->set( "offset_count", $updatingOrder[ "offset_count" ] + $rowsPerStage )
    ->where( [
        "id" => $updatingOrder[ "id" ]
    ] )
    ->execute();

/**
 * Очистка текущего кэша
 */
$API->DB->deleteFrom( "hardReports_cache" )
    ->where( [
        "report_id" => $updatingOrder[ "id" ]
    ] )
    ->execute();


/**
 * Детальная информация о рекламном источнике
 */
$advertiseDetail = $API->DB->from( "advertise" )
    ->where( "id", $updatingOrder[ "filters" ][ "id" ] )
    ->limit( 1 )
    ->fetch();

/**
 * Название рекламного источника
 */
$API->DB->insertInto( "hardReports_cache" )
    ->values( [
        "report_id" => $updatingOrder[ "id" ],
        "property_article" => "title",
        "property_value" => $advertiseDetail[ "title" ]
    ] )
    ->execute();

/**
 * Получение кол-ва клиентов
 */

$clients = $API->DB->from( "clients" )
    ->where( "advertise_id", $advertiseDetail[ "id" ] );

$API->DB->insertInto( "hardReports_cache" )
    ->values( [
        "report_id" => $updatingOrder[ "id" ],
        "property_article" => "clientsCount",
        "property_value" => count( $clients ),
    ] )
    ->execute();


/**
 * Обработка посещений
 */

foreach ( $visits as $visit ) {

    /**
     * Клиенты полученных услуг
     */
    if ( $visit[ "status" ] == "ended" ) $reportDetail[ "extantCount" ]++;
    elseif ( $visit[ "status" ] == "canceled" ) $reportDetail[ "underdoneCount" ]++;

    /**
     * Общее кол-во посещений
     */
    $reportDetail[ "visitsCount" ]++;

    /**
     * Кол-во записей
     */
    $reportDetail[ "recordedCount" ]++;

    /**
     * Сумма
     */
    $reportDetail[ "price" ] += $visit[ "price" ];

} // foreach. $visits


/**
 * Обновление данных кэша
 */

$API->DB->insertInto( "hardReports_cache" )
    ->values( [
        "report_id" => $updatingOrder[ "id" ],
        "property_article" => "extantCount",
        "property_value" => $currentCache[ "extantCount" ] + $reportDetail[ "extantCount" ],
    ] )
    ->execute();

$API->DB->insertInto( "hardReports_cache" )
    ->values( [
        "report_id" => $updatingOrder[ "id" ],
        "property_article" => "underdoneCount",
        "property_value" => $currentCache[ "underdoneCount" ] + $reportDetail[ "underdoneCount" ],
    ] )
    ->execute();

$API->DB->insertInto( "hardReports_cache" )
    ->values( [
        "report_id" => $updatingOrder[ "id" ],
        "property_article" => "visitsCount",
        "property_value" => $currentCache[ "visitsCount" ] + $reportDetail[ "visitsCount" ],
    ] )
    ->execute();

$API->DB->insertInto( "hardReports_cache" )
    ->values( [
        "report_id" => $updatingOrder[ "id" ],
        "property_article" => "recordedCount",
        "property_value" => $currentCache[ "recordedCount" ] + $reportDetail[ "recordedCount" ],
    ] )
    ->execute();

$API->DB->insertInto( "hardReports_cache" )
    ->values( [
        "report_id" => $updatingOrder[ "id" ],
        "property_article" => "price",
        "property_value" => $currentCache[ "price" ] + $reportDetail[ "price" ],
    ] )
    ->execute();