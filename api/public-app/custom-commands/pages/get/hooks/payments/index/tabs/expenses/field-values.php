<?php

if ( $tabBlock[ "type" ] == "analytic_widgets" ) $generatedTab[ "settings" ][ "filters" ] = [
    "start_at" => date( 'Y-m-d ' ) . "00:00:00",
    "end_at" => date( 'Y-m-d ' ) . "23:59:59"
];

if ( $tabBlock[ "type" ] == "list" ) $generatedTab[ "settings" ][ "filters" ] = [
    "start_at" => date( 'Y-m-d ' ) . "00:00:00",
    "end_at" => date( 'Y-m-d ' ) . "23:59:59"
];
