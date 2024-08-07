<?php
//ini_set( "display_errors", true );
/**
 * Обновление полей
 */
$formFieldsUpdate = $formFieldsUpdate ?? [];
$isReturn = ( $requestData->action ?? 'sell' ) === "sellReturn";

$amountOfPhysicalPayments = $amountOfPhysicalPayments ?? (
    $requestData->summary - ( $requestData->sum_bonus + $requestData->sum_deposit ) );
require_once "sum-fields-update.php";


if ( $requestData->action == "deposit" ) {

    $isReturn = false;
    $sum_card = $requestData->sum_card ?? 0;
    $sum_cash = $requestData->sum_cash ?? 0;
    $saleSummary = $sum_cash + $sum_card;

    $formFieldsUpdate[ "products" ] = $formFieldsUpdate[ "products" ] ?? [];
    $formFieldsUpdate[ "products" ][ "value" ][] = [
        "title" => "Пополнение депозита",
        "type" => "product",
        "cost" => $requestData->summary,
        "amount" => 1,
        "product_id" => 0
    ];

} // if ( $requestData->action !== "deposit" )


if ( $isReturn ) {

    $formFieldsUpdate[ "sum_deposit" ][ "is_visible" ] = false;
    $formFieldsUpdate[ "sum_bonus" ][ "is_visible" ] = false;

} // if ( isReturn )


$API->returnResponse( $formFieldsUpdate );