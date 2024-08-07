<?php

require_once $API::$configs[ "paths" ][ "public_app" ] . "/custom-libs/sales/promotions/index.php";
use Sales\Modifier as Modifier;

foreach ( $requestData as $key => $property ) {

    if ( $property == null ) {

        $scheme = $API->loadScheme( $API::$configs[ "paths" ][ "public_app" ] . "/object-schemes/promotions.json" );

        foreach ( $scheme[ "properties" ] as $prop ) {

            if ( $prop[ "article" ] == $key ) {

                if ( $prop[ "data_type" ] == "array" ) {

                    $API->DB->deleteFrom( $prop[ "join" ][ "connection_table" ] )
                        ->where( [
                            "promotion_id" => $requestData->id,
                            "type" => $prop[ "join" ][ "donor_table" ]
                        ] )
                        ->execute();

                } else {

                    $API->DB->update( $scheme[ "table" ] )
                        ->set( [ $prop[ "article" ] => null ] )
                        ->where( [
                            "id" => $requestData->id
                        ] )
                        ->execute();

                }

            }

        }

    }

}

if ( is_array( $requestData->services )  )              Modifier::removeByPattern( $requestData->id, new Modifier( null, "services" ) );
if ( is_array( $requestData->servicesGroups ) )         Modifier::removeByPattern( $requestData->id, new Modifier( null, "services", true ) );
if ( is_array( $requestData->requiredServices ) )       Modifier::removeByPattern( $requestData->id, new Modifier( null, "services", false, true ) );
if ( is_array( $requestData->requiredServicesGroups ) ) Modifier::removeByPattern( $requestData->id, new Modifier( null, "services", true, true ) );
if ( is_array( $requestData->excludedServices ) )       Modifier::removeByPattern( $requestData->id, new Modifier( null, "services", false, false, true ) );
if ( is_array( $requestData->excludedServicesGroups ) ) Modifier::removeByPattern( $requestData->id, new Modifier( null, "services", true, false, true ) );
if ( is_array( $requestData->clientsGroups ) )          Modifier::removeByPattern( $requestData->id, new Modifier( null, "clients",  true ) );

foreach ( $requestData->services as $service )
    Modifier::writeModifier( $requestData->id, new Modifier( $service, "services" ) );

foreach ( $requestData->servicesGroups as $serviceGroup )
    Modifier::writeModifier( $requestData->id, new Modifier( $serviceGroup, "services", true ) );

foreach ( $requestData->requiredServices as $service )
    Modifier::writeModifier( $requestData->id, new Modifier( $service, "services", false, true ) );

foreach ( $requestData->requiredServicesGroups as $serviceGroup )
    Modifier::writeModifier( $requestData->id, new Modifier( $serviceGroup, "services", true, true ) );

foreach ( $requestData->excludedServices as $service )
    Modifier::writeModifier( $requestData->id, new Modifier( $service, "services", false, false, true ) );

foreach ( $requestData->excludedServicesGroups as $serviceGroup )
    Modifier::writeModifier( $requestData->id, new Modifier( $serviceGroup, "services", true, false, true ) );

foreach ( $requestData->clientsGroups as $clientGroup )
    Modifier::writeModifier( $requestData->id, new Modifier( $clientGroup, "clients", true ) );