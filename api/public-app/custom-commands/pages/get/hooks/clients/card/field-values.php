<?php

if ( $pageDetail[ "row_detail" ][ "is_representative" ] == "Y" ) {

    $formFieldValues[ "present_last_name" ][ "is_visible" ] = true;
    $formFieldValues[ "present_first_name" ][ "is_visible" ] = true;
    $formFieldValues[ "present_patronymic" ][ "is_visible" ] = true;
    $formFieldValues[ "present_passport_series" ][ "is_visible" ] = true;
    $formFieldValues[ "present_passport_number" ][ "is_visible" ] = true;
    $formFieldValues[ "present_passport_issued" ][ "is_visible" ] = true;

}