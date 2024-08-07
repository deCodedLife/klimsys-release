<?php

/**
 * @file
 * Загрузка истории звонков из телефонии
 */



/**
 * Подключение базовых функций
 */
require_once( "/var/www/oxapi/data/www/api.mewbas.com/functions/basic.php" );



/**
 * Подключение модулей
 */

require_once( "/var/www/oxapi/data/www/api.mewbas.com/configs.php" );

require_once( "/var/www/oxapi/data/www/api.mewbas.com/modules/db.php" );
require_once( "/var/www/oxapi/data/www/api.mewbas.com/modules/employees.php" );
require_once( "/var/www/oxapi/data/www/api.mewbas.com/modules/ip_calls_dom_ru.php" );



/**
 * Получение истории
 */

$IPCallsHistory = $IPCallsDomRu->getHistory();

if ( $IPCallsHistory === false ) returnResponse( "Something was wrong", 500 );



/**
 * Формирование истории
 */

$IPCallsHistory = str_getcsv( $IPCallsHistory, "\n" );
foreach( $IPCallsHistory as &$row ) $row = str_getcsv( $row, "," );

foreach( $IPCallsHistory as $IPCallsHistoryRow ) {

    if ( !$IPCallsHistoryRow[ 0 ] ) continue;

    $id = $IPCallsHistoryRow[ 0 ];
    $status = $IPCallsHistoryRow[ 1 ];
    $client_phone = $IPCallsHistoryRow[ 2 ];
    $employee = $IPCallsHistoryRow[ 3 ];
    $employee_phone = $IPCallsHistoryRow[ 4 ];
    $datetime = $IPCallsHistoryRow[ 5 ];
    $wait_duration = $IPCallsHistoryRow[ 6 ];
    $call_duration = $IPCallsHistoryRow[ 7 ];
    $href = $IPCallsHistoryRow[ 8 ];

    if ( strpos( $employee, "@" ) )
        $employee = substr( $employee, 0, strpos( $employee, "@" ) );



    if (
        !mysqli_fetch_array(
            $DB->makeQuery( "SELECT * FROM call_history WHERE api_id = '$id' LIMIT 1" )
        )
    ) {
        $DB->makeQuery(
            "INSERT INTO call_history ( api_id, status, client_phone, employee, employee_phone, datetime, wait_duration, call_duration, href )
			VALUES ( '$id', '$status', '$client_phone', '$employee', '$employee_phone', '$datetime', '$wait_duration', '$call_duration', '$href' )"
        );
    }

} // foreach. $IPCallsHistory as $IPCallsHistoryRow


echo "History loaded\n";
exit();