<?php

if ( $requestData->purchases_consumables ) {

    foreach ( $requestData->purchases_consumables as $consumable ) {

        $consumableActive = $API->DB->from( "warehouses" )
            ->where( [
                "consumable_id" => $consumable->consumable_id,
                "store_id" => $requestData->store_id
            ] )
            ->limit( 1 )
            ->fetch();

        if ( $consumableActive ) {

            $API->DB->update( "warehouses" )
                ->set([
                    "count" => $consumableActive[ "count" ] + $consumable->count
                ])
                ->where( [
                    "consumable_id" => $consumable->consumable_id,
                    "store_id" => $requestData->store_id
                ] )
                ->execute();

        } else {

            $API->DB->insertInto("warehouses")
                ->values([
                    "consumable_id" => $consumable->consumable_id,
                    "count" => $consumable->count,
                    "store_id" => $requestData->store_id
                ])
                ->execute();

        }

    }

}

if ( $requestData->purchases_products ) {

    foreach ( $requestData->purchases_products as $product ) {

        $consumableActive = $API->DB->from( "warehouses" )
            ->where( [
                "product_id" => $product->product_id,
                "store_id" => $requestData->store_id
            ] )
            ->limit( 1 )
            ->fetch();

        if ( $consumableActive ) {

            $API->DB->update( "warehouses" )
                ->set([
                    "count" => $consumableActive[ "count" ] + $product->count
                ])
                ->where( [
                    "product_id" => $product->product_id,
                    "store_id" => $requestData->store_id
                ] )
                ->execute();

        } else {

            $API->DB->insertInto("warehouses")
                ->values([
                    "product_id" => $product->product_id,
                    "count" => $product->count,
                    "store_id" => $requestData->store_id
                ])
                ->execute();

        }

    }

}
