<?php

/**
 * Получение детальной информации о Задаче
 */

$taskDetail = $API->DB->from( "tasks" )
    ->where( "id", $requestData->id )
    ->limit( 1 )
    ->fetch();


/**
 * Уведомление о добавлении Задачи
 */
$API->addNotification( "system_alerts", "Сообщение в задаче: " . $taskDetail[ "description" ], "info", $taskDetail[ "performer_id" ] );

/**
 * Отправка события о добавлении Задачи
 */
$API->addEvent( "notifications" );

/**
 * Отправка события об обновлении чата
 */
$API->addEvent( "taskChat" );