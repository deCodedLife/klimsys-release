<?php

$requestData->is_payed = "Y";
$requestData->is_active = "Y";


if ( $requestData->start_price ) $requestSettings[ "filter" ][ "price >= ?" ] = $requestData->start_price;
if ( $requestData->end_price ) $requestSettings[ "filter" ][ "price <= ?" ] = $requestData->end_price;
if ( $requestData->start_at ) $requestSettings[ "filter" ][ "start_at >= ?" ] = $requestData->start_at . " 00:00:00";
if ( $requestData->end_at ) $requestSettings[ "filter" ][ "start_at <= ?" ] = $requestData->end_at . " 23:59:59";

if ( $requestData->sort_by == "period" ) $requestData->sort_by = "start_at";
if ( $requestData->sort_by == "fio" ) $requestData->sort_by = "last_name";

unset($requestData->start_price);
unset($requestData->end_price);
unset($requestData->start_at);
unset($requestData->end_at);

