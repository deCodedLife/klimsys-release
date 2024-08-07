<?php

$formFieldValues = [];

$formFieldValues[ "start_at" ][ "is_visible" ] = false;
$formFieldValues[ "minutes" ][ "is_visible" ] = false;
$formFieldValues[ "hours" ][ "is_visible" ] = false;
$formFieldValues[ "days" ][ "is_visible" ] = false;
$formFieldValues[ "month" ][ "is_visible" ] = false;

switch ( $pageDetail[ "row_detail" ][ "run_configuration" ]->value ) {

    case "once":
        $formFieldValues[ "start_at" ][ "is_visible" ] = true;
        break;

    case "period":
        $formFieldValues[ "minutes" ][ "is_visible" ] = true;
        $formFieldValues[ "hours" ][ "is_visible" ] = true;
        $formFieldValues[ "days" ][ "is_visible" ] = true;
        $formFieldValues[ "month" ][ "is_visible" ] = true;

}