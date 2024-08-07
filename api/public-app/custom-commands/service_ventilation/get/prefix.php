<?php

if ( !$requestData->sort_by ) {

    $requestSettings[ "filter" ][ "service_type = ?" ] = "ventilation";
    $requestData->sort_by = "title";

}