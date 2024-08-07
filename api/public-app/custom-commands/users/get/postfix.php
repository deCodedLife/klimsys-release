<?php

/**
 * Фильтр по Специальностям
 */

if ( $requestData->professions_id ) {

    /**
     * Отфильтрованные Записи
     */
    $filteredRows = [];


    /**
     * Обновление списка Записей
     */
    $response[ "data" ] = $filteredRows;

} // if. $requestData->professions_id

/**
 * Фильтр по Филиалу
 */

if ( $requestData->stores_id ) {

    /**
     * Отфильтрованные Записи
     */
    $filteredRows = [];


    /**
     * Обновление списка Записей
     */
    $response[ "data" ] = $filteredRows;

} // if. $requestData->store_id

/**
 * Подстановка ФИО
 */

$returnRows = [];
//$users_ids = [];

//foreach ( $response[ "data" ] as $row ) $users_ids[] = $row[ "id" ];
//$userInfo = $API->DB->from( "users" )
//    ->where( "id", $users_ids )
//    ->fetchAll( "id[]" );


foreach ( $response[ "data" ] as $row ) {

//    $row = $userInfo[ $row[ "id" ] ];
    $row[ "fio" ] = $row[ "last_name" ] . " " . $row[ "first_name" ] . " " . $row[ "patronymic" ];

    $short_fio = $row[ "last_name" ];
    if ( $row[ "first_name" ] ) $short_fio .= " " . mb_substr( $row[ "first_name" ], 0, 1 ) . ".";
    if ( $row[ "patronymic" ] ) $short_fio .= " " . mb_substr( $row[ "patronymic" ], 0, 1 ) . ".";
    $row[ "short_fio" ] = $short_fio;

    $returnRows[] = $row;

} // foreach. $response[ "data" ]

$response[ "data" ] = $returnRows;


if ( $API->isPublicAccount() ) {

    $siteUsers = [];

    foreach ( $response[ "data" ] as $key => $user ) {

        /**
         * Фильтр по филиалу
         */
        if ( property_exists( $API->request->data, "store_id" ) ) {
            $userStores = array_map( fn( $store ) => $store[ "value" ], $user[ "stores_id" ] ?? [] );
            if ( !in_array( $API->request->data->store_id, $userStores ) ) continue;
        }


        $phoneFormat = "+" . sprintf("%s (%s) %s-%s-%s",
                substr( $user[ "phone" ], 0, 1 ),
                substr( $user[ "phone" ], 1, 3 ),
                substr( $user[ "phone" ], 4, 3 ),
                substr( $user[ "phone" ], 7, 2 ),
                substr( $user[ "phone" ], 9 )
            );

        $siteUsers[] = [

            "id" => $user[ "id" ],
            "fio" => $user[ "fio" ]

        ];

    }

    $response[ "data" ] = $siteUsers;

}


/**
 * Обработка чата
 */
if ( $requestData->context->block === "chat" ) {

    $chatContext = [];

    foreach ( $response[ "data" ] as $user ) {

        $chatContext[] = [
            "id" => $user[ "id" ],
            "title" => "{$user[ "last_name" ]} {$user[ "first_name" ]} {$user[ "patronymic" ]}"
        ];

    }

    $response[ "data" ] = $chatContext;

}