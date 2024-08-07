<?php

require_once $API::$configs[ "paths" ][ "public_app" ] . "/custom-libs/atol/index.php";
//ini_set( 'display_errors', 1 );
ini_set( 'serialize_precision', -1 );


$AtolReciept = new Сashbox\Atol;
$AtolReciept->operator = new Сashbox\IOperator;
$AtolReciept->operator->name = "Миннахматовна Э. Ц.";
$AtolReciept->operator->vatin = "123654789507";


$cashboxStore = $API->DB->from( "atolCashboxes" )
    ->where( "cashbox_id", $requestData->cashbox_id )
    ->limit( 1 )
    ->fetch()[ "store_id" ];


$processedSale = $API->DB->from( "salesList" )
    ->where( [
        "store_id" => $cashboxStore,
        "status" => "waiting",
        "created_at > ?" => date('Y-m-d') . " 00:00:00"
    ] )
    ->orderBy( "id DESC" )
    ->limit( 1 )
    ->fetch();

if ( $requestData->sale_id ) {

    $processedSale = $API->DB->from( "salesList" )
        ->where( "id", $requestData->sale_id )
        ->fetch();

}

if ( !$processedSale ) $API->returnResponse( [] );

//if ( $processedSale[ "online_receipt" ] === "Y" ) {
//
//    $clientDetails = $API->DB->from( "clients" )
//        ->where( "id", $processedSale[ "client_id" ] )
//        ->fetch();
//
//    if ( $clientDetails[ "email" ] ) {
//
//        $AtolReciept->clientInfo = [
//            "name" => $clientDetails[ "last_name" ] . " " . $clientDetails[ "first_name" ] . " " . $clientDetails[ "patronymic" ],
//            "emailOrPhone" => $clientDetails[ "email" ],
//        ];
//
//        $AtolReciept->electronically = true;
//
//    }
//
//}

$paymentSales = [];
$paymentSales[] = $processedSale;

$saleVisits = $API->DB->from( "salesVisits" )
    ->where( "sale_id", $processedSale[ "id" ] );

$servicesPrice = 0;
$difference = 0;
$services = [];

foreach ( $API->DB->from( "salesProductsList" )->where( "sale_id", $processedSale[ "id" ] ) as $service ) {

    $details = $API->DB->from( "services" )
        ->where( "id", $service[ "product_id" ] )
        ->fetch();

    if ( !$details ) {

        $service[ "price" ] = $service[ "cost" ];
        $services[] = $service;
        $difference += $service[ "cost" ];
        continue;

    }

    $details[ "price" ] = $service[ "cost" ];
    $difference += $service[ "cost" ];

    $services[] = $details;

}

if ( !$services ) $API->returnResponse( [] ); // "Продажа {$processedSale[ "id" ]} не имеет услуг"


$summary = $processedSale[ "summary" ] - $processedSale[ "sum_bonus" ];
$discountPerProduct = $summary / ( $difference == 0 ? 1 : $difference );
//$discountPerProduct = 1;

function ceiling($number, $significance = 1)
{
    return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
}

foreach ( $services as $service ) {

    $paymentObject = new Сashbox\IProduct;
    $paymentObject->name = $service[ "title" ];
    $paymentObject->paymentObject = PAYMENT_OBJECTS[ 4 ];
    $paymentObject->quantity = 1;
    $paymentObject->price = round( $service[ "price" ] * $discountPerProduct, 2 );
    $paymentObject->piece = true;
    $paymentObject->tax = [ "type" => "none" ];
    $paymentObject->type = "position";
    $paymentObject->amount = round( $paymentObject->price * $paymentObject->quantity, 2 );

    $AtolReciept->items[] = $paymentObject;

}


if ( $processedSale[ "sum_deposit" ] ) $AtolReciept->payments[] = new Сashbox\IPayment( "2", $processedSale[ "sum_deposit" ] );
if ( $processedSale[ "sum_card" ] ) $AtolReciept->payments[] = new Сashbox\IPayment( "1", $processedSale[ "sum_card" ] );
if ( $processedSale[ "sum_cash" ] ) $AtolReciept->payments[] = new Сashbox\IPayment( "cash", $processedSale[ "sum_cash" ] );

$AtolReciept->summary = $summary;
$AtolReciept->taxationType = "usnIncomeOutcome";
$AtolReciept->uuid = $processedSale[ "id" ];

$action = $processedSale[ "action" ];
if ( $action == "deposit" ) $action = "sell";

$AtolReciept->sales = [ (int) $processedSale[ "id" ] ];
$AtolReciept->sale_type = $action;
$AtolReciept->pay_method = $processedSale[ "pay_method" ];

$API->returnResponse( $AtolReciept->GetReciept() );