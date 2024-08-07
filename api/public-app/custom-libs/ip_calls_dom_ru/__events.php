<?php

/**
 * @file
 * Обработка событий
 * Сюда отправляет запросы телефония
 */



/**
 * Подключение модулей
 */

require_once( PATH_MODULES . "/db.php" );
require_once( PATH_MODULES . "/employees.php" );



/**
 * Проверка. Переданны ли все обязательные параметры
 */
if ( !$_POST[ "cmd" ] ) returnResponse( "Bad request", 400 );



switch ( $_POST[ "cmd" ] ) {

    case "event":

        $DB->makeQuery(
            "DELETE FROM call_history WHERE api_id = 0 AND employee = '" . $_POST[ "user" ] . "'"
        );

        $DB->makeQuery(
            "INSERT INTO call_history ( api_id, status, client_phone, employee, employee_phone ) 
			    VALUES ( 0, '" . $_POST[ "type" ] . "', '" . $_POST[ "phone" ] . "', '" . $_POST[ "user" ] . "', '" . $_POST[ "diversion" ] . "' )"
        );

        break;

} // switch. $_GET[ "cmd" ]

returnResponse( true );
