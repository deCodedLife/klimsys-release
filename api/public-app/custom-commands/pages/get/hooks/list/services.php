<?php

$resultBlockFieldList = [];


foreach ( $blockField[ "list" ] as $blockFieldProperty ) {

    /**
     * Получение исполнителей услуги
     */

    $serviceUsers = $API->DB->from( "services_users" )
        ->where( "service_id", $blockFieldProperty[ "value" ] );

    foreach ( $serviceUsers as $serviceUser )
        $blockFieldProperty[ "joined_field_value" ][] = $serviceUser[ "user_id" ];

    $filteredBlockFieldProperties = array_unique( $blockFieldProperty[ "joined_field_value" ] );


    $blockFieldProperty[ "joined_field_value" ] = [];

    foreach ( $filteredBlockFieldProperties as $filteredBlockFieldProperty )
        $blockFieldProperty[ "joined_field_value" ][] = $filteredBlockFieldProperty;

    $resultBlockFieldList[] = $blockFieldProperty;

} // foreach. $blockField[ "list" ]


$blockField[ "list" ] = $resultBlockFieldList;