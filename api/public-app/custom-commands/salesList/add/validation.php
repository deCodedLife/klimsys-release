<?php


/**
 * Получение детальной информации о клиенте
 */

if ( !isset( $requestData->products ) ) $API->returnResponse( "Ошибка. Продукты не подгрузились", 500 );

if ( $requestData->pay_method != "parts" && $requestData->pay_method != "online" ) {

    if ( $requestData->sum_cash != 0 && $requestData->pay_method != "cash" )
        $API->returnResponse( "Ошибка. Несовпадение типа и значений оплаты", 500 );

    if ( $requestData->sum_card != 0 && $requestData->pay_method != "card" )
        $API->returnResponse( "Ошибка. Несовпадение типа и значений оплаты", 500 );

}

foreach ( $requestData->products as $product ) {

    if (
        !isset( $product->title ) ||
        !isset( $product->product_id )
    ) $API->returnResponse( "Ошибка. Продукты сформированы некорректно", 500 );

}

$clientDetails = $API->DB->from( "clients" )
    ->where( "id", $requestData->client_id )
    ->fetch();



/**
 * Проверка корректности суммы оплаты с суммой посещения
 */

$saleSummary = 0;
$paymentsSummary =
    $requestData->sum_card +
    $requestData->sum_cash +
    $requestData->sum_entity;



/**
 * Валидация итоговой суммы
 */

if ( $paymentsSummary != $requestData->summary )
    $API->returnResponse( "Сумма всех способов оплаты не совпадает с итоговой суммой посещения", 400 );