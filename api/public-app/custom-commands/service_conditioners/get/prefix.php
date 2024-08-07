<?php

if ( !$requestData->sort_by ) {

    $requestSettings[ "filter" ][ "service_type = ?" ] = "conditioners";
    $requestData->sort_by = "title";

}