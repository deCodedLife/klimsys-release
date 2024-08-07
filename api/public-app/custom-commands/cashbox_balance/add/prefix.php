<?php

$today = new DateTime();
$today->modify( "-1 day" );

$start = $today->format( "Y-m-d 00:00:00" );
$end = $today->format( "Y-m-d 23:59:59" );

$today->modify( "-1 day" );
$last_day = $today->format( "Y-m-d 00:00:00" );


foreach ( $stores = $API->DB->from( "stores" ) as $store ) {

    ini_set( "display_errors", 1 );

    $lastBalance = $API->DB->from( "cashboxBalances" )
        ->where( "store_id", $store[ "id" ] )
        ->orderBy( "id DESC" )
        ->fetch()[ "balance" ] ?? 0;


    $balance = $API->DB->from( "salesList" )
        ->select( null )
        ->select( "ROUND( SUM( sum_cash ), 2 ) as summary" )
        ->where(
            [
                "created_at >= ?" => $start,
                "created_at <= ?" => $end,
                "store_id" => $store[ "id" ],
                "status" => "done",
                "pay_method" => [ "cash", "parts" ]
            ]
        )
        ->fetch()[ "summary" ] ?? 0;


    $expenses = $API->DB->from( "expenses" )
        ->select( null )
        ->select( "ROUND( SUM( price ), 2 ) as expenses" )
        ->where( [
                "created_at >= ?" => $start,
                "created_at <= ?" => $end,
                "store_id" => $store[ "id" ]
        ] )
        ->fetch()[ "expenses" ] ?? 0;


    $API->DB->insertInto( "cashboxBalances" )
        ->values( [
            "balance" => max( $lastBalance + $balance - $expenses, 0 ),
            "store_id" => $store[ "id" ],
            "created_at" => $start
        ] )
        ->execute();

}


$API->returnResponse();