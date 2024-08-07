<?php

/**
 * Подключение общего скрипта обработки продаж
 */

$pageScheme[ "structure" ][ 1 ][ "settings" ][ 1 ][ "body" ][ 0 ][ "settings" ][ "data" ][ "employee_id" ] = intval( $API::$userDetail->id );
$pageScheme[ "structure" ][ 1 ][ "settings" ][ 0 ][ "body" ][ 0 ][ "settings" ][ "data" ][ "id" ] = $pageDetail[ "row_id" ];
$pageScheme[ "structure" ][ 1 ][ "settings" ][ 2 ][ "body" ][ 0 ][ "settings" ][ "data" ][ "id" ] = $pageDetail[ "row_detail" ][ "client_id" ]->value;
$pageScheme[ "structure" ][ 1 ][ "settings" ][ 1 ][ "body" ][ 0 ][ "settings" ][ "data" ][ "visits_ids" ] = [
    "visits" => [],
    "equipmentVisits" => [ $pageDetail[ "row_id" ] ]
];
$pageScheme[ "structure" ][ 1 ][ "settings" ][ 1 ][ "body" ][ 0 ][ "settings" ][ "data" ][ "object" ] = "visits";
$client_id = $pageDetail[ "row_detail" ][ "client_id" ]->value ?? 0;

/**
 * Предварительная настройка обязательных параметров
 */
$requestData->id = $pageDetail[ "row_id" ];
$requestData->visits_ids = [
    "visits" => [],
    "equipmentVisits" => [ $pageDetail[ "row_id" ] ]
];
$requestData->store_id = $pageDetail[ "row_detail" ][ "store_id" ]->value;
$requestData->client_id = $client_id;


/**
 * Вызов скрипта
 */
$publicAppPath = $API::$configs[ "paths" ][ "public_app" ];
require_once( $publicAppPath . '/custom-libs/sales/business_logic.php' );


/**
 * Заполнение полей стандартными значениями
 */
$formFieldValues = [
    "sum_cash" => $amountOfPhysicalPayments,
    "action" => "sell",
    "store_id" => $pageDetail[ "row_detail" ][ "store_id" ]->value,
    "client_id" => $client_id,
    "online_receipt" => true,
    "summary" => $saleSummary
];


/**
 * Получение информации о продаже
 */
$saleDetails = $API->DB->from( "salesList" )
    ->innerJoin( "salesEquipmentVisits ON salesEquipmentVisits.sale_id = salesList.id" )
    ->where( [
        "salesEquipmentVisits.visit_id" => $pageDetail[ "row_id" ],
        "salesList.action" => "sell"
    ] )
    ->limit(1)
    ->fetch();


/**
 * Заполнение полей из продаж
 */

if ( $pageDetail[ "row_detail" ][ "is_payed" ] == "Y" || ( $saleDetails && $saleDetails[ "status" ] != "error" ) ) {

    /**
     * Заполнение полей запросом в таблицу
     */
    $formFieldValues = $saleDetails;

    /**
     * Приведение данных к правильным типам
     */
    $formFieldValues[ "summary" ] = (float) $formFieldValues[ "summary" ];
    $formFieldValues[ "sum_cash" ] = (float) $formFieldValues[ "sun_cash" ];
    $formFieldValues[ "sum_card" ] = (float) $formFieldValues[ "sum_card" ];
    $formFieldValues[ "sum_bonus" ] = (float) $formFieldValues[ "sum_bonus" ];
    $formFieldValues[ "sum_deposit" ] = (float) $formFieldValues[ "sum_deposit" ];
    $formFieldValues[ "is_combined" ] = $formFieldValues[ "is_combined" ] == "Y";
    $formFieldValues[ "online_receipt" ] = $formFieldValues[ "online_receipt" ] == "Y";

    foreach ( $API->DB->from( "salesEquipmentVisits" )
                  ->where( "sale_id", $saleDetails[ "sale_id" ] ) as $saleVisit )
        $formFieldValues[ "visits_ids" ][ "value" ][] = $saleVisit[ "visit_id" ];

    $saleServices = $API->DB->from( "salesProductsList" )
        ->where( "sale_id", $saleDetails[ "id" ] );

    foreach ( $saleServices as $service )
        $formFieldValues[ "products_display" ][ "value" ][] = $service[ "title" ];

} else {

    $receipt = array_merge( $services, $products );

    foreach ( $receipt as $product )
        $formFieldValues[ "products_display" ][ "value" ][] = $product[ "title" ];

    $pageScheme[ "structure" ][ 1 ][ "settings" ][ 1 ][ "body" ][ 0 ][ "settings" ][ "data" ][ "products" ] = AddToReceipt( $receipt, $discountPerProduct );

}

if ( $visitDetails[ "assist_id" ] ) $formFieldsUpdate[ "assist_id" ][ "is_visible" ] = true;

