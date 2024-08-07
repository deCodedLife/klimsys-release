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
//ini_set("display_errors", true);
$currentStart = date('Y-m-01 00:00:00', strtotime($requestData->month));
$currentEnd = date('Y-m-t 23:59:59', strtotime($requestData->month));

$previousStart = date('Y-m-01 00:00:00', strtotime($requestData->month . ' -1 month'));
$previousEnd = date('Y-m-t 23:59:59', strtotime($requestData->month . ' -1 month'));

$currentClients = 0;
$previousClients = 0;

$currentVisitsSum = 0;
$previousVisitsSum = 0;

$currentWorkDays = 0;
$previousWorkDays = 0;

$directionWorkDays = "negative";
$directionVisitsSum = "negative";
$directionClients = "negative";

$userDetail = $API->DB->from( "users" )
    ->where( "id", $requestData->id )
    ->limit( 1 )
    ->fetch();

/**
 * Получение посещений Сотрудника за 3 месяца
 */
$visits = $API->DB->from( "visits" )
    ->where( [
        "end_at <= ?" => $currentEnd,
        "start_at >= ?" => $previousStart,
        "is_active" => "Y",
        "user_id" => $requestData->id
    ]);

/**
 * Обход рабочих Сотрудника за 3 месяца
 */
$workDays = $API->sendRequest( "workDays", "calendar", [
        "user_id" => $requestData->id,
        "event_to" => $currentEnd,
        "event_from" => $previousStart
    ] );

foreach ( $workDays as $date => $workDay ) {

    if ( $workDay[0]->is_weekend == "N" ) {

        if ( $date . " 00:00:00" >= $currentStart && $date . " 00:00:00" <= $currentEnd ) {

            $currentWorkDays++;

        } else if ( $date . " 00:00:00" >= $previousStart && $date . " 00:00:00" <= $previousEnd ) {

            $previousWorkDays++;

        }

    }

}


foreach ( $visits as $visit ) {

    if ( $visit[ "start_at" ] >= $currentStart && $visit[ "start_at" ] <= $currentEnd ) {

        $currentClients++;
        $currentVisitsSum += $visit[ "price" ];

    } else if ( $visit[ "start_at" ] >= $previousStart && $visit[ "start_at" ] <= $previousEnd ) {

        $previousClients++;
        $previousVisitsSum += $visit[ "price" ];

    }

}


if ( $previousWorkDays - $currentWorkDays <= 0 ) {

    $directionWorkDays = "positive";

}

if ( $previousVisitsSum - $currentVisitsSum <= 0 ) {

    $directionVisitsSum = "positive";

}

if ( $previousClients - $currentClients <= 0 ) {

    $directionClients = "positive";

}

$first_name = $userDetail[ "first_name" ];
$patronymic = $userDetail[ "patronymic" ];

$response[ "data" ] = [
    0 => [
        "user" => $userDetail[ "last_name" ] . " " . substr($first_name, 0, 2) . ". " . substr($patronymic, 0, 2) . ".",
        "title" => "",
        "current_value" => "",
        "previous_value" => ""
    ],
    1 => [
        "user" => "",
        "title" => "Рабочих дней",
        "previous_value" => [ "value" => $previousWorkDays ],
        "current_value" => [
            "direction" => $directionWorkDays,
            "value" => $currentWorkDays,
            "suffix" => ""
        ],
    ],
    2 => [
        "user" => "",
        "title" => "Пациентов",
        "previous_value" => [ "value" => $previousClients ],
        "current_value" => [
            "direction" => $directionClients,
            "value" => $currentClients,
            "suffix" => ""
        ],
    ],
    3 => [
        "user" => "",
        "title" => "Сумма",
        "previous_value" => [
            "value" => $previousVisitsSum,
            "suffix" => "₽"
        ],
        "current_value" => [
            "direction" => $directionVisitsSum,
            "value" => $currentVisitsSum,
            "suffix" => "₽"
        ],

    ]
];

