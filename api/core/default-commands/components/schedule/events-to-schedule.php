<?php

/**
 * @file
 * Привязка событий к расписанию
 */

/**
 * Добавление события в расписание
 *
 * @param $event        object   Событие
 * @param $performerId  integer  ID Исполнителя
 *
 * @return boolean
 */
function addEventIntoSchedule ( $event, $performerId ) {

    global $API;
    global $resultSchedule;
    global $performersDetail;
    global $public_customCommandDirPath;

    /**
     * Игнорирование записей, у сотрудников, которые не выводятся в расписании
     */
    if ( !$performersDetail->$performerId ) return false;


    /**
     * Получение даты события
     */
    $eventDate = date( "Y-m-d", strtotime( $event[ "start_at" ] ) );

    /**
     * Получение шага начала события
     */
    $eventStartStep = getStepKey(
        date( "H:i", strtotime( $event[ "start_at" ] ) )
    );

    /**
     * Получение шага конца события
     */
    $eventEndStep = getStepKey(
        date( "H:i", strtotime( $event[ "end_at" ] ) )
    );
    if ( $eventStartStep < $eventEndStep ) $eventEndStep--;


    /**
     * Описание события.
     * Выводится в ячейке события в Расписании
     */
    $eventDescription = [ $event[ "start_at" ] . "-" . $event[ "end_at" ] ];

    /**
     * Детальная информация о событии.
     * Выводится при наведении на событие в админке
     */
    $eventDetails = [];

    if ( !$event[ "icons" ] ) $event[ "icons" ] = [];


    /**
     * @hook
     * Заполнение детальной информации о событии
     */
    if ( file_exists( $public_customCommandDirPath . "/hooks/event-details.php" ) )
        require( $public_customCommandDirPath . "/hooks/event-details.php" );

    /**
     * Заполнение информации об Исполнителе
     */
    $resultSchedule[ $eventDate ][ $performerId ][ "performer_title" ] = $performersDetail->$performerId;


    /**
     * Добавление события в расписание
     */
    $resultSchedule[ $eventDate ][ $performerId ][ "schedule" ][ $eventStartStep ] = [
        "steps" => [ $eventStartStep, $eventEndStep ],
        "status" => "busy",
        "event" => $event ? [
            "id" => $event[ "id" ],
            "start_at" => $event[ "start_at" ],
            "end_at" => $event[ "end_at" ],
            "description" => $eventDescription,
            "color" => $event[ "color" ],
            "details" => $eventDetails,
            "icons" => $event[ "icons" ]
        ] : null
    ];

    return true;

} // function. addEventIntoSchedule


/**
 * Обработка событий
 */

$all_events = [];

foreach ( $response[ "data" ] as $event ) {

    $events = [];

    $event_start = new DateTime( $event[ "start_at" ] );
    $event_end = new DateTime( $event[ "end_at" ] );
    $days_count = $event_end->diff( $event_start )->format( "%a" );

    if ( $days_count == 0 ) {

        $all_events[] = $event;
        continue;

    }

    for( $iterator = new DateTime( $event[ "start_at" ] ); $iterator < $event_end; $iterator->modify( '+1 day' ) ) {

        $new_event = $event;

        if ( $iterator->format( "m-d" ) != $event_start->format( "m-d" ) )
            $new_event[ "start_at" ] = $iterator->format( "Y-m-d " . $stepsList[ 0 ] ?? "00:00" );

        if ( $iterator->format( "m-d" ) != $event_end->format( "m-d" ) )
            $new_event[ "end_at" ] = $iterator->format( "Y-m-d " . end( $stepsList ) );

        $new_event[ "iter" ] = $iterator->format( "Y-m-d H:i:s" );

        $new_event[ "iter_i" ] = $iterator->format( "m-d" );
        $new_event[ "iter_e" ] = $event_start->format( "m-d" );
        $all_events[] = $new_event;

    }

}

foreach ( $all_events as $event ) {

    /**
     * Получение цвета события
     */
//    if ( !$event[ "status" ][ "color" ] ) $event[ "color" ] = "light";


    /**
     * Разделение обработки на одного или нескольких Исполнителей
     */

    switch ( gettype( $event[ $requestData->performers_article ] ) ) {

        case "integer":
        case "string":

            /**
             * Добавление события в расписание
             */
            addEventIntoSchedule( $event, $event[ $requestData->performers_article ] );

            break;

        case "array":

            /**
             * Добавление события в расписание
             */
            addEventIntoSchedule( $event, $event[ $requestData->performers_article ][ "value" ] );

            break;

    } // switch. gettype( $event[ $requestData->joined_row_article ] )

} // foreach. $response[ "data" ]

