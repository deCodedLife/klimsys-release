<?php

$sum_cash = $requestData->sum_cash ?? 0;
$sum_card = $requestData->sum_card ?? 0;
$requestData->pay_method = $requestData->pay_method ?? false;

if ( $requestData->pay_method == "card" || $requestData->pay_method == "online" ) {

    $formFieldsUpdate[ "sum_cash" ][ "is_visible" ] = false;
    $formFieldsUpdate[ "sum_card" ][ "is_visible" ] = true;

    $sum_card = $amountOfPhysicalPayments ?? $saleSummary;
    $sum_cash = 0;


} // if. $requestData->pay_method == "card"

if ( $requestData->pay_method == "parts" ) {

    $formFieldsUpdate[ "sum_card" ][ "is_visible" ] = true;
    $formFieldsUpdate[ "sum_cash" ][ "is_visible" ] = true;

    $formFieldsUpdate[ "sum_cash" ][ "is_disabled" ] = false;

    $sum_card = $amountOfPhysicalPayments - $sum_cash;
    $sum_card = $sum_cash >= ( $amountOfPhysicalPayments ?? $saleSummary ) ? 0 : $sum_card;

    if ( $sum_card == 0 ) {
        $sum_cash = $amountOfPhysicalPayments;
    }

} // if. $requestData->pay_method == "parts"

if ( $requestData->pay_method == "cash" ) {

    $formFieldsUpdate[ "sum_card" ][ "is_visible" ] = false;
    $formFieldsUpdate[ "sum_cash" ][ "is_visible" ] = true;
    $sum_cash = $amountOfPhysicalPayments ?? $saleSummary;
    $sum_card = 0;

} // if. $requestData->pay_method == "cash"


$formFieldsUpdate[ "summary" ][ "value" ] = $saleSummary ?? $requestData->summary;
$formFieldsUpdate[ "sum_cash" ][ "value" ] = max( $sum_cash, 0 );
$formFieldsUpdate[ "sum_card" ][ "value" ] = max( $sum_card, 0 );