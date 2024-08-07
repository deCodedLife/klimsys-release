<?php

global $API, $requestData;

/**
 * Переменные для выдачи
 */
$currentDay = new DateTime();
$report = [];
$statistic = [];



/**
 * Обработка фильтра по дате
 */
$dateFrom = $currentDay->format("Y-m-d") . " 00:00:00";
$dateTo   = $currentDay->format("Y-m-d") . " 23:59:59";

if ( $requestData->start_at ) $dateFrom = $requestData->start_at . " 00:00:00";
if ( $requestData->end_at )   $dateTo   = $requestData->end_at . " 23:59:59";



/**
 * Создание фильтров для запроса
 */
$filter = [
    "created_at >= ?" => $dateFrom,
    "created_at <= ?" => $dateTo,
    "not action = ?" => "sellReturn",
    "status" => "done"
];


/**
 * Обработка оставшихся параметров из фильтров
 */
if ( $requestData->action ) $filter[ "action = ?" ] = $requestData->action;
if ( $requestData->client_id ) $filter[ "client_id = ?" ] = $requestData->client_id;
if ( $requestData->employee_id ) $filter[ "employee_id = ?" ] = $requestData->employee_id;
if ( $requestData->store_id ) $filter[ "store_id = ?" ] = $requestData->store_id;
if ( $requestData->pay_method ) $filter[ "pay_method" ] = $requestData->pay_method;


/**
 * Получение продаж
 */
$salesList = $API->DB->from( "salesList" )->where( $filter ) ?? [];


/**
 * Формирование списка графиков
 */
$report[ "Наличными" ] = 0;
$report[ "Безналичными" ] = 0;
$report[ "Аванс" ] = 0;
$report[ "Итого" ] = 0;
$report[ "Возврат наличными" ] = 0;
$report[ "Возврат безналичными" ] = 0;



/**
 * Подсчёт значений графиков
 */
foreach ( $salesList as $sale ) {

    $report[ "Аванс" ] += (float) $sale[ "sum_deposit" ];
    $report[ "Наличными" ] += (float) $sale[ "sum_cash" ];
    $report[ "Безналичными" ] += (float) $sale[ "sum_card" ];
    $report[ "Итого" ] += (float) $sale[ "sum_deposit" ] + (float) $sale[ "sum_cash" ] + (float) $sale[ "sum_card" ];

} // foreach ( $salesList as $sale ) {



/**
 * Получение списка возвратов
 */
unset( $filter[ "not action = ?" ] );
//$filter[ "not action = ?" ] = "sale";
$filter[ "action = ?" ] = "sellReturn";
$salesList =  $API->DB->from( "salesList" )->where( $filter );


/**
 * Подсчёт значений возвратов
 */
foreach ( $salesList as $sale ) {

    $report[ "Возврат наличными" ] += (float) $sale[ "sum_cash" ];
    $report[ "Возврат безналичными" ] += (float) $sale[ "sum_card" ];

}


/**
 * Формирование и выдача графиков
 */
foreach ( $report as $key => $item ) {
    $statistic[] = [
        "size" => 1,
        "value" =>  number_format( round($item, 2), 2, '.', ' ' ),
        "description" => $key,
        "icon" => "",
        "prefix" => "",
        "postfix" => [
            "icon" => "",
            "value" => "₽",
            "background" => "dark"
        ],
        "background" => "",
        "detail" => [
        ]
    ];
}

$API->returnResponse( $statistic );