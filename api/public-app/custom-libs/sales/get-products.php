<?php

$products = [];

function fetchProduct ( int $id, int $amount ): array
{
    global $API;

    $productDetails = $API->DB->from( "products" )
        ->where( "id", $id )
        ->fetch();

    $manufacturer = $API->DB->from( "providers" )
        ->where( "id", $productDetails[ "manufacturer" ] )
        ->fetch();

    return [
        "title" => "{$manufacturer[ "title" ]} {$productDetails[ "series" ]} {$productDetails[ "model" ]}",
        "product_id" => $id,
        "amount" => $amount,
        "cost" => $productDetails[ "price" ] * $manufacturer[ "exchange_rate" ] * $manufacturer[ "coefficient" ],
        "price" => $productDetails[ "price" ] * $manufacturer[ "exchange_rate" ] * $manufacturer[ "coefficient" ],
        "type" => "product"
    ];
}

function fetchService ( int $id, int $amount ): array
{
    global $API;

    $serviceDetails = $API->DB->from( "services" )
        ->where( "id", $id )
        ->fetch();

    return [
        "title" => $serviceDetails[ "title" ],
        "product_id" => $id,
        "amount" => $amount,
        "cost" => $serviceDetails[ "price" ],
        "price" => $serviceDetails[ "price" ],
        "type" => "service",
        "related" => $serviceDetails[ "depend_from" ]
    ];
}

if ( property_exists( $requestData, "sale_products" ) )
{
    $requestData->products = [];

    foreach ( $requestData->sale_products as $product ) {
        $parts = explode( "#", $product->id );

        $type = $parts[ 0 ];
        $peoduct_id = intval( $parts[ 1 ] );

        $productDetails = $API->DB->from( "{$type}s" )
            ->where( "id", $peoduct_id )
            ->fetch();

        $requestData->products[] = (object) [
            "type" => $type,
            "product_id" => $peoduct_id,
            "amount" => $product->amount,
            "related" => $productDetails[ "depend_from" ] ?? null
        ];
    }
}

foreach ( $requestData->products as $product ) {

    $table = $product->type == "product" ? "products" : "services";

    if ( $product->type === "product" ) {

        $products[] = fetchProduct( $product->product_id, $product->amount );
        continue;

    }

    $service = fetchService( $product->product_id, $product->amount );
    $products[] = $service;

    $hasRelated = false;

    foreach ( $requestData->products as $relatedProduct )
    {
        if ( $relatedProduct->type != $product->type ) continue;
        if ( $relatedProduct->product_id != $product->related ) continue;
        $hasRelated = true;
    }

    if ( $hasRelated ) continue;

    while ( $service[ "related" ] != null )
    {
        $service = fetchService( $service[ "related" ], 1 );
        $products[] = $service;
    }
}

$productsPrice = 0;
foreach ( $products as $product ) $productsPrice += $product[ "cost" ] * $product[ "amount" ];