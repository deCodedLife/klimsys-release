<?php

$workDay = $API->DB->from( "workDays" )
    ->where( "id", $requestData->id )
    ->limit( 1 )
    ->fetch();
$logDescription = "Внесены изменения в графике:";
foreach ( $requestData as $key => $item ) {

    switch ( $key ) {

        case "store_id":
            $storeTitle = $API->DB->from( "stores" )
                ->where( "id", $item )
                ->limit( 1 )
                ->fetch()[ "title" ];

            $logDescription .= " Филиал изменен на \"$storeTitle\"; ";

            break;

        case "cabinet_id":
            $cabinetTitle = $API->DB->from( "cabinets" )
                ->where( "id", $item )
                ->limit( 1 )
                ->fetch()[ "title" ];
            $logDescription .= " Кабинет изменен на \"$cabinetTitle\"; ";

            break;

    }

}

$API->addLog( [
    "table_name" => "users",
    "description" => $logDescription,
    "row_id" => $workDay[ "user_id" ]
], $requestData );

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
$workDays = (array)$requestData->work_days;
unset( $requestData->work_days );
unset( $requestData->id );


/**
 * Изменяем правило из запроса, попутно сохраняя ID
 * создаваемого объекта
 */
$ruleID = $API->DB->update( "workDays" )
    ->set( (array) $requestData )
    ->where( "id", $ruleDetails[ "id" ] )
    ->execute();


/**
 * Очистка и запись данных в связанную таблицу
 */
$API->DB->deleteFrom( "workDaysWeekdays" )
    ->where( "rule_id", $ruleDetails[ "id" ] )
    ->execute();


foreach ( $workDays as $workDay ) {

    $API->DB->insertInto( "workDaysWeekdays" )
        ->values([
            "rule_id" => $ruleDetails[ "id" ],
            "workday" => $workDay
        ])
        ->execute( );

}

$API->DB->deleteFrom( "scheduleEvents" )
    ->where( "rule_id", $ruleDetails[ "id" ] )
    ->execute();


foreach ( $newSchedule as $scheduleEvent ) {

    unset( $scheduleEvent[ "id" ] );
    $scheduleEvent[ "rule_id" ] = $ruleDetails[ "id" ];

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