<?php

$visitDetail = $API->DB->from( "visits" )
    ->where( [
        "id" => $requestData->visit_id,
    ] )
    ->limit( 1 )
    ->fetch();

$API->addLog( [
    "table_name" => "visits",
    "description" => "Написано заключение врача (" . date("d.m.Y H:i") . ")",
    "row_id" => $visitDetail[ "id" ]
] );
