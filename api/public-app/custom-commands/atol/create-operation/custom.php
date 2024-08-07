<?php

/**
 * Печать нулевого чека
 */

$operationTypes = [
    "openShift",
    "closeShift",
    "reportX"
];

$storeID = $API->DB->from( "users_stores" )
    ->where( "user_id", $API::$userDetail->id )
    ->fetch() ?? "62";

$cashbox = $API->DB->from( "atolCashboxes" )
    ->where( "store_id", $storeID )
    ->fetch() ?? 0;

$API->DB->insertInto( "atolOperations" )
    ->values( [
        "cashbox_id" => $cashbox[ "cashbox_id" ],
        "type" => $operationTypes[ (int) $requestData->operation_type ]
    ] )
    ->execute();