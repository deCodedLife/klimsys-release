<?php

$formFieldsUpdate = [];

if ( $cash_sum > $amountOfPhysicalPayments ) $cash_sum = $amountOfPhysicalPayments;
if ( $card_sum > $amountOfPhysicalPayments ) $card_sum = $amountOfPhysicalPayments;



/**
 * Подсчёт суммы списания с карты и наличными в зависимости от выбранного типа оплаты
 */

if ( $requestData->pay_method == "card" ) {

    $formFieldsUpdate[ "cash_sum" ][ "is_visible" ] = false;
    $formFieldsUpdate[ "card_sum" ][ "is_visible" ] = true;
    $card_sum = $amountOfPhysicalPayments;
    $cash_sum = 0;

} // if. $requestData->pay_method == "card"

if ( $requestData->pay_method == "parts" ) {

    $formFieldsUpdate[ "card_sum" ][ "is_visible" ] = true;
    $formFieldsUpdate[ "cash_sum" ][ "is_visible" ] = true;
    $card_sum = $amountOfPhysicalPayments - $cash_sum;
    $card_sum = $cash_sum >= $amountOfPhysicalPayments ? 0 : $card_sum;

} // if. $requestData->pay_method == "parts"

if ( $requestData->pay_method == "cash" ) {

    $formFieldsUpdate[ "card_sum" ][ "is_visible" ] = false;
    $formFieldsUpdate[ "cash_sum" ][ "is_visible" ] = true;
    $cash_sum = $amountOfPhysicalPayments;
    $card_sum = 0;

} // if. $requestData->pay_method == "cash"



/**
 * Заполнение и отправка формы
 */

$clientDetails = $API->DB->from( "clients" )
    ->where( "id", $requestData->client_id )
    ->fetch();


foreach ( $saleVisits as $visit )
    $formFieldsUpdate[ "visits_ids" ][ "value" ][] = $visit[ "id" ];

foreach ( $saleServices as $service )
    $formFieldsUpdate[ "pay_object" ][ "value" ][] = $service[ "id" ];

$formFieldsUpdate[ "services" ][ "value" ] = $saleServices;



$formFieldsUpdate[ "cash_sum" ][ "value" ] = max( $cash_sum, 0 );
$formFieldsUpdate[ "card_sum" ][ "value" ] = max( $card_sum, 0 );
$formFieldsUpdate[ "summary" ][ "value" ] = $saleSummary;

$formFieldsUpdate[ "pay_type" ][ "is_visible" ] = false;
$formFieldsUpdate[ "visits_ids" ][ "is_visible" ] = false;
$formFieldsUpdate[ "store_id" ][ "is_visible" ] = false;
$formFieldsUpdate[ "client_id" ][ "is_visible" ] = count(
    $API->DB->from( "visits_clients" )
        ->where( "visit_id", $saleVisits[ 0 ][ "id" ] )
) > 1;

if ( $is_return ) {

    $formFieldsUpdate[ "deposit_sum" ][ "is_visible" ] = false;
    $formFieldsUpdate[ "bonus_sum" ][ "is_visible" ] = false;

}