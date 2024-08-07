<?php

/**
 * Подсчёт суммы списания с карты и наличными в зависимости от выбранного типа оплаты
 */
$formFieldsUpdate[ "sum_cash" ][ "is_disabled" ] = true;
$formFieldsUpdate[ "sum_card" ][ "is_disabled" ] = true;

$amountOfPhysicalPayments = $amountOfPhysicalPayments ?? (
    $requestData->summary - ( $requestData->sum_bonus + $requestData->sum_deposit ) );

$requestData->pay_method = $requestData->pay_method ?? false;

if( !$requestData->pay_method ) {

    $formFieldsUpdate[ "sum_card" ][ "is_visible" ] = false;
    $formFieldsUpdate[ "sum_cash" ][ "is_visible" ] = false;
    $API->returnResponse( $formFieldsUpdate );

}

//$publicAppPath = $API::$configs[ "paths" ][ "public_app" ];
//require_once $publicAppPath . '/custom-libs/sales/business_logic.php' ;
//require_once "update-products.php";

require_once "sum-fields-update.php";
$API->returnResponse( $formFieldsUpdate );