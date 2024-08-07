<?php

if ( !$requestData->user_id ) {

    $API->returnResponse(

        [
            [
                "value" => 0,
                "description" => "Сумма продаж",
                "size" => "1",
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
            ],
            [
                "value" => 0,
                "description" => "Сумма продаж с  %",
                "size" => "1",
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
            ],
            [
                "value" => 0,
                "description" => "Сумма %",
                "size" => "1",
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
            ],
            [
                "value" => 0,
                "description" => "Количество клиентов",
                "icon" => "",
                "size" => "1",
                "prefix" => "",
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

};

/**
 * @file
 * Отчет "Клиенты посещавшие специалистов
 */

/**
 * Детальная информация об отчете
 */
$reportStatistic = [

    /**
     * Сумма продаж
     */
    "visits_sum" => 0,

    /**
     * Сумма продаж
     */
    "services_user_percents" => 0,

    /**
     * Сумма продаж
     */
    "clients_count" => 0,

];

/**
 * Получение списка посещений
 */

//$requestData->limit = 0;
//$start = microtime( true );
//$end = microtime( true );
//$API->returnResponse( $visitsClients );
//ini_set( "display_errors", 1 );
//if ( $requestData->user_id ) $filters[ "visits.user_id" ] = $requestData->user_id;
//if ( $requestData->start_price ) $filters[ "visits.price >= ?" ] = $requestData->start_price;
//if ( $requestData->end_price ) $filters[ "visits.price <= ?" ] = $requestData->end_price;
//if ( $requestData->start_at ) $filters[ "visits.start_at >= ?" ] = $requestData->start_at;
//if ( $requestData->end_at ) $filters[ "visits.end_at <= ?" ] = $requestData->end_at;
//$filters[ "visits.status <= ?" ] = "ended";
//$filters[ "visits.is_payed" ] = "Y";
//
//$start = microtime( true );
//$visitsList = $API->DB->from( "visits" )
//    ->innerJoin( "visits_services on visits_services.visit_id = visits.id" )
//    ->select( "service_id as service_id" )
//    ->where( $filters );
//
//foreach ( $visitsList as $visit ) {
//
//    $visitServices[ $visit[ "service_id" ] ]++;
//    $reportStatistic[ "visits_sum" ] += $visit[ "price" ];
//
//}
//
//$servicesUserPercents = $API->DB->from( "services_user_percents")
//    ->where( "row_id", $requestData->user_id );
//
//$services = [];
//
//foreach ( $servicesUserPercents as $servicesUserPercent)
//    $services[] = $servicesUserPercent[ "service_id" ];
//
//
//
//$reportStatistic[ "clients_count" ] += count( $visitServices );
//
///**
// * Обрабботка списка
// */
//foreach ( $visitsList as $visit ) {
//
//
//
//    foreach ( $visitsClient->services_id as $service ) {
//
//        if ( in_array( $service->value, $services ) ) {
//
//            $reportStatistic[ "services_user_percents" ] += $visitsClient->price;
//
//        }
//
//    }
//
//
//} // foreach .$userServices

//$visitsClients = $API->sendRequest( "visit_clients", "get", $requestData );


if ( $requestData->user_id ) $filters[ "visits.user_id" ] = $requestData->user_id;
if ( $requestData->start_price ) $filters[ "visits.price >= ?" ] = $requestData->start_price;
if ( $requestData->end_price ) $filters[ "visits.price <= ?" ] = $requestData->end_price;
if ( $requestData->start_at ) $filters[ "visits.start_at >= ?" ] = $requestData->start_at . " 00:00:00";
if ( $requestData->end_at ) $filters[ "visits.end_at <= ?" ] = $requestData->end_at . " 23:59:59";
$filters[ "visits.status" ] = "ended";
$filters[ "visits.is_payed" ] = "Y";

$servicesUserPercents = $API->DB->from( "services_user_percents")
    ->where( "row_id", $requestData->user_id );

$services = [];
$servicesCash = [];

foreach ( $servicesUserPercents as $servicesUserPercent) {

    $services[] = $servicesUserPercent[ "service_id" ];
    $servicesCash[ $servicesUserPercent[ "service_id" ] ][ "percent" ] = $servicesUserPercent[ "percent" ];

}

$visitsList = $API->DB->from( "visits" )
    ->select( null )->select(
        [
            "salesProductsList.product_id",
            "salesProductsList.cost",
            "saleVisits.visit_id",
            "saleVisits.sale_id",
            "visits.user_id",
        ]
    )
    ->innerJoin( "saleVisits on saleVisits.visit_id = visits.id" )
    ->innerJoin( "salesProductsList on salesProductsList.sale_id = saleVisits.sale_id" )
    ->where( $filters );

$visits = [];

foreach ( $visitsList as $visit ) {

    if ( in_array( $visit[ "product_id" ], $services )  ) {

        if ( $servicesCash[ $visit[ "product_id" ] ][ "percent" ] != 0 ) {

            $reportStatistic[ "services_user_percents" ] +=  $servicesCash[ $visit[ "product_id" ] ][ "percent" ] * $visit[ "cost" ] / 100;

        }


        $reportStatistic[ "visit_user_percents" ] += $visit[ "cost" ];

    }
    $visits[] = $visit[ "visit_id" ];
    $reportStatistic[ "visits_sum" ] += $visit[ "cost" ];
}

$API->returnResponse(

    [
        [
            "value" => number_format( intval( $reportStatistic[ "visits_sum" ] ), 0, '.', ' ' ),
            "description" => "Сумма продаж",
            "size" => "1",
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
        ],
        [
            "value" => number_format( intval( $reportStatistic["visit_user_percents"] ), 0, '.', ' ' ),
            "description" => "Сумма продаж с  %",
            "size" => "1",
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
        ],
        [
            "value" => number_format( intval( $reportStatistic["services_user_percents"] ), 0, '.', ' ' ),
            "description" => "Сумма %",
            "size" => "1",
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
        ],
        [
            "value" => count(array_unique($visits)),
            "description" => "Количество клиентов",
            "icon" => "",
            "size" => "1",
            "prefix" => "",
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
