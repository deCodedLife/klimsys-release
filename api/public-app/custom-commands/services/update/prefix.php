<?php

if ( $requestData->users_id ) {

    $serviceTitle = $API->DB->from( "services" )->
        where( "id", $requestData->id )->
        limit( 1 )->
        fetch()[ "title" ];

    $API->addNotification(
        "system_alerts",
        "Изменить процент от продаж",
        "Необходимо изменить процент от продаж для услуги $serviceTitle",
        "info",
        $API::$userDetail->id,
        ""
    );

    $API->addEvent( "notifications" );

}