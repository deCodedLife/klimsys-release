<?php

function getProducts ( string $type, string $table, array $titleFields = [ "title" ] ): array
{
    global $API;

    $productList = [];

    foreach ( $API->DB->from( $table )->fetchAll() as $product ) {
        $title = [];

        foreach ( $titleFields as $field ) $title[] = $product[ $field ];
        $title = join( ' ', $title );

        $productList[] = [
            "type" => $type,
            "product_id" => $product[ "id" ],
            "title" => $title,
            "amount" => 1,
            "cost" => $product[ "price" ],
            "related" => $product[ "depend_from" ] ?? null,
        ];
    }

    return $productList;
}

$productList = array_merge(
    getProducts( "product", "products", [ "series", "model" ] ),
    getProducts( "service", "services" ),
);

$API->returnResponse( $productList );