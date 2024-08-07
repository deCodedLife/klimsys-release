<?php

/**
 * KPI
 */
$returnKpi = [];

/**
 * KPI по количеству проданных услуг
 */
$range = [];

/**
 * KPI по количеству проданных услуг - Тип отображения
 */
$range[ "type" ] = "range";

/**
 * KPI по количеству проданных услуг - Заголовок
 */
$range[ "title" ] = "KPI по количеству проданных услуг";

/**
 * KPI продаж
 */
$progressbar = [];

/**
 * KPI продаж - Тип отображения
 */
$progressbar[ "type" ] = "progressbar";

/**
 * KPI продаж - Заголовок
 */
$progressbar[ "title" ] = "KPI продаж";

/**
 * Сумма продаж
 */
$sum = 0;

/**
 * Список  KPI продаж
 */
$kpiSales = $API->DB->from( "kpi_sales" )
    ->where( "row_id", $requestData->id );

/**
 * KPI по количеству проданных услуг
 */
$kpiServices = $API->DB->from( "kpi_services" )
    ->where( "row_id", $requestData->id );


/**
 * Получение детальной информации о пользователе
 */

$userDetail = $API->DB->from( "users" )
    ->where( "id", $requestData->id )
    ->fetch();

/**
 * Возвращает пустой массив, если
 * Тип KPI не Ставка + KPI и
 * если не настроены услуги
 */

if ( $userDetail[ "salary_type" ] != "rate_kpi" &&  count( $kpiServices ) == 0  )
    $API->returnResponse( [] );


$start_at = date( 'Y-m-01' );
$end_at = date( 'Y-m-d' );
$user_id = $requestData->id;

$publicApp = $API::$configs[ "paths" ][ "public_app" ];
require_once( "$publicApp/custom-libs/kpi/visits.php" );


/**
 * Обход KPI
 */
foreach ( $kpiSales as $kpiSale ) {

    $percent = ( $sales_summary * 100 ) / $kpiSale[ "summary" ];

    $progressbar[ "values" ][] = [

        "title" => $sales_summary . " руб.",
        "percent" => (int)$percent,
        "reward" => $kpiSale[ "kpi_value" ] . " руб.",

    ];

}

/**
 * Наполнение выдачи KPI продаж
 */
$returnKpi[] = $progressbar;

/**
 * Обход KPI проданных услуг
 */
foreach ( $kpiServices as $kpiService ) {

    $service = $kpiService[ "service" ];
    $sales_id = $sales_id ?? [ 0 ];

    $services_count = mysqli_fetch_array( mysqli_query( $API->DB_connection, "
    SELECT 
        COUNT( salesProductsList.id ) as count
    FROM 
        salesList
    INNER JOIN 
        salesProductsList ON salesProductsList.sale_id = salesList.id
    WHERE 
        salesList.id IN ( " . join( ",", $sales_id ) . " ) AND
        salesProductsList.product_id = $service AND
        salesProductsList.type = 'service' AND
        salesList.action = 'sell'",
        ) )[ "count" ] ?? 0;

    /**
     * Получение детальной информации об услуги
     */
    $serviceDetail = $API->DB->from( "services" )
        ->where( "id", $kpiService[ "service" ] )
        ->limit( 1 )
        ->fetch();

    /**
     * Наполнение списка продаж услуг сотрудника
     */
    $range[ "values" ][] = [

        "title" => $serviceDetail[ "title" ],
        "current" => $services_count,
        "reach" => (int) $kpiService[ "required_value" ],
        "reward" => $kpiService[ "kpi_value" ] . " руб."

    ];

}

/**
 * Наполнение списка продаж услуг сотрудника
 */
$returnKpi[] = $range;

/**
 * Ответ
 */
$API->returnResponse( $returnKpi );


