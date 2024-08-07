<?php

/**
 * Подстановка ФИО
 */

$returnRows = [];

foreach ( $response[ "data" ] as $row ) {

    if ( ( $row[ "is_active" ] ?? 'Y' ) == 'N' ) continue;

    /**
     * Получение детальной информации о клиенте
     */
    $clientDetail = $API->DB->from( "users" )
        ->where( "id", $row[ "id" ] ?? $row[ "value" ] )
        ->limit( 1 )
        ->fetch();

    $user = "{$clientDetail[ "last_name" ]} {$clientDetail[ "first_name" ]} {$clientDetail[ "patronymic" ]}";
    $row[ "fio" ] = $user;

    if ( $API::$userDetail->id == 3 ) {

        $row = [
            "id" => $row[ "id" ],
            "fio" => $row[ "fio" ]
        ];


    };

    $returnRows[] = $row;

} // foreach. $response[ "data" ]

$response[ "data" ] = $returnRows;