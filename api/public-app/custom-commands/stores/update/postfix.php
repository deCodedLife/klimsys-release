<?php

if ( $requestData->is_operating == true ) {

    $operating = $API->DB->from( "cabinets" )
        ->where( [

            "store_id" => $requestData->id,
            "is_operating" => "Y"

        ] )
        ->limit( 1 )
        ->fetch();

    if ( $operating ) {

        $API->DB->update( "cabinets" )
            ->set( "is_active", "Y" )
            ->where( [

                "store_id" => $requestData->id,
                "is_operating" => "Y"

            ] )
            ->execute();

    } else {

        $API->DB->insertInto( "cabinets" )
            ->values( [

                "title" => "Операционная",
                "store_id" => $requestData->id,
                "is_active" => "Y",
                "is_operating" => "Y",
                "is_employment" => "Y"

            ] )
            ->execute();

    }

} else {

    $API->DB->update( "cabinets" )
        ->set( "is_active", "N" )
        ->where( [

            "store_id" => $requestData->id,
            "is_operating" => "Y",

        ] )
        ->execute();

}
