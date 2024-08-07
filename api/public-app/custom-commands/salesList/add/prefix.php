<?php

if ( empty( $requestData->products ) ) $API->returnResponse( "В корзине нет товаров", 402 );

$requestData->user_id = 4;
$publicAppPath = $API::$configs[ "paths" ][ "public_app" ];

$clientDetails = $API->DB->from( "clients" )
    ->where( "phone", intval( $requestData->client->phone ) )
    ->fetch();

if ( empty( $clientDetails ) ) {

    $clientDetails = $API->sendRequest(
        "clients",
        "add",
        $requestData->client,
        $_SERVER[ "HTTP_HOST" ],
        true );

    $clientDetails = (array)$clientDetails;
    if ( $clientDetails[ "status" ] != 200 ) $API->returnResponse( $clientDetails[ "data" ], 402 );
    $requestData->client_id = intval( $clientDetails[ "data" ] );

}
else $requestData->client_id = $clientDetails[ "id" ];

$requestData->summary = 0;
foreach ( $requestData->products as $product ) $requestData->summary += $product->cost * $product->amount;
$requestData->sum_cash = $requestData->summary;


if ( $requestData->action == "order" )  {

    $requestData->pay_method = "cash";
    require ( $publicAppPath . "/custom-commands/salesList/add/validation.php" );

}


/**
 * Создание транзакции
 */
require ( $publicAppPath . "/custom-commands/salesList/add/create-transaction.php" );


$API->returnResponse();