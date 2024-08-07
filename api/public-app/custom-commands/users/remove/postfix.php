<?php
$API->DB->update( "users" )
    ->set( [
        "is_visibleOnSite" => "N",
        "is_visible_in_schedule" => "N",
        "notify_clients" => "N",
        "is_queue" => "N"
    ] )
    ->where( "id", $requestData->id[0] )
    ->execute();