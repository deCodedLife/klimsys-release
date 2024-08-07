<?php

/**
 * Проверка наличия обязательного филтьтра
 */
if (!$requestData->user_id) $API->returnResponse([]);


/**
 * Сформированный список
 */
$returnData= [];

/**
 * Фильтр Продаж
 */
$filter = [];
$filter[ "salesList.status" ] = "done";
if ( $requestData->user_id ) $filter[ "visits.user_id" ] = $requestData->user_id;
if ( $requestData->store_id ) $filter[ "visits.store_id" ] = $requestData->store_id;
if ( $requestData->start_at ) $filter[ "visits.start_at >= ?" ] = $requestData->start_at;
if ( $requestData->end_at ) $filter[ "visits.end_at <= ?" ] = $requestData->end_at;

$visitsList = $API->DB->from( "salesList" )
    ->select( null )->select(
        [
            "salesList.id",
            "salesProductsList.product_id",
            "saleVisits.visit_id",
            "services.title",
            "services.category_id",
            "salesProductsList.cost",
            "visits.user_id",
            "visits.store_id",
            "salesProductsList.amount",
            "salesProductsList.discount"
        ]
    )
    ->where($filter)
    ->innerJoin( "salesProductsList on salesProductsList.sale_id = salesList.id" )
    ->innerJoin( "services on services.id = salesProductsList.product_id" )
    ->innerJoin( "saleVisits on saleVisits.sale_id = salesList.id" )
    ->innerJoin( "visits on visits.id = saleVisits.visit_id" );


foreach ( $visitsList as $visit ) {

    $returnData[] = [
        "title" => $visit[ "title" ],
        "id" => $visit[ "product_id" ],
        "count" => $visit[ "amount" ],
        "price" => $visit[ "cost" ],
        "discount_value" => $visit[ "discount" ],
        "sum" => $visit[ "cost" ] - $visit[ "discount" ],
        "category_id" => $visit[ "category_id" ],
        "store_id" => $visit[ "store_id" ],
        "user_id" => $visit[ "user_id" ]
    ];

}

$returnVisits = [];

foreach ( $returnData as $visit ) {

    $productId = $visit[ "id" ];

    if (!isset( $returnVisits[ $productId ] )) {

        $returnVisits[ $productId ] = [
            "title" => $visit[ "title" ],
            "id" => $visit[ "id" ],
            "count" => $visit[ "count" ],
            "price" => $visit[ "price" ],
            "discount_value" => $visit[ "discount_value" ],
            "sum" => $visit[ "sum" ],
            "category_id" => $visit[ "category_id" ],
            "store_id" => $visit[ "store_id" ],
            "user_id" => $visit[ "user_id" ]
        ];
    } else {

        $returnVisits[$productId]["count"] += $visit["count"];
        $returnVisits[$productId]["price"] += $visit["price"];
        $returnVisits[$productId]["discount_value"] += $visit["discount_value"];
        $returnVisits[$productId]["sum"] += $visit["sum"];

    }
}

foreach ( $returnVisits as $key => $visit ) {

    if ( $requestData->start_price && $visit[ "sum" ] < $requestData->start_price ) unset( $returnVisits[$key] );
    if ( $requestData->end_price && $visit[ "sum" ] > $requestData->end_price ) unset( $returnVisits[$key] );

}


$returnVisits = array_values($returnVisits);

$response[ "data" ] = $returnVisits;

if ( $requestData->limit ) {

    $response[ "detail" ] = [

        "pages_count" => ceil(count($response[ "data" ]) / $requestData->limit),
        "rows_count" => count($response[ "data" ])

    ];
    
}


$response[ "data" ] = array_slice($response[ "data" ], $requestData->limit * $requestData->page - $requestData->limit, $requestData->limit);

