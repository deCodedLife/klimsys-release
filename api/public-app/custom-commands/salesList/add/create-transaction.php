<?php


/**
 *  Создание транзакции
 */
$saleID = $API->DB->insertInto( "salesList" )
    ->values( [
        "employee_id" => (int) $API::$userDetail->id ?? 2,
        "action" => $requestData->action,
        "sum_card" => $requestData->sum_card,
        "sum_cash" => $requestData->sum_cash,
        "status" => "waiting",
        "pay_method" => $requestData->pay_method,
        "summary" => $requestData->summary,
        "client_id" => $requestData->client_id,
    ] )
    ->execute();



/**
 * Добавление продуктов к продаже
 */
foreach ( $requestData->products as $product ) {

    $product = (array) $product;
    $product[ "sale_id" ] = $saleID;

    if ( !$product[ "product_id" ] && $requestData->action != "deposit" ) continue;

    $API->DB->insertInto( "salesProductsList" )
        ->values( $product )
        ->execute();

} // foreach. $requestData->pay_object as $service


$API->addEvent( "salesList" );