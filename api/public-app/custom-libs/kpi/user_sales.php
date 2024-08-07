<?php
//
//global $API, $requestData;
//
//
//$sales_summary = 0;
//$sales_count = 0;
//
//$services = [];
//
//$sales_ids = [];
//
//
///**
// * Получаем информацию о сотруднике
// */
//$userDetail = $API->DB->from( "users" )
//    ->where( "id", $requestData->user_id )
//    ->fetch();
//
///**
// * Берём продажи непосредственно из таблицы
// */
//
//
//function getSales( $user_id, $start_at, $end_at, $action ): array {
//
//    global $API;
//
//    $user_sales = mysqli_query(
//        $API->DB_connection, "
//        SELECT  *
//        FROM    salesList
//        WHERE   employee_id = $user_id
//                AND created_at > '$start_at'
//                AND created_at < '$end_at'
//                AND status = 'done'
//                AND action = '$action'"
//    );
//
//    foreach ( $user_sales as $sale ) $sales_ids[ $sale[ "id" ] ] = $sale;
//    return $sales_ids ?? [];
//
//}
//
//function getSummary( $sales_ids ): float {
//
//    global $API;
//
//    $sales_ids = join( ",", array_keys( $sales_ids ?? [] ) );
//    return floatval( mysqli_fetch_array(
//        mysqli_query(
//            $API->DB_connection,
//            "SELECT Sum( (amount * cost) - discount )
//        FROM   salesProductsList
//        WHERE  sale_id IN ($sales_ids) AND type = 'service'"
//        )
//    )[0] ?? 0 );
//
//}
//
//function getServices( $sales_ids, &$services ) {
//
//    global $API;
//
//    $sales_ids = join( ",", array_keys( $sales_ids ?? [] ) );
//    $servicesList = mysqli_query(
//        $API->DB_connection,
//        "SELECT *
//        FROM   salesProductsList
//        WHERE  sale_id IN ($sales_ids) AND type = 'service'"
//    );
//    foreach ( $servicesList as $service ) $services[ $service[ "product_id" ] ][] = $service;
//
//}
//
//$sales = getSales( $requestData->user_id, $start_at, $end_at, "sell" );
//$returns = getSales( $requestData->user_id, $start_at, $end_at, "sellReturn" );
//
//getServices( $sales, $services );
//
//$salesSummary = getSummary( $sales );
//$returnsSummary = getSummary( $returns );
//
//$sales_summary = $salesSummary - $returnsSummary;
//$sales_count = count( $sales ) - count( $returns );