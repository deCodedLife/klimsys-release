<?php

/**
 * Отчет "Кол-во звонков у Сотрудника"
 */

/**
 * График звонков Сотрудника
 */
$userCallsGraph = [];

/**
 * График заергистрированных Клиентов
 */
$regCallsGraph = [];

/**
 * Колличество заергистрированных Клиентов
 */
$regCalls = 0;
/**
 * График записавшихся Клиентов
 */
$visitsCallsGraph = [];

/**
 * Колличество записавшихся Клиентов
 */
$visitsCalls = 0;

if ( !$requestData->user_id ) $API->returnResponse(

    [
        [
            "value" => "Не указан сотрудник",
            "description" => "",
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
                    "char" => []
                ]
            ]

        ]
    ]

);


/**
 * Получение посещений Сотрудника
 */

$filter = [ "user_id" => $requestData->user_id ];
if ( $requestData->start_at ) $filter[ "created_at >= ?" ] = $requestData->start_at . " 00:00:00";
if ( $requestData->end_at ) $filter[ "created_at <= ?" ] = $requestData->end_at . " 23:59:59";

$userCalls = $API->DB->from( "callHistory" )
    ->where( $filter )
    ->limit( 0 );

/**
 * Формирование графика посещений
 */

foreach ( $userCalls as $userCall ) {

    $callDate = date( "Y-m-d", strtotime( $userCall[ "created_at" ] ) );
    $userCallsGraph[ $callDate ]++;
    if ( !$regCallsGraph[ $callDate ] ) $regCallsGraph[ $callDate ] = 0;
    if ( !$visitsCallsGraph[ $callDate ] ) $visitsCallsGraph[ $callDate ] = 0;

    $client = $API->DB->from( "clients" )
        ->where( "phone", $userCall[ "client_phone" ] )
        ->limit( 1 )
        ->fetch();

    if ( $client && $client[ "created_at" ] < $callDate + 1 && $client[ "created_at" ] > $callDate ) {

        $regCallsGraph[ $callDate ]++;
        $regCalls++;

        $visitClient = $API->DB->from( "visits_clients" )
            ->where( "client_id", $client[ "id" ] )
            ->limit( 0 )
            ->fetch();

        $visit = $API->DB->from( "visits_clients" )
            ->where( "id", $visitClient[ "visit_id" ] )
            ->limit( 0 )
            ->fetch();

        if ( $visitClient && $visit[ "created_at" ] < $callDate + 1 && $visit[ "created_at" ] > $callDate ) {

            $visitsCallsGraph[ $callDate ]++;
            $visitsCalls++;

        }

    }

} // foreach. $userCalls

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
            "value" => num_word( count( $userCalls ), [ 'звонок', 'звонка', 'звонков' ] ),
            "description" => "",
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
                        "x" => array_keys($userCallsGraph),
                        "lines" => [
                            [
                                "title" => "Звонков",
                                "values" => $userCallsGraph
                            ],
                            [
                                "title" => "Зарегистрировано клиентов",
                                "values" => $regCallsGraph

                            ],
                            [
                                "title" => "Записано клиентов",
                                "values" => $visitsCallsGraph
                            ],
                        ]
                    ]
                ]
            ]

        ]
    ]

);
