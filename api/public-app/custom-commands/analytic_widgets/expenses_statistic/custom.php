<?php

$requestData->context = (object) [ "block" => "list" ];
$expenses = $API->sendRequest( "expenses", "get", $requestData );

$sum = 0;
foreach ( $expenses as $expense ) $sum += $expense->price;

$API->returnResponse(

    [
        [
            "value" => number_format( abs( $sum )  , 0, '.', ' ' ),
            "description" => "Сумма расходов",
            "icon" => "",
            "prefix" => "₽",
            "size" => 1,
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
