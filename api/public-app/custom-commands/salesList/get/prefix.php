<?php

if ( $requestData->context->block === "list" ) {

    if ( $requestData->start_at ) $dateFrom = date( 'Y-m-d', strtotime( $requestData->start_at ) ) . " 00:00:00";
    if ( $requestData->end_at   ) $dateTo   = date( 'Y-m-d', strtotime( $requestData->end_at ) )   . " 23:59:59";

    if ( $requestData->start_at ) $requestSettings[ "filter" ][ "created_at > ?" ] = $dateFrom ?? '';
    if ( $requestData->end_at   ) $requestSettings[ "filter" ][ "created_at < ?" ] = $dateTo ?? '';

    if ( $requestData->action )   $requestSettings[ "filter" ][ "action = ?" ]   = $requestData->action;
    if ( $requestData->pay_method ) $requestSettings[ "filter" ][ "pay_method = ?" ] = $requestData->pay_method;

//    $requestSettings[ "filter" ][ "status = ?" ] = "done";

    if ( !$requestData->sort_by ) {

        $requestData->sort_by = "created_at";
        $requestData->sort_order = "desc";

    }

    if ( $requestData->client_id )  $requestSettings[ "filter" ][ "client_id = ?" ]  = $requestData->client_id;

}


