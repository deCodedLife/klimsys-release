<?php

$filterFieldsUpdate = [];

if ( $requestData->user_id ) {

    $filterFieldsUpdate[ "end_at" ][ "is_visible" ] = true;
    $filterFieldsUpdate[ "end_at" ][ "value" ] = date(
        "Y-m-d", strtotime("+30 days", strtotime($requestData->start_at))
    );

} else {

    $filterFieldsUpdate[ "end_at" ][ "is_visible" ] = false;

}

$API->returnResponse( $filterFieldsUpdate );