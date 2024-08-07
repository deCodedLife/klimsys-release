<?php

/**
 * Создание событий для правил.
 * Создаёт массив объектов - событий
 *{
 * "id": ...,
 * "event_from": "2023-12-04 08:00:00",
 * "event_to": "2023-12-04 15:00:00",
 * "cabinet_id": 75,
 * "is_weekend": "N",
 * "user_id": 132
 * }
 *
 * @param array $rule
 * @return array
 */
function generateRuleEvents( array $rule, $customWorkdays = [] ): array
{

    global $API, $requestData;


    /**
     * Списки событий
     */
    $weekdays = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];
    $generatedEvents = [];


    if ( empty( $customWorkdays ) ) {

        /**
         * Получение дней графика
         */
        $eventWeekdays = $API->DB->from( "workDaysWeekdays" )
            ->where( "rule_id" , $rule[ "id" ] );


        foreach ( $eventWeekdays as $weekday)
            $eventWorkdays[] = $weekday[ "workday" ];

    } else {

        $eventWorkdays = $customWorkdays;

    } // if ( empty( $customWorkdays ) )

    if ( empty( $eventWorkdays ) ) $eventWorkdays = $weekdays;


    /**
     * Итерация графика по дням
     */
    $eventEnd = DateTime::createFromFormat( "Y-m-d H:i:s", $rule[ "event_to" ] );

    for (
        $iterator = DateTime::createFromFormat( "Y-m-d H:i:s", $rule[ "event_from" ] );
        $iterator < $eventEnd;
        $iterator->modify( "+1 day" )
    ) {

        $date = $iterator->format( "Y-m-d" );
        $weekday = date( "l", strtotime( $date ) );
        if ( !in_array( $weekday, $eventWorkdays ) ) continue;

        /**
         * Генерируем событие
         */
        $generatedEvents[] = [
            "id" => $rule[ "id" ] ?? 0,
            "event_from" => $iterator->format( "Y-m-d H:i:s" ),
            "event_to" => $eventEnd->format( "$date H:i:s" ),
            "cabinet_id" => intval( $rule[ "cabinet_id" ] ),
            "store_id" => $rule[ "store_id" ],
            "is_weekend" => ( $rule[ "is_weekend" ] ?? 'N' ),
            "is_rule" => $rule[ "is_rule" ],
            "user_id" => intval( $rule[ "user_id" ] )
        ];

    } // for days iterator


    return $generatedEvents;

} // function generateRuleEvents( int $eventID ): array

function filterRuleEvents( array $eventList ): array {

    foreach ( $eventList as $event ) {

        if ( $event[ "is_weekend" ] === 'Y' ) continue;

        $day = date( 'Y-m-d', strtotime( $event[ "event_from" ] ) );
        $eventsDayMap[ $day ][] = $event;

        if ( $event[ "is_rule" ] === 'Y' ) continue;
        $ignoreRuleDays[] = $day;

    }

    foreach ( $eventsDayMap ?? [] as $key => $events ) {

        if ( !in_array( $key, $ignoreRuleDays ?? [] ) ) {

            $filteredList = array_merge(
                $filteredList ?? [],
                $eventsDayMap[ $key ] ?? []
            );
            continue;

        }

        foreach ( $events as $event ) {

            if ( $event[ "is_rule" ] === 'Y' ) continue;
            $filteredList[] = $event;

        }

    }

    return $filteredList ?? [];
}