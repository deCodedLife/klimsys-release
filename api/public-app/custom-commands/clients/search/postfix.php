<?php

global $API, $response;

/**
 * Подстановка ФИО
 */

foreach ( $response[ "data" ] as $key => $row ) {

    /**
     * Получение детальной информации о клиенте
     */
    if ( $row[ "last_name" ] && $row[ "first_name" ] && $row[ "patronymic" ] ) $clientDetail = $row;
    else $clientDetail = $API->DB->from( "clients" )
        ->where( "id", $row[ "id" ] ?? $row[ "value" ] )
        ->limit( 1 )
        ->fetch();


    $row[ "fio" ] = $clientDetail[ "last_name" ];
    if ( $clientDetail[ "first_name" ] ) $row[ "fio" ] .= " {$clientDetail[ "first_name" ]}";
    if ( $clientDetail[ "patronymic" ] ) $row[ "fio" ] .= " {$clientDetail[ "patronymic" ]}";


    if ( $API->isPublicAccount() ) {

        $row = [
            "id" => $row[ "id" ],
            "fio" => $row[ "fio" ],
            "phone" => $row[ "phone" ]
        ];

    }

    $response[ "data" ][ $key ] = $row;

} // foreach. $response[ "data" ]