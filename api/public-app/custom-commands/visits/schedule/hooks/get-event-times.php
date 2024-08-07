<?php // X

/**
 * Получение графиков работ Сотрудников
 */
$iterators = [];
$performersWorkSchedule = [];

foreach ( $performersDetail as $performerId => $performerDetail ) {

    if ( !in_array( $performerId, $requestData->user_id ?? [] ) ) continue;

    /**
     * Обход графика работы Сотрудника
     */
    if ( $requestData->end_at ) $requestData->end_at = explode( ' ', $requestData->end_at )[ 0 ];
    else $requestData->end_at = $requestData->start_at;

    $iteratorEnd = strtotime( $requestData->end_at );
    $iteratorEnd = strtotime( "+1 day", $iteratorEnd );

    for (
        $iteratorStart = strtotime( $requestData->start_at );
        $iteratorStart < $iteratorEnd;
        $iteratorStart = strtotime( "+1 day", $iteratorStart )
    ) {


        /**
         * Обход графика работы Сотрудника
         */
        $next_day = strtotime( "+1 day", $iteratorStart );

        $filters = [
            "event_from >= ?" => date( "Y-m-d", $iteratorStart ) . " 00:00:00",
            "event_to < ?" => date( "Y-m-d", $next_day ) . " 00:00:00",
            "user_id" => $performerId,
            "store_id" => $requestData->store_id,
            "is_weekend" => 'Y'
        ];

        $is_weekend = $API->DB->from( "scheduleEvents" )
            ->where( $filters )
            ->fetch();

        if ( $is_weekend ) continue;
        unset( $filters[ "is_weekend" ] );
        $filters[ "is_rule" ] = 'N';

        $hasEvents = $API->DB->from( "scheduleEvents" )
            ->where( $filters )
            ->fetch();

        if ( !$hasEvents ) unset( $filters[ "is_rule" ] );

        $performerWorkSchedule = $API->DB->from( "scheduleEvents" )
            ->where( $filters )
            ->orderBy( "event_from ASC" );

        foreach ( $performerWorkSchedule as $scheduleEvent ) {

            /**
             * Игнорирование выходных
             */
            if ( $scheduleEvent[ "is_weekend" ] == "Y" ) continue;


            /**
             * Получение даты графика работы
             */

            $scheduleEventDate = date( "Y-m-d", strtotime( $scheduleEvent[ "event_from" ] ) );

            $performersWorkSchedule[ $performerId ][ $scheduleEventDate ][] = [
                "from" => date( "H:i", strtotime( $scheduleEvent[ "event_from" ] ) ),
                "to" => date( "H:i", strtotime( $scheduleEvent[ "event_to" ] ) ),
                "cabinet_id" => $scheduleEvent[ "cabinet_id" ]
            ];


            /**
             * Добавление события в список шагов
             */

            $eventTimes[] = date(
                "H:i",
                strtotime( $scheduleEvent[ "event_from" ] )
            );

            $eventTimes[] = date(
                "H:i",
                strtotime( $scheduleEvent[ "event_to" ] )
            );

        } // foreach. $performerWorkSchedule
    }

} // foreach. $performersDetail


/**
 * Очистка дублей
 */
$eventTimes = array_unique( $eventTimes );
/**
 * Сортировка временных отрезков
 */
sort( $eventTimes );