<?php

if ( !$requestData->sort_by ) {

    $requestSettings[ "filter" ][ "service_type = ?" ] = "refrigerators";
    $requestData->sort_by = "title";

}