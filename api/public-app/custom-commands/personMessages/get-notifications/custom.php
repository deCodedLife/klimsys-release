<?php

/**
 * @file
 * Получение уведомлений
 */


$response[ "data" ] = [];


/**
 * Получение невыведенных сообщений
 */
$unreadMessages = $API->DB->from( "personMessages" )
    ->where( [
        "is_notificated" => "N",
        "author_id != ?" => $API::$userDetail->id
    ] );


/**
 * Вывод невыведенных сообщений
 */

foreach ( $unreadMessages as $unreadMessage ) {

    /**
     * Получение детальной информации об авторе
     */

    $authorDetail = $API->DB->from( "users" )
        ->where( "id", $unreadMessage[ "author_id" ] )
        ->limit( 1 )
        ->fetch();

    $unreadMessage[ "author_id" ] = [
        "title" => $authorDetail[ "last_name" ] . " " . $authorDetail[ "first_name" ] . " " . $authorDetail[ "patronymic" ],
        "value" => $unreadMessage[ "author_id" ]
    ];


    /**
     * Получение ID чата
     */

    $chatDetail = $API->DB->from( "personChats" )
        ->where( "id", $unreadMessage[ "chat_id" ] )
        ->limit( 1 )
        ->fetch();

    $chatKey = explode( "_", $chatDetail[ "chat_key" ] );


    $isNotify = false;

    foreach ( $chatKey as $chatUserId ) {

        if ( $chatUserId == $API::$userDetail->id ) {

            $isNotify = true;

        } else {

            /**
             * Получение ID группы чата
             */
            $chatGroupDetail = $API->DB->from( "users" )
                ->where( "id", $chatUserId )
                ->limit( 1 )
                ->fetch();

            /**
             * Получение названия группы чата
             */
            $chatGroupTitleDetail = $API->DB->from( "roles" )
                ->where( "id", $chatGroupDetail[ "role_id" ] )
                ->limit( 1 )
                ->fetch();


            $unreadMessage[ "chat_id" ] = [
                "title" => $chatGroupDetail[ "last_name" ] . " " . $chatGroupDetail[ "first_name" ] . " " . $chatGroupDetail[ "patronymic" ],
                "value" => $chatGroupDetail[ "id" ]
            ];

            $unreadMessage[ "group_id" ] = [
                "title" => $chatGroupTitleDetail[ "title" ],
                "value" => $chatGroupDetail[ "role_id" ]
            ];

        } // if. $chatUserId == $API::$userDetail->id

    } // foreach. $chatKey


    if ( $isNotify ) $response[ "data" ][] = $unreadMessage;


    /**
     * Чтение сообщения
     */
    $API->DB->update( "personMessages" )
        ->set( [
            "is_notificated" => "Y"
        ] )
        ->where( [
            "id" => $unreadMessage[ "id" ]
        ] )
        ->execute();

} // foreach. $unreadMessages