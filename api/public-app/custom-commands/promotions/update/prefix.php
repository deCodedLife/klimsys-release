<?php

$updates = (array) $requestData;
$stores = $requestData->stores_id ?? [];

unset( $updates[ "id" ] );
unset( $updates[ "services" ] );
unset( $updates[ "servicesGroups" ] );
unset( $updates[ "requiredServices" ] );
unset( $updates[ "requiredServicesGroups" ] );
unset( $updates[ "clientsGroups" ] );
unset( $updates[ "excludedServices" ] );
unset( $updates[ "excludedServicesGroups" ] );
unset( $updates[ "stores_id" ] );

if ( empty( $updates ) == false ) {

    $API->DB->update( "promotions" )
        ->set( $updates )
        ->where( "id", $requestData->id )
        ->execute();

} // if empty( $updates ) == false

if ( $requestData->stores_id  ) {

    $API->DB->delete( "promotionStores" )
        ->where( "promotion_id", $requestData->id )
        ->execute();

    foreach ( $stores as $store ) {

        $API->DB->insertInto( "promotionStores" )
            ->values( [
                "promotion_id" => $requestData->id,
                "store_id" => $store
            ] )
            ->execute();

    }

}




require $API::$configs[ "paths" ][ "public_app" ] . "/custom-commands/promotions/update/update-modifiers.php";


$API->returnResponse();
