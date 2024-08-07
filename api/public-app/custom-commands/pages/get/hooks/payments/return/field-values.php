<?php
$saleID = $pageDetail[ "row_id" ];

$formFieldValues = $API->DB->from( "salesList" )
    ->where( "id", $saleID )
    ->limit( 1 )
    ->fetch();

$formFieldValues[ "pay_method" ] = [
    "is_disabled" => true,
    "value" => $formFieldValues[ "pay_method" ]
];
$formFieldValues[ "summary" ] = (float) $formFieldValues[ "summary" ];
$formFieldValues[ "sum_cash" ] = [
    "is_visible" =>  (float) $formFieldValues[ "sum_cash" ] != 0,
    "value" => (float) $formFieldValues[ "sum_cash" ]
];
$formFieldValues[ "sum_card" ] = [
    "is_visible" =>  (float) $formFieldValues[ "sum_card" ] != 0,
    "value" => (float) $formFieldValues[ "sum_card" ]
];
$formFieldValues[ "sum_bonus" ] = [
    "title" => "Вернуть бонусов",
    "is_visible" => (float) $formFieldValues[ "sum_bonus" ] != 0,
    "value" => (float) $formFieldValues[ "sum_bonus" ]
];
$formFieldValues[ "sum_deposit" ] = [
    "is_visible" => (float) $formFieldValues[ "sum_deposit" ] != 0,
    "title" => "Вернуть на депозит",
    "value" => (float) $formFieldValues[ "sum_deposit" ]
];
$formFieldValues[ "is_combined" ] = $formFieldValues[ "is_combined" ] == "Y";
$formFieldValues[ "online_receipt" ] = $formFieldValues[ "online_receipt" ] == "Y";

$formFieldValues[ "return_bonuses" ] = [
    "is_visible" => (float) $formFieldValues[ "sum_bonus" ][ "value" ] != 0,
    "value" => true
];
$formFieldValues[ "return_deposit" ] = [
    "is_visible" => (float) $formFieldValues[ "sum_deposit" ][ "value" ] != 0,
    "value" => true
];

$formFieldValues[ "action" ] = "sellReturn";
$formFieldValues[ "terminal_code" ] = [
    "is_visible" => $formFieldValues[ "terminal_code" ] != "",
    "value" => $formFieldValues[ "terminal_code" ]
];

$products = $API->DB->from( "salesProductsList" )
    ->where( "sale_id", $saleID );

foreach ( $products as $saleService )
{
    unset( $saleService[ "id" ] );
    unset( $saleService[ "sale_id" ] );
    unset( $saleService[ "is_system" ] );

    $formFieldValues[ "products_display" ][ "value" ][] = $saleService[ "title" ];
    $pageScheme[ "structure" ][ 1 ][ "settings" ][ "data" ][ "products" ][] = $saleService;
}


if ( $formFieldValues[ "pay_method" ] == "card" ) {

    $formFieldValues[ "sum_cash" ][ "is_visible" ] = false;
    $formFieldValues[ "sum_card" ][ "is_visible" ] = true;

} // if. $requestData->pay_method == "card"

if ( $formFieldValues[ "pay_method" ] == "parts" ) {

    $formFieldValues[ "sum_card" ][ "is_visible" ] = true;
    $formFieldValues[ "sum_cash" ][ "is_visible" ] = true;
    $formFieldValues[ "sum_cash" ][ "is_disabled" ] = false;

} // if. $requestData->pay_method == "parts"

if ( $formFieldValues[ "pay_method" ] == "cash" ) {

    $formFieldValues[ "sum_card" ][ "is_visible" ] = false;
    $formFieldValues[ "sum_cash" ][ "is_visible" ] = true;

} // if. $requestData->pay_method == "cash"

if ( $formFieldValues[ "pay_method" ] == "legalEntity" ) {

    $formFieldValues[ "sum_card" ][ "is_visible" ] = false;
    $formFieldValues[ "sum_cash" ][ "is_visible" ] = false;

}

$visitsIds = $API->DB->from( "saleVisits" )
    ->where( "sale_id", $saleID )
    ->fetchAll( "visit_id" );

$equipmentVisitsIds = $API->DB->from( "salesEquipmentVisits" )
    ->where( "sale_id", $saleID )
    ->fetchAll( "visit_id" );

$visitsIds = array_keys($visitsIds);
$equipmentVisitsIds = array_keys($equipmentVisitsIds);

$visits_ids = [
    "visits" => $visitsIds,
    "equipmentVisits" => $equipmentVisitsIds
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "data" ][ "employee_id" ] = $API::$userDetail->id;
$pageScheme[ "structure" ][ 1 ][ "settings" ][ "data" ][ "visits_ids" ] = $visits_ids;
