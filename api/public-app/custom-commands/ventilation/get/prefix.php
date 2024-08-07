<?php

if ( !$requestData->sort_by ) {

    $requestSettings[ "filter" ][ "product_type = ?" ] = "ventilation";

}