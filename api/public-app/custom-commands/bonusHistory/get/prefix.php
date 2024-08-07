<?php

if ( !$requestData->sort_by ) {

    $requestData->sort_by = "created_at";
    $requestData->sort_order = "desc";

}

if ( $requestData->sort_by != "created_at") {

    $sort_by = $requestData->sort_by;
    $sort_order = $requestData->sort_order;

    unset($requestData->sort_by);
    unset($requestData->sort_order);

}