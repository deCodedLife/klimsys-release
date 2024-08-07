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
 * Проверка. Переданы ли все обязательные параметры
 */
if ( !$request->data->employee ) returnResponse( "Bad request", 400 );


$query = "SELECT * FROM call_history WHERE employee = '" . $request->data->employee . "' AND ( status = 'INCOMING' OR status = 'ACCEPTED' ) LIMIT 1";

$IPCall = mysqli_fetch_array( $DB->makeQuery( $query ) )[ "client_phone" ];
if ( !$IPCall ) returnResponse( null, 200 );


/**
 * Привязка клиента к сотруднику
 */

$client = mysqli_fetch_array( $DB->makeQuery( "SELECT * FROM clients WHERE phone = '$IPCall' LIMIT 1" ) );
if ( !$client[ "id" ] ) returnResponse( $IPCall, 200 );

$employee = mysqli_fetch_array( $DB->makeQuery( "SELECT * FROM employees WHERE ip_phone_login = '" . $request->data->employee . "' LIMIT 1" ) );
if ( !$employee[ "id" ] ) returnResponse( $IPCall, 200 );

if ( $client[ "employee_id" ] ) {


    $employee_to = (string) date( "Y-m-d", strtotime( date( "Y-m-d" ) . "+1 days" ) );
    if ( $client[ "employee_to" ] < date( "Y-m-d" ) ) $DB->makeQuery( "UPDATE clients SET employee_id = '" . $employee[ "id" ] . "', employee_to = '$employee_to' WHERE id = " . $client[ "id" ] );

} else {

    $employee_to = (string) date( "Y-m-d", strtotime( date( "Y-m-d" ) . "+1 days" ) );
    $DB->makeQuery( "UPDATE clients SET employee_id = '" . $employee[ "id" ] . "', employee_to = '$employee_to' WHERE id = " . $client[ "id" ] );

} // if. $client[ "employee_id" ]


$History->addLog(
    "clients", "Звонок сотруднику (" . $employee[ "last_name" ] . " " . $employee[ "first_name" ] . ")",
    "info", $employee[ "id" ], $client[ "id" ], $client[ "id" ], null
);

returnResponse( $IPCall, 200 );