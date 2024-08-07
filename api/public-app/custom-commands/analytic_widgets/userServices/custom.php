<?php

/**
 * @file
 * Отчет "продажа услуг по сотрудникам
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
 * Получение списка услуг
 */
$userServices = $API->sendRequest( "userServices", "get", $requestData );
/**
 * Обрабботка списка
 */
foreach ( $userServices as $userService ) {

    $reportStatistic[ "services_sum" ] += $userService->sum;

} // foreach .$userServices

$API->returnResponse(

    [

        [
            "value" => number_format( intval( $reportStatistic[ "services_sum" ] ), 0, '.', ' '),
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
