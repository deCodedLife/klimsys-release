<?php

if ( $requestData->context->block === "list" ) {

    if ( $requestData->start_at ) $dateFrom = date( 'Y-m-d', strtotime( $requestData->start_at ) ) . " 00:00:00";
    if ( $requestData->end_at   ) $dateTo   = date( 'Y-m-d', strtotime( $requestData->end_at ) )   . " 23:59:59";

    if ( $requestData->start_at ) $requestSettings[ "filter" ][ "created_at > ?" ] = $dateFrom ?? '';
    if ( $requestData->end_at   ) $requestSettings[ "filter" ][ "created_at < ?" ] = $dateTo ?? '';

}
