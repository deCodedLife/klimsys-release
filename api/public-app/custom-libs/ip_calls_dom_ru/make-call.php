<?php

/**
 * @file
 * Инициализация звонка
 */



/**
 * Подключение модулей
 */

require_once( PATH_MODULES . "/db.php" );
require_once( PATH_MODULES . "/employees.php" );
require_once( PATH_MODULES . "/ip_calls_dom_ru.php" );



/**
 * Проверка. Авторизован ли пользователь
 */
$userInfo = validateJWT( $JWT, $request->jwt, $jwt[ "key" ] );
if ( !$userInfo ) returnResponse( "Authorization required", 401 );



/**
 * Проверка. Переданны ли все обязательные параметры
 */
if ( !$request->data->client_phone ) returnResponse( "Bad request", 400 );
if ( !$request->data->employee ) returnResponse( "Bad request", 400 );



$IPCallsDomRu->makeCall( $request->data->client_phone, $request->data->employee );

returnResponse( true );