<?php

/**
 * @file
 * Авторизация Пользователя
 */


/**
 * Поиск Пользователя с указанными данными
 */
$userDetail = $API->DB->from( "users" )
    ->select( null )->select( [ "id", "email", "role_id" ] )
    ->where( [
        "email" => $requestData->email,
        "password" => $requestData->password
    ] )
    ->limit( 1 )
    ->fetch();

/**
 * Проверка введенных данных
 */
if ( !$userDetail ) $API->returnResponse( "Неверные логин или пароль", 403 );


/**
 * Сохранение данных Пользователя
 */
$API->userDetail = $userDetail;

/**
 * Формирование токена для JWT авторизации
 */
$token = [
    "id"      => $userDetail[ "id" ],
    "ip"      => $_SERVER[ "REMOTE_ADDR" ],
    "email"   => $userDetail[ "email" ],
    "role_id" => $userDetail[ "role_id" ]
];

/**
 * JWT авторизация
 */
$jwt_authorization = $API->JWT::encode( $token, $API::$configs[ "jwt_key" ] );


$API->returnResponse( $jwt_authorization );