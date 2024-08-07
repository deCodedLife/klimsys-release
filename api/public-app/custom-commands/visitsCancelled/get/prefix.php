<?php

$requestSettings[ "filter" ][ "is_active" ] = "N";
$requestSettings[ "filter" ][ "cancelledDate <= ?" ] = $requestData->cancelledDate_end . " 23:59:59";
$requestSettings[ "filter" ][ "cancelledDate >= ?" ] = $requestData->cancelledDate_start . " 00:00:00";

if ( !$requestData->sort_by ) {

    $requestData->sort_by = "cancelledDate";
    $requestData->sort_order = "desc";

}

