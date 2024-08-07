<?php


foreach ( $services as $service )
    $formFieldsUpdate[ "products_display" ][ "value" ][] = $service[ "title" ];

$formFieldsUpdate[ "products" ][ "value" ] = array_merge(
    AddToReceipt( $services ?? [], $discountPerProduct ),
    $formFieldsUpdate[ "products" ][ "value" ] ?? []
);

$formFieldsUpdate[ "products" ][ "value" ] = array_merge(
    AddToReceipt( $products ?? [], $discountPerProduct ),
    $formFieldsUpdate[ "products" ][ "value" ] ?? []
);

$formFieldsUpdate[ "summary" ][ "value" ] = max( $amountOfPhysicalPayments, 0 );