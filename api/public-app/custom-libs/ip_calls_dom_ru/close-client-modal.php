<?php

/**
 * @file
 * Получение входящих звонков
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
if ( !$request->data->employee || !$request->data->client_phone ) returnResponse( "Bad request", 400 );



$DB->makeQuery( "DELETE FROM call_history WHERE api_id = 0 AND employee = '" . $request->data->employee . "' AND client_phone = '" . $request->data->client_phone . "'" );



returnResponse( true );