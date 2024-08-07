<?php

$requestData->client_id = intval( $requestData->clients_id[ 0 ] ?? 1 );

$API->DB->update( "visits" )
    ->set( "client_id", $requestData->client_id )
    ->where( "id", $insertId )
    ->execute();


if ( $requestData->phone || $requestData->last_name || $requestData->first_name || $requestData->patronymic ) {

    $clientDetail = $API->DB->from( "clients" )
        ->where( "phone", $requestData->phone )
        ->limit( 1 )
        ->fetch();

    $requestData->clients_id = [$clientDetail[ "id" ]];

}


/**
 * Отправка события об обновлении расписания
 */
$API->addEvent( "schedule" );
$API->addEvent( "day_planning" );

/**
 * Отправка уведомления
 */
$API->addNotification(
    "system_alerts",
    "Создана запись ",
    "на " . date( "H:i:s d.m.Y", strtotime( $requestData->start_at ) ),
    "info",
    $requestData->user_id
);

if ( !empty( $requestData->clients_id ) ) {

    $clientDetail = $API->DB->from( "clients" )
        ->where( "id", $requestData->clients_id[ 0 ] )
        ->fetch();

    $employeeDetail = $API->DB->from( "users" )
        ->where( "id", $requestData->user_id )
        ->fetch();

    $storeDetail = $API->DB->from( "stores" )
        ->where( "id", $requestData->store_id )
        ->fetch();

    if ( $employeeDetail[ "notify_clients" ] == "Y" ) {

        $date = date( "d.m.Y в H:i", strtotime( $requestData->start_at ) );

        $clientFio = $clientDetail[ "first_name" ];
        if ( empty( $clientDetail[ "patronymic" ] ) ) $clientFio .= " " . trim( $clientDetail[ "last_name" ] );
        else $clientFio .= " " . trim( $clientDetail[ "patronymic" ] );

        $employeeFio = $employeeDetail[ "last_name" ];
        if ( !empty( $employeeDetail[ "first_name" ] ) ) $employeeFio .= " {$employeeDetail[ "first_name" ]}";
        if ( !empty( $employeeDetail[ "patronymic" ] ) ) $employeeFio .= " {$employeeDetail[ "patronymic" ]}";

        $app_name = $storeDetail[ "name" ];
        $app_address = $storeDetail[ "address" ];
        $app_map = $storeDetail[ "map" ];
        $app_phone = $storeDetail[ "phone" ];

        $message = "Здравствуйте!\n\n$clientFio, Вы записаны на приём в $app_name на $date\n\nВрач: $employeeFio.\n\nПознакомиться с доктором предстоящего визита вы можете по ссылке: {$employeeDetail[ "site_url" ]}\n\nТел: $app_phone Адрес: $app_address\n\n$app_map\n\nДо встречи в $app_name!";

        if ( $requestData->status == "remote" )
            $message = "Здравствуйте!\n\n$clientFio, Вы записаны на онлайн прием в $app_name на $date\n\nВрач: $employeeFio.\n\nПознакомиться с доктором предстоящего визита вы можете по ссылке: {$employeeDetail[ "site_url" ]}\n\nВ ближайшее время администратор отправит Вам ссылку на оплату и расскажет как подключиться.\n\nТел: $app_phone\n\nТакже Вы можете написать в этот чат по любым вопросам 😌";

        $send_message = true;
        if ( $requestData->notify === false || $requestData->notify === "N" ) $send_message = false;
        if ( $API->isPublicAccount() ) $send_message = true;

        if ( $send_message )
            telegram\sendMessage(
                $message,
                telegram\getClient( $requestData->clients_id[ 0 ] )
            );

    }

}