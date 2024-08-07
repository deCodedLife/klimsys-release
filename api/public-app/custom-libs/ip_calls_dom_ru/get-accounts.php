<?php

/**
 * @file
 * Вывод аккаунтов
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
 * Получение аккаунтов
 */

$accounts = $IPCallsDomRu->getAccounts();

if ( $accounts === false ) return returnResponse( "Something was wrong", 200 );

$accounts = json_decode( $accounts );



return returnResponse( $accounts, 200 );