<?php

if ( $requestData->is_operating == true ) {

    $API->DB->insertInto( "cabinets" )
        ->values( [
            "title" => "Операционная",
            "store_id" => $insertId,
            "is_active" => "Y",
            "is_operating" => "Y",
            "is_employment" => "N"
        ] )
        ->execute();

}
