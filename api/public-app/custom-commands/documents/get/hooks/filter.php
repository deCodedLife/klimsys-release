<?php

/**
 * Фильтр документов при печати
 */
$requestData->limit = 999;

if ( $requestData->context->block == "print" ) {

    $requestData->is_system = 'Y';
    $requestData->is_general = 'Y';

    if ( $API::$userDetail->role_id == 7 ) {

        $requestData->is_system = 'N';
        $requestData->is_general = 'N';
        $requestData->owners_id[] = $API::$userDetail->id;

    }

}
