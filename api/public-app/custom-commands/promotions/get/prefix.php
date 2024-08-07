<?php

global $API;

$sort_by = $requestData->sort_by;
$sort_order = $requestData->sort_order;

unset($requestData->sort_by);
unset($requestData->sort_order);


if ( $requestData->begin_at ) {

    $begin_at = $requestData->begin_at . " 00:00:00";

    unset( $requestData->begin_at );

}
if ( $requestData->end_at ) {

    $end_at = $requestData->end_at . " 23:59:59" ;

    unset( $requestData->end_at);

}
