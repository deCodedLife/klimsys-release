<?php

/**
 * @file
 * Хуки на Закупку
 */
$formFieldsUpdate = [];


/**
 * Стоимость закупки
 */
$purchaseCost = 0;


/**
 * Вывод умных списков
 */
switch ( $requestData->purchaseType ) {

    case "products":

        $formFieldsUpdate[ "purchases_products" ] = [
            "is_visible" => true
        ];

        $formFieldsUpdate[ "purchases_consumables" ] = [
            "is_visible" => false
        ];

        break;

    case "consumables":

        $formFieldsUpdate[ "purchases_products" ] = [
            "is_visible" => false
        ];

        $formFieldsUpdate[ "purchases_consumables" ] = [
            "is_visible" => true
        ];

        break;

} // switch. $requestData->purchaseType


/**
 * Расчет стоимости закупки
 */

if ( $requestData->purchases_products ) {

    foreach ( $requestData->purchases_products as $productKey => $product ) {

        $purchaseCost += $product->price;
        $product->priceOnce = $product->price / $product->count;

        $formFieldsUpdate[ "purchases_products" ][ "value" ][ $productKey ] = $product;

    } // foreach. $requestData->purchases_products

} // if. $requestData->purchases_products

if ( $requestData->purchases_consumables ) {

    foreach ( $requestData->purchases_consumables as $consumableKey => $consumable ) {

        $purchaseCost += $consumable->price;
        $consumable->priceOnce = $consumable->price / $consumable->count;

        $formFieldsUpdate[ "purchases_consumables" ][ "value" ][ $consumableKey ] = $consumable;

    } // foreach. $requestData->purchases_consumables

} // if. $requestData->purchases_consumables

$formFieldsUpdate[ "price" ][ "value" ] = $purchaseCost;


$API->returnResponse( $formFieldsUpdate );
