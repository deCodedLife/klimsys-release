<?php

if ( !$requestData->sort_by || $requestData->sort_by == "fio" ) {

    $requestData->sort_by = "last_name";

}