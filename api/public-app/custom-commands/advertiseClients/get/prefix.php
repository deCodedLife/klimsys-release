<?php

if ( !$requestData->start_at ) $requestData->start_at = date("Y-m-d", strtotime("-1 months"));
if ( !$requestData->end_at ) $requestData->end_at = date( 'Y-m-d' );

$sort_by = $requestData->sort_by;
$sort_order = $requestData->sort_order;


unset($requestData->sort_by);
unset($requestData->sort_order);