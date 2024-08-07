<?php

/**
 * @file
 * Получение времени начала и окончания событий
 */

foreach ( $response[ "data" ] as $event ) {

    $eventTimes[] = date(
        "H:i",
        strtotime( $event[ "start_at" ] )
    );
    $eventTimes[] = date(
        "H:i",
        strtotime( $event[ "end_at" ] )
    );

} // foreach. $response[ "data" ]

/**
 * Очистка дублей
 */
$eventTimes = array_unique( $eventTimes );

/**
 * Сортировка временных отрезков
 */
sort( $eventTimes );