<?php

if ( !$requestData->sort_by ) {

    $requestData->sort_by = "created_at";
    $requestData->sort_order = "desc";

}