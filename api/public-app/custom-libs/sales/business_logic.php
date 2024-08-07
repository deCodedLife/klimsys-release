<?php

//ini_set( "display_errors", true );

global $API, $requestData;

require_once "get-products.php";
$saleSummary += $productsPrice ?? 0;

if ( ( $requestData->discount_type ?? "" ) === "fixed"   ) $saleSummary -= ( $requestData->discount_value ?? 0 );
if ( ( $requestData->discount_type ?? "" ) === "percent" ) $saleSummary -= ( $saleSummary / 100 ) * ( $requestData->discount_value ?? 0 );

$sum_cash = $sum_cash ?? 0;
$sum_card = $sum_card ?? 0;
$saleSummary = max( $saleSummary, 0 );
include_once "calculate-promotions.php";
include_once "collect-receipt.php";


if ( $sum_cash > $amountOfPhysicalPayments ) $sum_cash = $amountOfPhysicalPayments;
if ( $sum_card > $amountOfPhysicalPayments ) $sum_card = $amountOfPhysicalPayments;