<?php

/**
 * @ file
 * Отчет статистики клиентов
 */


/**
 * График новых клиетнов
 */
$clientsRegGraph = [];

/**
 * График посещений клиентов
 */
$clientVisitsGraph = [];

/**
 * График прихода
 */
$cashFlowGraph = [];

/**
 * График Среднего чека
 */
$averageСhequeGraph = [];


/**
 * Количество посещений
 */
$visitsCount = 0;

/**
 * Приход
 */
$cashFlow = 0;

/**
 * Средний чек
 */
$averageСheque = 0;

/**
 * Фильтр клиентов
 */
$clientsFilter = [];

/**
 * Фильтр посещений клиентов
 */
$clientsVisitsfilter = [];


/**
 * Колличество онлайн поссещений
 */
$clientsOnlineVisits = 0;

/**
 * График онлайн поссещений
 */
$clientOnlineVisitsGraph = [];

/**
 * Формирование фильтра посещений клиентов
 */

$clientsVisitsfilter[ "clients.is_active" ] = "Y";
if ( $requestData->start_at ) $clientsVisitsfilter[ "clients.created_at >= ?" ] = $requestData->start_at;
if ( $requestData->end_at ) $clientsVisitsfilter[ "clients.created_at <= ?" ] = $requestData->end_at;
if ( $requestData->start_ear ) $clientsVisitsfilter[ "clients.birthday >= ?" ] = $requestData->start_ear;
if ( $requestData->end_ear ) $clientsVisitsfilter[ "clients.birthday <= ?" ] = $requestData->end_ear;

$clientsVisitsfilter[ "visits.is_active" ] = "Y";
$clientsVisitsfilter[ "visits.is_payed" ] = "Y";
if ( $requestData->store_id ) $clientsVisitsfilter[ "visits.store_id" ] = $requestData->store_id;


$clientsVisits = $API->DB->from( "visits" )
    ->leftJoin( "clients ON visits.client_id = clients.id" )
    ->select( null )->select( [ "visits.id", "visits.client_id",  "visits.start_at", "visits.is_active", "visits.is_payed", "visits.price", "visits.is_online", "clients.birthday", "clients.is_active", "clients.created_at", "visits.store_id" ] )
    ->where( $clientsVisitsfilter )
    ->orderBy( "visits.start_at DESC" );

/**
 * Формирование фильтра клиентов
 */
$clientsFilter[ "is_active" ] = "Y";
if ( $requestData->start_at ) $clientsFilter[ "created_at >= ?" ] = $requestData->start_at;
if ( $requestData->end_at ) $clientsFilter[ "created_at <= ?" ] = $requestData->end_at;
if ( $requestData->start_ear ) $clientsFilter[ "birthday >= ?" ] = $requestData->start_ear;
if ( $requestData->end_ear ) $clientsFilter[ "birthday <= ?" ] = $requestData->end_ear;

/**
 * Получение клиентов
 */
$clients = $API->DB->from( "clients" )
    ->where( $clientsFilter );

/**
 * Обход клиентов
 */
foreach ( $clients as $client ) {

    /**
     * График новых клиентов
     */
    $regDate = date( "Y-m-d", strtotime( $client[ "created_at" ] ) );
    $clientsRegGraph[ $regDate ]++;

} // foreach. $clients


/**
 * Обход посещений клиентов
 */
foreach ( $clientsVisits as $clientsVisit ) {

    /**
     * Дата посещения
     */
    $visitDate = date( "Y-m-d", strtotime( $clientsVisit[ "start_at" ] ) );

    /**
     * Проверка на Онлайн запись
     */
    if ( $clientsVisit[ "is_online" ] == "Y" ) {

        /**
         * График онлайн записей клиентов
         */
        $clientsOnlineVisits++;
        $clientOnlineVisitsGraph[ $visitDate ]++;

    }

    /**
     * График посещений клиентов
     */
    $clientVisitsGraph[ $visitDate ]++;

    /**
     * Приход
     */
    $cashFlow += $clientsVisit[ "price" ];

    /**
     * График прихода
     */
    $cashFlowGraph[ $visitDate ] += $clientsVisit[ "price" ];


}

