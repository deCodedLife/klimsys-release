<?php
//
///**
// * @file
// * Отчет "группы услуг
// */
//
//$returnServices = [];
//
///**
// * Получение текущего времени
// */
//$currentDateTime = new DateTime();
//
//
///**
// * Начало текущего месяца
// */
//$currentStart = $currentDateTime->format("Y-m-01 00:00:00");
//
///**
// * Конец текущего месяца
// */
//$currentEnd = $currentDateTime->modify('last day of this month')->format("Y-m-d 23:59:59");
//
///**
// * Начало прошлого месяца
// */
//$pastStart = $currentDateTime->modify("-32 days")->format("Y-m-01 00:00:00");
//
///**
// * Конец прошлого месяца
// */
//$pastEnd = $currentDateTime->modify('last day of this month')->format("Y-m-d 23:59:59");
//
///**
// * Начало позапрошлого месяца
// */
//$beforeLastStart = $currentDateTime->modify("-2 month")->format("Y-m-01 00:00:00");
//
///**
// * Конец позапрошлого месяца
// */
//$beforeLastEnd = $currentDateTime->modify('last day of this month')->format("Y-m-d 23:59:59");
//
///**
// * Получение списка продаж
// */
//
//$salesList = mysqli_query(
//    $API->DB_connection,
//    "SELECT salesList.id,
//                   serviceGroups.id,
//                   serviceGroups.title,
//                   salesList.created_at,
//                   salesProductsList.product_id,
//                   salesProductsList.cost,
//                   salesProductsList.amount
//            FROM salesList
//            LEFT JOIN salesProductsList ON salesProductsList.sale_id = salesList.id
//            LEFT JOIN services ON salesProductsList.product_id = services.id
//            LEFT JOIN serviceGroups ON serviceGroups.id = services.category_id
//            WHERE salesList.status = 'done'
//              AND serviceGroups.is_active = 'Y'
//              AND salesList.action = 'sell'
//              AND salesList.created_at >= '$beforeLastStart'
//              AND salesList.created_at <= '$currentEnd'
//            ORDER BY  salesList.created_at DESC;"
//);
//
//
//$servicesDetail = [];
//
///**
// * Обход продаж
// */
//foreach ( $salesList as $sale ) {
//
//    if ( $requestData->category_id && (int)$requestData->category_id != (int)$sale[ "id" ] ) { break; }
//
//    if ( !$returnServices[ $sale[ "id" ]][ "sum_one" ] ) $returnServices[ $sale[ "id" ]][ "sum_one" ] = "0";
//    if ( !$returnServices[ $sale[ "id" ]][ "sum_two" ] ) $returnServices[ $sale[ "id" ]][ "sum_two" ] = "0";
//    if ( !$returnServices[ $sale[ "id" ]][ "sum_three" ] ) $returnServices[ $sale[ "id" ]][ "sum_three" ] = "0";
//
//
//    /**
//     * Получение детальной информации об услуги
//     */
//
//    $serviceDetail = $servicesDetail[ $sale[ "product_id" ] ];
//
//    if ( !$serviceDetail ) {
//
//        $serviceDetail = $API->DB->from( "services" )
//            ->select( null )->select( "category_id" )
//            ->where( "id", $sale[ "product_id" ] )
//            ->limit( 1 )
//            ->fetch();
//
//        $servicesDetail[ $sale[ "product_id" ] ] = $serviceDetail;
//
//    }
//
//
//    /**
//     * Заполнение списка
//     */
//    if ( $serviceDetail[ "category_id" ] == $sale[ "id" ] ) {
//
//        $returnServices[$sale[ "id" ]][ "title" ] =  $sale[ "title" ];
//
//        if ( $sale[ "created_at" ] >= $currentStart && $sale[ "created_at" ] <= $currentEnd ){
//
//            $returnServices[$sale[ "id" ]][ "sum_one" ] = $returnServices[$sale[ "id" ]][ "sum_one" ] + $sale[ "amount" ] * $sale[ "cost" ];
//
//        }
//
//        if ( $sale[ "created_at" ] >= $pastStart && $sale[ "created_at" ] <= $currentEnd ) {
//
//            $returnServices[$sale[ "id" ]][ "sum_two" ] =  $returnServices[$sale[ "id" ]][ "sum_two" ] + $sale[ "amount" ] * $sale[ "cost" ];
//
//        }
//
//        if ( $sale[ "created_at" ] >= $beforeLastStart && $sale[ "created_at" ] <= $currentEnd ) {
//
//            $returnServices[$sale[ "id" ]][ "sum_three" ] = $returnServices[$sale[ "id" ]][ "sum_three" ] + $sale[ "amount" ] * $sale[ "cost" ];
//
//        }
//
//    }
//
//
//} // foreach. $salesList
//
//
//$response[ "data" ] = array_values($returnServices);
//
//function array_sort ( $array, $on, $order=SORT_ASC )
//{
//    $new_array = array();
//    $sortable_array = array();
//
//    if (count($array) > 0) {
//        foreach ($array as $k => $v) {
//            if (is_array($v)) {
//                foreach ($v as $k2 => $v2) {
//                    if ($k2 == $on) {
//                        $sortable_array[$k] = $v2;
//                    }
//                }
//            } else {
//                $sortable_array[$k] = $v;
//            }
//        }
//
//        switch ($order) {
//            case SORT_ASC:
//                asort($sortable_array);
//                break;
//            case SORT_DESC:
//                arsort($sortable_array);
//                break;
//        }
//
//        foreach ($sortable_array as $k => $v) {
//            $new_array[$k] = $array[$k];
//        }
//    }
//
//    return $new_array;
//}
//
//if ( $sort_by == "title" ) {
//
//    if ( $sort_order == "desc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "title", SORT_DESC ) );
//    if ( $sort_order == "asc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "title", SORT_ASC ) );
//
//}
//
//if ( $sort_by == "sum_one" ) {
//
//    if ( $sort_order == "desc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "sum_one", SORT_DESC ) );
//    if ( $sort_order == "asc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "sum_one", SORT_ASC ) );
//
//}
//
//if ( $sort_by == "sum_two" ) {
//
//    if ( $sort_order == "desc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "sum_two", SORT_DESC ) );
//    if ( $sort_order == "asc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "sum_two", SORT_ASC ) );
//
//}
//
//if ( $sort_by == "sum_three" ) {
//
//    if ( $sort_order == "desc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "sum_three", SORT_DESC ) );
//    if ( $sort_order == "asc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "sum_three", SORT_ASC ) );
//
//}
//
//$response[ "detail" ] = [
//
//    "pages_count" => ceil(count($response[ "data" ]) / $requestData->limit),
//    "rows_count" => count($response[ "data" ])
//
//];
//
//$response[ "data" ] = array_slice($response[ "data" ], $requestData->limit * $requestData->page - $requestData->limit, $requestData->limit);

