<?php


/**
 * Изменение статуса оплаты
 */

$saleDetails = $API->DB->from( "salesList" )
    ->where( "id", $requestData->sale_id )
    ->fetch();

if ( !$saleDetails ) $API->returnResponse();
if ( $saleDetails[ "status" ] == "done" ) $API->returnResponse();

$saleVisit = $API->DB->from( "saleVisits" )
    ->where( "sale_id", $requestData->sale_id )
    ->fetch();

if ( $saleVisit[ "is_payed" ] == 'Y' ) $API->returnResponse();

$API->DB->update( "salesList" )
    ->set( [
        "status" => $requestData->status ?? "error",
        "error" => $requestData->description ?? ""
    ] )
    ->where( "id", $requestData->sale_id )
    ->execute();

$API->DB->update( "visits" )
    ->set( "is_payed", 'N' )
    ->where( "id", $saleVisit[ "visit_id" ] )
    ->execute();