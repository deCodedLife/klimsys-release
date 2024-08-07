<?php

$visitsList = $API->DB->from( "visits" )
    ->where([
        "start_at >= ?" => date("Y-m-d 00:00:00", strtotime("+1 day")),
        "end_at <= ?" => date("Y-m-d 23:59:59", strtotime("+1 day")),
        "is_active" => "Y",
        "is_called" => "N",
        "notify" => "Y"
    ])
    ->fetchAll( "client_id[]" );

foreach ( $visitsList as $client_id => $visits ) {

    $employees = [];
    $visit_ids = [];
    $times = [];
    $store_id = null;
    $is_remote = false;

    foreach ( $visits as $key => $visit ) {

        $employeeDetail = $API->DB->from( "users" )
            ->where( "id", $visit[ "user_id" ] )
            ->fetch();

        if ( $visit[ "store_id" ] ) $store_id = $visit[ "store_id" ];
        if ( $employeeDetail[ "notify_clients" ] == "N" )  unset( $visits[ $key ] );
        if ( $visit[ "status" ] == "remote" ) $is_remote = true;

        $employee_fio = trim( $employeeDetail[ "last_name" ] );
        if ( !empty( $employeeDetail[ "first_name" ] ) ) $employee_fio .= " " . trim( $employeeDetail[ "first_name" ] );
        if ( !empty( $employeeDetail[ "patronymic" ] ) ) $employee_fio .= " " . trim( $employeeDetail[ "patronymic" ] );

        $visitTime = date( "H:i", strtotime( $visit[ "start_at" ] ) );

        $times[] = $visitTime;
        $employees[] = $employee_fio;
        $visit_ids[] = $visit[ "id" ];

    }

    if ( empty( $visits ) ) continue;

    $storeDetails = $API->DB->from( "stores" )
        ->where( "id", $store_id ?? 0 )
        ->fetch();

    $clientDetail = $API->DB->from( "clients" )
        ->where( "id", $client_id )
        ->fetch();

    $clientFio = $clientDetail[ "first_name" ];
    if ( empty( $clientDetail[ "patronymic" ] ) ) $clientFio .= " " . trim( $clientDetail[ "last_name" ] );
    else $clientFio .= " " . trim( $clientDetail[ "patronymic" ] );

    $userDetails = telegram\getClient( $client_id );
    $times = join( ", ", $times );
    $employees = join( ", ", $employees );
    $tomorrow = date( "d.m.Y", strtotime( "+1 day" ) );

    $app_name = $storeDetails[ "name" ];
    $app_address = $storeDetails[ "address" ];

    $message = "Здравствуйте!\n\n$clientFio, Вы записаны в $app_name на $tomorrow в $times по адресу $app_address.\n\nВрач: $employees\n\nДля подтверждения записи ответьте '1'\n\nДля переноса или отмены посещения напишите об этом в чате.";

    if ( $is_remote )
        $message = "Здравствуйте!\n\n$clientFio, Вы записаны в $app_name на онлайн приём $tomorrow в $times.\n\nВрач: $employees\n\nДля подтверждения записи ответьте '1'\n\nДля переноса или отмены посещения напишите об этом в чате.";

    telegram\sendMessage(
        $message,
        $userDetails,
        telegram\getDefaultVisitHandlers( $visit_ids, $userDetails[ "phone" ] )
    );

}