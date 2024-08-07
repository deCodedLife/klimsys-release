<?php

$saleID = $pageDetail[ "row_id" ];

$formFieldValues = $API->DB->from( "salesList" )
    ->where( "id", $saleID )
    ->limit( 1 )
    ->fetch();

$formFieldValues[ "pay_method" ] = [
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


$formFieldValues[ "action" ] = "sell";
$products = $API->DB->from( "salesProductsList" )
    ->where( "sale_id", $saleID );

foreach ( $products as $saleService )
{
    $formFieldValues[ "sale_products" ][ "value" ][] = [
        "id" => "{$saleService[ "type" ]}#{$saleService[ "product_id" ]}",
        "amount" => $saleService[ "amount" ]
    ];

    unset( $saleService[ "id" ] );
    unset( $saleService[ "sale_id" ] );
    unset( $saleService[ "is_system" ] );

    $pageScheme[ "structure" ][ 1 ][ "settings" ][ "data" ][ "products" ][] = $saleService;
}

$clientInfo = $API->DB->from( "clients" )
    ->where( "id", $formFieldValues[ "client_id" ] ?? 0 )
    ->fetch();

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "data" ][ "client" ] = [
    "first_name" => $clientInfo[ "first_name" ],
    "last_name" => $clientInfo[ "first_name" ],
    "phone" => $clientInfo[ "phone" ],
];


$clientsInfo[] = [
    "link" => "clients/card/{$clientInfo[ "id" ]}",
    "title" => "№{$clientInfo[ "id" ]} {$clientInfo[ "last_name" ]} {$clientInfo[ "first_name" ]} {$clientInfo[ "patronymic" ]}"
];
$pageScheme[ "structure" ][ 1 ][ "settings" ][ "data" ][ "clients_info" ] = $clientsInfo;
//$formFieldsUpdate[ "clients_info" ] = [ "is_visible" => true, "value" => $clientsInfo ];

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


$pageScheme[ "structure" ][ 1 ][ "settings" ][ "data" ][ "employee_id" ] = $API::$userDetail->id;
