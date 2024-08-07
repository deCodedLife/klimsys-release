<?php

/**
 * Получение детальной информации о Задаче
 */

$userDetail = $API->DB->from( "users" )
    ->where( "id", $API::$userDetail->id )
    ->limit( 1 )
    ->fetch();


/**
 * Отправка события о добавлении Задачи
 */
$API->addEvent( "notifications" );

/**
 * Отправка события об обновлении чата
 */
$API->addEvent( "personMessages" );