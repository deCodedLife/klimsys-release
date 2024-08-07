<?php

/**
 * Получение детальной информации о посещении
 */

$clientDetail = $API->DB->from( "clients" )
    ->where( "id", $pageDetail[ "row_id" ] )
    ->limit( 1 )
    ->fetch();

if ( $clientDetail[ "is_representative" ] == "Y" ) {

    $formFieldValues[ "present_last_name" ][ "is_visible" ] = true;
    $formFieldValues[ "present_first_name" ][ "is_visible" ] = true;
    $formFieldValues[ "present_patronymic" ][ "is_visible" ] = true;
    $formFieldValues[ "present_passport_series" ][ "is_visible" ] = true;
    $formFieldValues[ "present_passport_number" ][ "is_visible" ] = true;
    $formFieldValues[ "present_passport_issued" ][ "is_visible" ] = true;

}

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "info" ][ "body" ][ 0 ][ "settings" ][ "data" ][ "id" ] = intval( $pageDetail[ "row_id" ] );