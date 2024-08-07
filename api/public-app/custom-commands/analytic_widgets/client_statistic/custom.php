<?php

/**
* Отчет "Статистика клиента
*/

/**
* Статистика клиента
*/
$clientStatistic = [

    /**
    * Количество посещений
    */
    "visits_count" => 0,

    /**
    * Сумма посещений
    */
    "visits_sum" => 0,

    /**
    * Средний чек
    */
    "medium_visit_price" => 0,

    /**
    * Минимальная цена
    */
    "min_visit_price" => 0,

    /**
    * Максимальная цена
    */
    "max_visit_price" => 0,

    /**
    * Дата последнего посещения
    */
    "last_visit_date" => ""

];

$filters = [
    "visits_clients.client_id" => $requestData->client_id,
    "is_payed" => 'Y'
];

if ( $requestData->start_at ) $filters[ "start_at >= ?" ] = $requestData->start_at . " 00:00:00";
if ( $requestData->end_at )   $filters[ "end_at <= ?" ]   = $requestData->end_at   . " 23:59:59";

/**
* Получение посещений Сотрудника
*/
$clientVisits = $API->DB->from( "visits" )
    ->leftJoin( "visits_clients ON visits_clients.visit_id = visits.id" )
    ->select( null )->select( [  "visits.id", "visits.start_at", "visits.is_active", "visits.status", "visits.price"  ] )
    ->where( $filters )
    ->orderBy( "visits.start_at desc" )
    ->limit( 0 );


/**
* Формирование графика посещений
*/

foreach ( $clientVisits as $userVisit ) {

$clientStatistic[ "visits_count" ]++;
$clientStatistic[ "visits_sum" ] += (float) $userVisit[ "price" ];

} // foreach. $userVisits


function num_word( $value, $words, $show = true ) { // function. num_word() for declension of nouns after the numeral

    $num = $value % 100;

    if ( $num > 19 ) {

        $num = $num % 10;

    }

    $out = ( $show ) ?  $value . ' ' : '';
    switch ( $num ) {

        case 1:  $out .= $words[0]; break;

        case 2:

        case 3:

        case 4:  $out .= $words[1]; break;

        default: $out .= $words[2]; break;

    }

    return $out;
}

$API->returnResponse(

    [
        [
            "value" => num_word( $clientStatistic[ "visits_count" ], [ 'посещение', 'посещения', 'посещений' ]),
            "description" => "всего",
            "icon" => "",
            "prefix" => "",
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
            "value" =>  number_format( intval( $clientStatistic[ "visits_sum" ] ), 0, '.', ' '),
            "description" => "Сумма посещений",
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
