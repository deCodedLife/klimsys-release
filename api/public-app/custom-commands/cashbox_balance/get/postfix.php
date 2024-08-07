<?php

//$API->returnResponse( json_encode($requestData), 500 );

$today = new DateTime();

$start = ( $requestData->start_at ?? $today->format( "Y-m-d" ) ) . " 00:00:00";
$end = ( $requestData->end_at ?? $today->format( "Y-m-d" ) ) . " 23:59:59";

$filters = [
    "created_at >= ?" => $start,
    "created_at <= ?" => $end
];

if ( $requestData->store_id ) $filters[ "store_id" ] = $requestData->store_id;

/**
 * Получение списка продаж
 */
$payments = $API->DB->from( "salesList" )
    ->where( $filters )
    ->orderBy( "created_at DESC" );

$expenses = $API->DB->from( "expenses" )
    ->where( $filters )
    ->orderBy( "created_at DESC" );


/**
 * Если возвращаемый тип - список
 */
if ( $requestData->context->block === "list" ) {

    $listData = [];

    foreach ( $expenses as $expense ) {

        $listItem = [];
        $listItem[ "date" ] = $expense[ "created_at" ];

        $listItem[ "operation_type" ] = "Расход";
        $listItem[ "client_id" ] = $expense[ "user_id" ];

        $user = $API->DB->from( "users" )
            ->where( "id", $expense[ "user_id" ] )
            ->fetch();

        $rowTitle = $user[ "last_name" ] . " ";
        if ( $user[ "first_name" ] ) $rowTitle .= mb_substr( $user[ "first_name" ], 0, 1 ) . ". ";
        if ( $user[ "patronymic" ] ) $rowTitle .= mb_substr( $user[ "patronymic" ], 0, 1 ) . ". ";

        $listItem[ "client" ][] = [
            "title" => $rowTitle,
            "value" => $user[ "id" ]
        ];

        $listItem[ "summary" ] = $expense[ "price" ] * -1;
        $listItem[ "operator" ] = $rowTitle;

        $listData[] = $listItem;

    }

    /**
     * Обход всех продаж
     */
    foreach ( $payments as $payment ) {

        if ( in_array( $payment[ "pay_method" ], [ "card", "legalEntity", "online" ] ) ) continue;
        if ( $payment[ "status" ] != "done" ) continue;

        $listItem = [];
        $listItem[ "date" ] = $payment[ "created_at" ];

        if ( $payment[ "action" ] === "sell" ) $listItem[ "operation_type" ] = "Продажа";
        if ( $payment[ "action" ] === "sellReturn" ) $listItem[ "operation_type" ] = "Возврат";

        $listItem[ "client_id" ] = $payment[ "client_id" ];

        $client = mysqli_fetch_array(mysqli_query(
            $API->DB_connection,
            "SELECT * FROM clients WHERE id = {$payment[ "client_id" ]}"
        ));

        $clientName = $client[ "last_name" ] . " ";
        if ( $client[ "first_name" ] ) $clientName .= mb_substr( $client[ "first_name" ], 0, 1 ) . ". ";
        if ( $client[ "patronymic" ] ) $clientName .= mb_substr( $client[ "patronymic" ], 0, 1 ) . ". ";

        $listItem[ "client" ][] = [
            "title" => $clientName,
            "value" => $client[ "id" ]
        ];

        $listItem[ "summary" ] = round( $payment[ "sum_cash" ], 2 );

        $employee = mysqli_fetch_array( mysqli_query(
            $API->DB_connection,
            "SELECT * FROM users WHERE id = {$payment[ "employee_id" ]}"
        ) );

        $userName = $employee[ "last_name" ] . " ";
        if ( $employee[ "first_name" ] ) $userName .= mb_substr( $employee[ "first_name" ], 0, 1 ) . ". ";
        if ( $employee[ "patronymic" ] ) $userName .= mb_substr( $employee[ "patronymic" ], 0, 1 ) . ". ";

        $listItem[ "operator" ] = $userName;
        $listData[] = $listItem;

    } // foreach ( $payments as $payment )

    $response[ "data" ] = $listData;

} // if ( $requestData->context->block === "list" )