$currentStart = date('Y-m-1 00:00:00', strtotime($requestData->month));
$currentEnd = date('Y-m-t 23:59:59', strtotime($requestData->month));

$previousStart = date('Y-m-1 00:00:00', strtotime($requestData->month . ' -1 month'));
$previousEnd = date('Y-m-t 23:59:59', strtotime($requestData->month . ' -1 month'));

$currentValue = 0;
$previousValue = 0;
$direction = "negative";

$salesList = mysqli_query(
    $API->DB_connection,
    "SELECT salesList.id,
                   serviceGroups.title,
                   salesList.created_at,
                   salesProductsList.product_id,
                   salesProductsList.cost,
                   salesProductsList.amount
            FROM salesList
            LEFT JOIN salesProductsList ON salesProductsList.sale_id = salesList.id
            LEFT JOIN services ON salesProductsList.product_id = services.id
            LEFT JOIN serviceGroups ON serviceGroups.id = services.category_id
            WHERE salesList.status = 'done'
              AND serviceGroups.is_active = 'Y'
              AND serviceGroups.id = '$requestData->id'
              AND salesList.action = 'sell'
              AND salesList.created_at >= '$previousStart'
              AND salesList.created_at <= '$currentEnd'
            ORDER BY  salesList.created_at DESC;"
);

/**
 * Обход продаж
 */
foreach ( $salesList as $sale ) {

    if ( $sale[ "created_at" ] >= $currentStart && $sale[ "created_at" ] <= $currentEnd ) {

        $currentValue += $sale[ "cost" ] * $sale[ "amount" ];

    }

    if ( $sale[ "created_at" ] >= $previousStart && $sale[ "created_at" ] <= $previousEnd ) {

        $previousValue += $sale[ "cost" ] * $sale[ "amount" ];

    }

} // foreach. $salesList

if ( $previousValue - $currentValue <= 0 ) {

    $direction = "positive";

}

$response[ "data" ][ 0 ] = [

    "title" => $response[ "data" ][ 0 ][ "title" ],
    "current_value" => [
        "direction" => $direction,
        "value" => $currentValue,
        "suffix" => "₽"
    ],
    "previous_value" => [
        "value" => "$previousValue",
        "suffix" => "₽"
    ]

];

