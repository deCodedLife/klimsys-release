<?php

$start = ( $requestData->start_at ?? date( 'Y-m-d', strtotime( "-1 day" ) ) ) . " 00:00:00";
$end = ( $requestData->end_at ?? date( 'Y-m-d', strtotime( "-1 day" ) ) ) . " 23:59:59";

$stores = $API->DB->from( "stores" )
    ->fetchAll();

foreach ( $stores as $store ) {

    $incomeBalance = $API->DB->from( "cashboxBalances" )
        ->where( "store_id", $store[ "id" ] )
        ->orderBy( "created_at DESC" )
        ->limit( 1 )
        ->fetch();

    $filters = [
        "status" => "done",
        "created_at >= ?" => $start,
        "created_at <= ?" => $end,
        "store_id" => $store[ "id" ]
    ];

    $payments = $API->DB->from( "salesList" )
        ->where( $filters );

    unset( $filters[ "action" ] );
    unset( $filters[ "pay_method" ] );
    unset( $filters[ "status" ] );

    $expenses = $API->DB->from( "expenses" )
        ->where( $filters );

    $summary = 0;
    $expenses_summary = 0;

    foreach ( $expenses as $expense ) {

        $expenses_summary += $expense[ "price" ];

    }

    foreach ( $payments as $payment ) {

        if ( $payment[ "action" ] === "sellReturn" ) {
            $summary -= $payment[ "sum_cash" ];
            continue;
        };
        $summary += $payment[ "sum_cash" ];

    }

    $incomeBalance[ "balance" ] = $incomeBalance[ "balance" ] ?? 0;
    $summary += $incomeBalance[ "balance" ] - $expenses_summary;

    $API->DB->insertInto( "cashboxBalances" )
        ->values( [
            "store_id" => $store[ "id" ],
            "balance" => $summary,
            "created_at" => date( "Y-m-d 00:00:00", strtotime( "-1 day" ) ),
        ] )
        ->execute();

}