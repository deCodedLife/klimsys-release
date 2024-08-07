<?php

/**
 * @file
 * Отчет "Рекламные источники"
 */
$API->request->data->limt = 0;
$advertise_fixed = $API->sendRequest( "advertiseClients", "get", $API->request->data );
$sum = 0;

foreach( $advertise_fixed as $item ) $sum += $item->price;

$API->returnResponse(
    [
        [
           "value" => number_format( $sum, 0, '.', ' ' ),
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
