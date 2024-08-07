<?php

/**
 * Отчет "Кол-во посещений у Сотрудника"
 */


/**
 * График посещений Сотрудника
 */
$userVisitsGraph = [];

/**
 * Получение посещений Сотрудника
 */
$userVisits = $API->DB->from( "visits" )
    ->where( "( user_id = $requestData->user_id OR assist_id = $requestData->user_id )" )
    ->where([
        "start_at >= ?" => date("Y-m-d", strtotime("-1 months")) . " 00:00:00",
        "start_at <= ?" => date( "Y-m-d" ) . " 23:59:59"
    ]);

$userEquipmentVisits = $API->DB->from( "equipmentVisits" )
    ->where( "( user_id = $requestData->user_id OR assist_id = $requestData->user_id )" )
    ->where( [
        "start_at >= ?" => date("Y-m-d", strtotime("-1 months")) . " 00:00:00",
        "start_at <= ?" => date( "Y-m-d" ) . " 23:59:59"
    ] );

/**
 * Формирование графика посещений
 */

foreach ( $userVisits as $userVisit ) {

    $visitDate = date( "Y-m-d", strtotime( $userVisit[ "start_at" ] ) );
    $userVisitsGraph[ $visitDate ]++;

} // foreach. $userVisits

foreach ( $userEquipmentVisits as $userEquipmentVisit ) {

    $visitDate = date( "Y-m-d", strtotime( $userEquipmentVisit[ "start_at" ] ) );
    $userVisitsGraph[ $visitDate ]++;

} // foreach. $userEquipmentVisits


function num_word ( $value, $words, $show = true ) { // function. num_word() for declension of nouns after the numeral

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
            "value" => num_word( ( count( $userVisits ) + count( $userEquipmentVisits ) ) , [ 'посещение', 'посещения', 'посещений' ]),
            "description" => "за 30 дней",
            "icon" => "",
            "prefix" => "",
            "postfix" => [
                "icon" => "",
                "value" => "",
                "background" => ""
            ],
            "background" => "",
            "detail" => [
                "type" => "char",
                "settings" => [
                    "char" => [
                        "x" => array_keys($userVisitsGraph),
                        "lines" => [
                            [
                                "title" => "Посещений",
                                "values" => $userVisitsGraph
                            ]
                        ]
                    ]
                ]
            ]

        ]
    ]

);
