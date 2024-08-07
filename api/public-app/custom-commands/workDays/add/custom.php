<?php
//ini_set( "display_errors", true );

/**
 * Вызываем метод создания ячеек и их валидации
 */
require_once $API::$configs[ "paths" ][ "public_app" ] . "/custom-libs/workdays/createEvents.php";
require_once $API::$configs[ "paths" ][ "public_app" ] . "/custom-libs/workdays/validate.php";


/**
 * Если мы находимся тут, то никаких накладок не выявлено
 */

/**
 * Вытаскиваем список дней для последующего добавления
 * в связанную таблицу workDaysWeekdays
 */
$workDays = (array) $requestData->work_days;
unset( $requestData->work_days );
unset( $requestData->id );


/**
 * Добавляем правило из запроса, попутно сохраняя ID
 * создаваемого объекта
 */
$ruleID = $API->DB->insertInto( "workDays" )
    ->values( (array) $requestData )
    ->execute();


/**
 * Запись данных в связанную таблицу
 */
foreach ( $workDays as $workDay ) {

    $API->DB->insertInto( "workDaysWeekdays" )
        ->values( [
            "rule_id" => $ruleID,
            "workday" => $workDay
        ] )
        ->execute();

}

foreach ( $newSchedule as $scheduleEvent ) {

    unset( $scheduleEvent[ "id" ] );
    $scheduleEvent[ "rule_id" ] = $ruleID; 

    $API->DB->insertInto( "scheduleEvents" )
        ->values( $scheduleEvent )
        ->execute();

}

/**
 * Отправка события об обновлении расписания
 */
$API->addEvent( "schedule" );
$API->addEvent( "day_planning" );

/**
 * Блокируем создание записей
 */
$API->returnResponse();