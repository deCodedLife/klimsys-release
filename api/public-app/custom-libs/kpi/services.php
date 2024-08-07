<?php

global $API, $requestData;
require_once( __DIR__ . "/user_sales.php" );


/**
 *  Определение переменных
 */
$kpi_type = "KPI услуг";
$kpi_article = "services";



/**
 * Получение настроек KPI
 */
$kpi_sales = $API->DB->from( "kpi_services" )
    ->where( "row_id", $requestData->user_id );



/**
 * Выгрузка списка
 */
foreach ( $kpi_sales as $kpi_sale ) {

    $kpi[] = [
        "type" => "$kpi_type",
        "services_summary" => $sales_summary,
        "services_count" => $sales_count,
        "percent" => ( $sales_count * 100 / $kpi_sale[ "required_value" ] ),
        "promotion" => "-",
        "bonus" => $sales_count < $kpi_sale[ "required_value" ] ? "0" : $kpi_sale[ "kpi_value" ],
        "kpi_type" => $kpi_article
    ];

} // foreach ( $kpi_services as $kpi )