<?php

/**
 * Уведомление о добавлении Задачи
 */
$API->addNotification(
    "system_alerts",
    "Создана задача",
    $requestData->description,
    "info",
    $requestData->performer_id,
    "/tasks/update/$insertId"
);

/**
 * Отправка события о добавлении Задачи
 */
$API->addEvent( "notifications" );