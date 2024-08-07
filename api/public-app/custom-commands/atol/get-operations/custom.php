<?php

//$requestData->cashbox_id;
//
//$store_id = $API->DB->from( "stores" )
//    ->innerJoin( "atolCashboxes on atolCashboxes.store_id = stores.id" )
//    ->where( "cashbox_id", $requestData->cashbox_id )
//    ->fetch();

$report = $API->DB->from( "atolOperations" )
    ->where( "cashbox_id", $requestData->cashbox_id )
    ->limit(1)
    ->fetch();

if ( !$report ) $API->returnResponse();

$API->DB->deleteFrom( "atolOperations" )
    ->where( "id", $report[ "id" ] )
    ->execute();

$API->returnResponse( [
    "request" => [
        "uuid" => "doca-" . $report[ "type" ] . "-id-" . $report[ "id" ],
        "type" => $report[ "type" ],
        "operator" => [
            "name" => "Миннахматовна Э. Ц.",
            "vatin" => "123654789507"
        ]
    ]
] );

//$reportX = $API->DB->from( "atolOperations" )
//    ->where( "cashbox_id", $requestData->cashbox_id )
//    ->limit(1)
//    ->fetch();
//
//if ( !$reportX ) $API->returnResponse();
//
//$request = [
//    "request" => [
//        "uuid" => "doca-" . $reportX[ "type" ] . "-id-" . $reportX[ "id" ],
//        "type" => $reportX[ "type" ],
//        "operator" => [
//            "name" => "Миннахматовна Э. Ц.",
//            "vatin" => "123654789507"
//        ]
//    ]
//];
//
//$API->DB->deleteFrom( "atolOperations" )
//    ->where( "id", $reportX[ "id" ] )
//    ->execute();
//
//$API->returnResponse( $request );