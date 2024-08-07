<?php

/**
 * @file
 * Хуки на Добавление сотрудников
 */

$formFieldsUpdate = [];
$users = [];

if ( $requestData->category_id ) {

    $serviceGroupEmployees = $API->DB->from( "serviceGroupEmployees" )
        ->where( "groupID", $requestData->category_id );

    foreach ( $serviceGroupEmployees as $serviceGroupEmployee ) {

        $users[] = (int)$serviceGroupEmployee[ "employeeID" ];

    }
    $formFieldsUpdate[ "users_id" ][ "value" ] = array_values(array_unique($users));

    if ( $requestData->user_id ) {

        $users = [];

        foreach ( $requestData->user_id as $serviceGroupEmployee ) {

            $users[] = (int)$serviceGroupEmployee;

        }

        $formFieldsUpdate[ "users_id" ][ "value" ] = array_values(array_unique($users));

    }

}

if ( $requestData->is_equipment == 'Y' ) $formFieldsUpdate[ "equipment_id" ][ "is_visible" ] = true;
else $formFieldsUpdate[ "equipment_id" ][ "is_visible" ] = false;

$API->returnResponse( $formFieldsUpdate );