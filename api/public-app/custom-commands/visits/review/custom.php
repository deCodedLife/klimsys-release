<?php

$visitsList = $API->DB->from( "visits" )
    ->where([
        "end_at >= ?" => date("Y-m-d H:i:00", strtotime( "-3 hour" ) ),
        "end_at <= ?" => date("Y-m-d H:i:00", strtotime( "-2 hour" ) ),
        "status" => "ended",
        "is_payed" => "Y",
        "is_active" => "Y",
        "is_notified" => "N",
        "send_review" => "Y"
    ])
    ->fetchAll( "client_id[]" );

foreach ( $visitsList as $client_id => $visits ) {

    $store_id = null;

    foreach ( $visits as $key => $visit ) {

        $anotherVisits = $API->DB->from( "visits" )
            ->where([
                "end_at >= ?" => date("Y-m-d 00:00:00", strtotime( $visit[ "start_at" ] )),
                "end_at <= ?" => $visit[ "end_at" ],
                "status" => "ended",
                "is_payed" => "Y",
                "is_active" => "Y",
                "client_id" => $visit[ "client_id" ],
                "not id" => $visit[ "id" ],
                "send_review" => "Y"
            ])
            ->fetch();

        $employeeDetail = $API->DB->from( "users" )
            ->where( "id", $visit[ "user_id" ] )
            ->fetch();

        if ( $anotherVisits !== false ) unset( $visits[ $key ] );
        if ( $visit[ "store_id" ] ) $store_id = $visit[ "store_id" ];
        if ( $employeeDetail[ "notify_clients" ] == "N" )  unset( $visits[ $key ] );

        $API->DB->update( "visits" )
            ->set( "is_notified", "Y" )
            ->where( "id", $visit[ "id" ] )
            ->execute();

    }

    if ( empty( $visits ) ) continue;

    $clientDetail = $API->DB->from( "clients" )
        ->where( "id", $client_id )
        ->fetch();

    $clientFio = $clientDetail[ "first_name" ];
    if ( empty( $clientDetail[ "patronymic" ] ) ) $clientFio .= " " . trim( $clientDetail[ "last_name" ] );
    else $clientFio .= " " . trim( $clientDetail[ "patronymic" ] );

    $userDetails = telegram\getClient( $client_id );
    $storeDetails = $API->DB->from( "stores" )->where( "id", $store_id ?? 0 )->fetch();

    $app_name = $storeDetails[ "name" ];
    $app_map = $storeDetails[ "map" ];

    telegram\sendMessage(
        "$clientFio, благодарим Вас за то, что доверяете свое здоровье $app_name\n\nПожалуйста, оцените нашу работу:\n\n$app_map\n\nДля нас очень важно Ваше мнение.",
        $userDetails,
    );

}