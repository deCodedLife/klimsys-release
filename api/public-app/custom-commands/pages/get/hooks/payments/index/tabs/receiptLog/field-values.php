<?php

if ( $tabBlock[ "type" ] == "analytic_widgets" || $tabBlock[ "type" ] == "list" ) $generatedTab[ "settings" ][ "filters" ] = [
    "start_at" => date( 'Y-m-d ' ) . "00:00:00",
    "end_at" => date( 'Y-m-d ' ) . "23:59:59"
];