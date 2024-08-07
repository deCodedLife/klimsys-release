<?php

/**
 * @file
 * Список "Рекламные источники
 */

$filter = [];
if ( $requestData->start_at ) $filter[ "created_at >= ?" ] = $requestData->start_at . " 00:00:00";
if ( $requestData->end_at ) $filter[ "created_at <= ?" ] = $requestData->end_at . " 23:59:59";

if ( $requestData->start_price ) $visitsFilter[ "price >= ?" ] = $requestData->start_price;
if ( $requestData->end_price ) $visitsFilter[ "price <= ?" ] = $requestData->end_price;
if ( $requestData->store_id ) $visitsFilter[ "store_id" ] = $requestData->store_id;


$advertiseReturn = [];

foreach ( $response[ "data" ] as $advertise ) {

    $filter[ "advertise_id" ] = $advertise[ "id" ];

    $clientsIds = $API->DB->from( "clients" )
        ->select( null )
        ->select( "id" )
        ->where( $filter );

    $clientsIds = array_keys( $clientsIds->fetchAll( "id" ) );

    $cancelVisits[ "count" ] = 0;
    $endedVisits[ "count" ] = 0;
    $visits[ "count" ] = 0;
    $visitsPrice[ "summary" ] = 0;

    if ( !empty( $clientsIds ) ) {

        $visitsFilter[ "client_id" ] = $clientsIds;

        $visits = $API->DB->from( "visits" )
            ->select( null )
            ->select( [
                "COUNT( id ) as count",
                "SUM( CASE WHEN status = 'ended' THEN 1 ELSE 0 END ) as ended",
                "SUM( CASE WHEN status = 'canceled' THEN 1 ELSE 0 END ) as canceled",
                "SUM( CASE WHEN is_payed = 'Y' THEN price ELSE 0 END ) as price",
                "client_id"
            ] )
            ->where( $visitsFilter )
            ->groupBy( "client_id" )
            ->fetchAll( "client_id" );

        foreach ( $visits as $visit ) {

            $cancelVisits[ "count" ] += $visit[ "canceled" ];
            $endedVisits[ "count" ] += $visit[ "ended" ];
            $visits[ "count" ] += $visit[ "count" ];
            $visitsPrice[ "summary" ] += $visit[ "price" ];

        }

    }

    $advertiseReturn[] = [

        "id" => $advertise[ "id" ],
        "title" => $advertise[ "title" ],
        "start_at" => $advertise[ "start_at" ],
        "end_at" => $advertise[ "end_at" ],
        "store_id" => $advertise[ "store_id" ],
        "advertise_id" => $advertise[ "advertise_id" ],
        "clientsCount" => count($clientsIds),
        "recordedCount" => $visits[ "count" ] - $endedVisits[ "count" ] - $cancelVisits[ "count" ],
        "extantCount" => $endedVisits[ "count" ],
        "underdoneCount" => $cancelVisits[ "count" ],
        "visitsCount" => $visits[ "count" ],
        "price" => $visitsPrice[ "summary" ]

    ];

}

$response[ "data" ] = $advertiseReturn;