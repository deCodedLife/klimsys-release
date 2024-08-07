<?php

/**
 * @file
 * Вывод истории
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


if ( strpos( $request->data->login, "@" ) )
    $employee = substr( $request->data->login, 0, strpos( $request->data->login, "@" ) );


/**
 * Сформированная история
 */
$returnIPCallsHistory = [];
if ( !$request->data->phone && !$request->data->login ) returnResponse( [] );


if ( $request->data->login ) $request->data->phone = substr(
    $request->data->login, 0, strpos( $request->data->login, "@" )
);


/**
 * Получение истории
 */

$query = "SELECT * FROM call_history";
if ( $request->data->filter_column ) $query .= " WHERE api_id > 0 AND " . $request->data->filter_column . " = '" . $request->data->phone . "'";
else $query .= " WHERE api_id > 0 AND employee_phone = '" . $request->data->phone . "' OR client_phone = '" . $request->data->phone . "'";

$query .= " GROUP BY api_id ORDER BY id DESC";
$IPCallsHistory = $DB->makeQuery( $query );

if ( !$IPCallsHistory ) returnResponse( "Something was wrong", 500 );


/**
 * Формирование истории
 */

foreach( $IPCallsHistory as $IPCallsHistoryRow ) {

    switch ( $IPCallsHistoryRow[ "status" ] ) {

        case "in":
            $IPCallsHistoryRow[ "status" ] = "Входящий";
            break;

        case "out":
            $IPCallsHistoryRow[ "status" ] = "Исходящий";
            break;

        case "noanswer":
            $IPCallsHistoryRow[ "status" ] = "Без ответа";
            break;

        case "CANCELLED":
            $IPCallsHistoryRow[ "status" ] = "Отменен";
            break;

    } // switch. $IPCallsHistoryRow[ "status" ]

    switch ( $IPCallsHistoryRow[ "employee" ] ) {

        case "hello":
            $IPCallsHistoryRow[ "employee" ] = "Приветствие";
            break;

        case "sales":
            $IPCallsHistoryRow[ "employee" ] = "Отдел продаж";
            break;

    } // switch. $IPCallsHistoryRow[ "status" ]


    /**
     * Получение детальной информации о сотруднике
     */

    $employee = mysqli_fetch_array(
        $DB->makeQuery( "SELECT * FROM employees WHERE ip_phone_login = '" . $IPCallsHistoryRow[ "employee" ] . "' LIMIT 1" )
    );

    if ( !$employee ) $employee = $IPCallsHistoryRow[ "employee" ];
    else $employee = $employee[ "last_name" ] . " " . $employee[ "first_name" ];


    $returnIPCallsHistory[] = [

        /**
         * Id звонка
         */
        "id" => $IPCallsHistoryRow[ "api_id" ],

        /**
         * Статус
         */
        "status" => $IPCallsHistoryRow[ "status" ],

        /**
         * Номер клиента
         */
        "client_phone" => $IPCallsHistoryRow[ "client_phone" ],

        /**
         * Сотрудник, который обработал звонок
         */
        "employee" => $employee,
        "employee_login_ip_calls" => $IPCallsHistoryRow[ "employee" ],

        /**
         * Номер сотрудника
         */
        "employee_phone" => $IPCallsHistoryRow[ "employee_phone" ],

        /**
         * Дата и время начала звонка
         */
        "datetime" => $IPCallsHistoryRow[ "datetime" ],

        /**
         * Время ожидания (сек)
         */
        "wait_duration" => $IPCallsHistoryRow[ "wait_duration" ],

        /**
         * Длительность разговора (сек)
         */
        "call_duration" => $IPCallsHistoryRow[ "call_duration" ],

        /**
         * Ссылка на запись разговора
         */
        "href" => $IPCallsHistoryRow[ "href" ]
    ];

} // foreach. $IPCallsHistory as $IPCallsHistoryRow


returnResponse( $returnIPCallsHistory, 200 );