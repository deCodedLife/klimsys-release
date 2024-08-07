<?php

function AddToReceipt( array $products, float $discount ): array
{
    $formFieldsUpdate = [];
    if ( !$products ) return $formFieldsUpdate;

    foreach ( $products as $product ) {

        $id = $product[ "id" ] ?? $product[ "product_id" ] ?? 0;
        $title = $product[ "title" ] ?? "NO TITLE";
        $price = $product[ "price" ] ?? 0;
        $amount = $product[ "amount" ] ?? 1;
        $type = $product[ "type" ] ?? "product";

        $formFieldsUpdate[] = [
            "title" => $title,
            "type" => $type,
            "cost" => floatval( $price ),
            "discount" => round( $price - ( $price * $discount ), 2 ),
            "amount" => $amount,
            "product_id" => intval( $id )
        ];

    }

    return $formFieldsUpdate;
}


/**
 * Вычет депозита и бонусов для расчёта сумм налички и карты
 */
$amountOfPhysicalPayments = 0;
$amountOfPhysicalPayments = $saleSummary - ( ($requestData->sum_bonus ?? 0) + ($requestData->sum_deposit ?? 0) );

$saleServicesPrice = 0;
$allServicesPrice = 0;
$productsPrice = $productsPrice ?? 0;

/**
 * Подсчёт стоимости посещения без скидок
 */

foreach ( $products as $service )
    $allServicesPrice += $service[ "price" ];

foreach ( $products as $service )
    $saleServicesPrice += $service[ "price" ];



/**
 * Нахождение скидки для товаров по формуле (стоимость со скидками / стоимость без скидок)
 */

$discountPerProduct = 0;

if ( $allServicesPrice + $productsPrice != 0 ) {

    $discountPerProduct = $amountOfPhysicalPayments / ( $allServicesPrice + $productsPrice );

}


/**
 * Нахождение суммы для налички и карты с учётом скидок
 */

$amountOfPhysicalPayments = ( $saleServicesPrice + $productsPrice ) * $discountPerProduct;
$amountOfPhysicalPayments = round( $amountOfPhysicalPayments, 2 );