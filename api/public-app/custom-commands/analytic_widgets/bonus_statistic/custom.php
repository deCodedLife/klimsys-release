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
    "deposit_count" => 0,

    /**
     * Сумма посещений
     */
    "deposit_sum" => 0,


];

$clientInfo = $API->DB->from( "clients" )
    ->where( "id", $requestData->client_id )
    ->fetch();

/**
 * Получение посещений Сотрудника
 */
$clientVisits = $API->DB->from( "bonusHistory" )
    ->where( [
        "client_id" => $requestData->client_id
    ] )
    ->limit( 1000 );


/**
 * Формирование пополнений
 */

foreach ( $clientVisits as $userBonus ) {

    $clientStatistic[ "bonus_count" ]++;
    $clientStatistic[ "bonus_sum" ] += (float) $userBonus[ "replenished" ];

} // foreach. $userBonus


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
            "value" => num_word ( number_format( $clientStatistic[ "bonus_count" ], 0, '.', ' ' ), [ 'операция', 'операции', 'операций' ]),
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
            "value" => number_format ( $clientInfo[ "bonuses" ], 0, '.', ' ' ),
            "description" => "Баланс",
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
