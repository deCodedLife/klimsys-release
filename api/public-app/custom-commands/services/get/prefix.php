<?php

//if ( $API->request->data->context->block == "select"  ) {
//
//    $requestData->sort_by = "num";
//    $requestData->sort_order = "asc";
//
//}

if ( !$requestData->sort_by ) {

    $requestData->sort_by = "title";
    $requestData->sort_order = "asc";
}

$requestSettings[ "filter" ][ "is_active" ] = "Y";