<?php

if ( !$requestData->sort_by ) {

    $requestSettings[ "filter" ][ "service_type = ?" ] = "heating_systems";
    $requestData->sort_by = "title";

}