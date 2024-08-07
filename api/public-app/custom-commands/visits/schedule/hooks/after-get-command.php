<?php
/**
 * Фильтр Записей по Клиентам
 */

if ( $requestData->clients_id ) {

    /**
     * Отфильтрованные Записи
     */
    $filteredEvents = [];


    /**
     * Фильтрация Записей
     */

    foreach ( $response[ "data" ] as $event ) {

        if ( $event[ "client_id" ] == $requestData->clients_id ) {

            $filteredEvents[] = $event;

        }

    } // foreach. $response[ "data" ]


    /**
     * Обновление списка Записей
     */
    $response[ "data" ] = $filteredEvents;

} // if. $requestData->clients_id