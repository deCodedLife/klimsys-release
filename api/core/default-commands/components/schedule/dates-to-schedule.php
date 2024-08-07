<?php

/**
 * @file
 * Привязка дат к расписанию
 */


/**
 * Дата начала графика
 */
$scheduleFrom = strtotime( $requestData->start_at );

/**
 * Дата окончания графика
 */
$scheduleTo = strtotime( $requestData->end_at );

/**
 * Обработанная дата
 */
$currentScheduleDate = $scheduleFrom;


/**
 * Обход дат расписания
 */

while ( $currentScheduleDate <= $scheduleTo ) {

    global $resultSchedule, $API;


    /**
     * Получение текущей даты
     */
    $scheduleDate = date( "Y-m-d", $currentScheduleDate );

    /**
     * Привязка даты в расписание
     */
    $resultSchedule[ $scheduleDate ] = null;


    /**
     * Привязка Исполнителей к дате
     */
    foreach ( $performersDetail as $performerId => $performerTitle ) {

        $resultSchedule[ $scheduleDate ][ $performerId ] = [
            "performer_id" => (int) $performerId,
            "performer_href" => "$requestData->performers_table/update/$performerId",
            "performer_title" => $performerTitle,
            "schedule" => []
        ];

    } // foreach. $performersDetail


    /**
     * Обновление текущей даты
     */
    $currentScheduleDate = strtotime( "+1 day", $currentScheduleDate );

} // while. $currentScheduleDate <= $scheduleTo