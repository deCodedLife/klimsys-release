<?php

$purchaseDetail = $API->DB->from( "purchases" )
    ->where( "id", $requestData->id )
    ->limit( 1 )
    ->fetch();



if ( $purchaseDetail[ "purchaseType" ] == "consumables" ) {

    $purchases_consumables = $API->DB->from( "purchases_consumables" )
        ->where( "row_id", $requestData->id );

    foreach ( $purchases_consumables as $purchase_consumable ){

        $consumableActive = $API->DB->from( "warehouses" )
            ->where( [
                "consumable_id" => $purchase_consumable[ "consumable_id" ],
                "store_id" => $purchaseDetail[ "store_id" ]
            ] )
            ->limit( 1 )
            ->fetch();

        $API->DB->update( "warehouses" )
            ->set([
                "count" =>  (int)$consumableActive[ "count" ] - (int)$purchase_consumable[ "count" ]
            ])
            ->where( [
                "consumable_id" => (int)$purchase_consumable[ "consumable_id" ],
                "store_id" => (int)$purchaseDetail[ "store_id" ]
            ] )
            ->execute();

    }


    foreach ($requestData->purchases_consumables as $consumable) {

        $consumableActive = $API->DB->from("warehouses")
            ->where([
                "consumable_id" => $consumable->consumable_id,
                "store_id" => $purchaseDetail[ "store_id" ]
            ])
            ->limit(1)
            ->fetch();

        $API->DB->update("warehouses")
            ->set([
                "count" => $consumableActive["count"] + $consumable->count
            ])
            ->where([
                "consumable_id" => $consumable->consumable_id,
                "store_id" => (int)$purchaseDetail[ "store_id" ]
            ])
            ->execute();

    }

}

if ( $purchaseDetail[ "purchaseType" ] == "products" ) {

    $purchases_products = $API->DB->from( "purchases_products" )
        ->where( "row_id", $requestData->id );

    foreach ( $purchases_products as $purchase_product ){

        $productActive = $API->DB->from( "warehouses" )
            ->where( [
                "product_id" => $purchase_product[ "product_id" ],
                "store_id" => $purchaseDetail[ "store_id" ]
            ] )
            ->limit( 1 )
            ->fetch();

        $API->DB->update( "warehouses" )
            ->set([
                "count" =>  (int)$productActive[ "count" ] - (int)$purchase_product[ "count" ]
            ])
            ->where( [
                "product_id" => (int)$purchase_product[ "product_id" ],
                "store_id" => (int)$purchaseDetail[ "store_id" ]
            ] )
            ->execute();

    }


    foreach ($requestData->purchases_products as $product) {

        $productActive = $API->DB->from("warehouses")
            ->where([
                "product_id" => $product->product_id,
                "store_id" => $purchaseDetail[ "store_id" ]
            ])
            ->limit(1)
            ->fetch();

        $API->DB->update("warehouses")
            ->set([
                "count" => $productActive["count"] + $product->count
            ])
            ->where([
                "product_id" => $product->product_id,
                "store_id" => (int)$purchaseDetail[ "store_id" ]
            ])
            ->execute();

    }

}