/**
 * Обход графика прихода
 */
foreach ( $cashFlowGraph as $visitDate => $cashFlowGraphDay ) {

    /**
     * Посчет ссреднего чека на дату
     */
    $averageСhequeGraph[ $visitDate ] = round($cashFlowGraphDay / $clientVisitsGraph[ $visitDate ] );

}


/**
 * Проверка есть ли посещения
 */
if ( count( $clientsVisits ) != 0 ) {

    /**
     * Средний чек
     */
    $averageСheque = $cashFlow / count( $clientsVisits );

} // if ( $visitsCount != 0 )

/**
 * Формирования виджетов
 */
$API->returnResponse(

    [
        [
            "value" => count( $clients ),
            "description" => "Новые клиенты",
            "icon" => "",
            "size" => "4",
            "prefix" => "",
            "postfix" => [
                "icon" => "",
                "value" => "",
                "background" => ""
            ],
            "background" => "",
            "detail" => [
                "type" => "details_char",
                "settings" => [
                    "char" => [
                        "x" => array_keys($clientsRegGraph),
                        "lines" => [
                            [
                                "title" => "Новых клиентов",
                                "values" => $clientsRegGraph
                            ]
                        ]
                    ]
                ]
            ]
        ],
        [
            "value" => count( $clientsVisits ),
            "description" => "Посещения",
            "icon" => "",
            "prefix" => "",
            "postfix" => [
                "icon" => "",
                "value" => "",
                "background" => ""
            ],
            "background" => "",
            "detail" => [
                "type" => "details_char",
                "settings" => [
                    "char" => [
                        "x" => array_keys($clientVisitsGraph),
                        "lines" => [
                            [
                                "title" => "Посещений",
                                "values" => $clientVisitsGraph
                            ]
                        ]
                    ]
                ]
            ]
        ],
        [
            "value" => $clientsOnlineVisits,
            "description" => "Посещения через онлайн запись",
            "icon" => "",
            "prefix" => "",
            "postfix" => [
                "icon" => "",
                "value" => "",
                "background" => ""
            ],
            "background" => "",
            "detail" => [
                "type" => "details_char",
                "settings" => [
                    "char" => [
                        "x" => array_keys($clientOnlineVisitsGraph),
                        "lines" => [
                            [
                                "title" => "Посещений",
                                "values" => $clientOnlineVisitsGraph
                            ]
                        ]
                    ]
                ]
            ]
        ],
        [
            "value" => number_format( $cashFlow, 0, '.', ' ' ),
            "description" => "Приход",
            "icon" => "",
            "prefix" => "₽",
            "postfix" => [
                "icon" => "",
                "value" => "",
                "background" => ""
            ],
            "background" => "",
            "detail" => [
                "type" => "details_char",
                "settings" => [
                    "char" => [
                        "x" => array_keys($cashFlowGraph),
                        "lines" => [
                            [
                                "title" => "Приход",
                                "values" => $cashFlowGraph
                            ]
                        ]
                    ]
                ]
            ]
        ],
        [
            "value" => number_format( round( $averageСheque, 2 ), 2, '.', ' '),
            "description" => "Средний чек",
            "icon" => "",
            "prefix" => "₽",
            "postfix" => [
                "icon" => "",
                "value" => "",
                "background" => ""
            ],
            "background" => "",
            "detail" => [
                "type" => "details_char",
                "settings" => [
                    "char" => [
                        "x" => array_keys($averageСhequeGraph),
                        "lines" => [
                            [
                                "title" => "Средний чек",
                                "values" => $averageСhequeGraph
                            ]
                        ]
                    ]
                ]
            ]

        ]
    ]

);
