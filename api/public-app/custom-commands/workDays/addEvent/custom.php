<?php

/**
 * Вызываем метод создания ячеек и их валидации
 */

require_once $API::$configs[ "paths" ][ "public_app" ] . "/custom-libs/workdays/createEvents.php";
require_once $API::$configs[ "paths" ][ "public_app" ] . "/custom-libs/workdays/validate.php";


unset( $requestData->work_days );
unset( $requestData->id );

/**
 * Создание записи в расписании
 */
if ( !$requestData->is_weekend ) $requestData->is_weekend = 'N';

$rowID = $API->DB->insertInto( "workDays" )
    ->values( (array) $requestData )
    ->execute();

$requestData->rule_id = $rowID;
$API->DB->insertInto( "scheduleEvents" )
    ->values( (array) $requestData )
    ->execute();

/**
 * Отправка события об обновлении расписания
 */
$API->addEvent( "schedule" );
$API->addEvent( "day_planning" );
