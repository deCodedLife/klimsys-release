<?php

/**
 * Сформированный график
 */
$generatedGraph = [];
require_once $API::$configs[ "paths" ][ "public_app" ] . "/custom-libs/workdays/createEvents.php";

foreach ( $response[ "data" ] as $eventDate => $events ) {

    foreach ( $events as $event ) {

        /**
         * Получение типа события (рабочий день / выходной)
         */

        $eventDetail = $API->DB->from( $API->request->object )
            ->where( "id", $event[ "id" ] )
            ->limit( 1 )
            ->fetch();

        /**
         * Получаем события для правила
         */
        $ruleEvents = generateRuleEvents( $eventDetail );


        /**
         * Подготавливаем события к выдаче
         */
        foreach ( $ruleEvents as $ruleEvent ) {

            $ruleEventDate = date( "Y-m-d", strtotime( $ruleEvent[ "event_from" ] ) );

            $ruleEventDateTimeStart = date( "H:i", strtotime( $ruleEvent[ "event_from" ] ) );
            $ruleEventDateTimeEnd = date( "H:i", strtotime( $ruleEvent[ "event_to" ] ) );

            $newEvent = $event;
            $newEvent[ "is_rule" ] = $eventDetail[ "is_rule" ];
            $newEvent[ "is_weekend" ] = $ruleEvent[ "is_weekend" ];

            if ( $eventDetail[ "is_rule" ] === 'N' ) {

                $newEvent[ "background" ] = "primary";
                $generatedGraph[ $ruleEventDate ][ "hasIndividualRule" ] = true;

            }

            $cabinetDetail = $API->DB->from( "cabinets" )
                ->where( "id", $eventDetail[ "cabinet_id" ] )
                ->limit( 1 )
                ->fetch();


            $newEvent[ "title" ] = "$ruleEventDateTimeStart - $ruleEventDateTimeEnd";
            if ( $cabinetDetail )  $newEvent[ "title" ] .= " [Каб. {$cabinetDetail[ "title" ]} ]";
            if ( $eventDetail[ "is_rule" ] === 'Y' ) $newEvent[ "background" ] = "success";

            if ( $ruleEvent[ "is_weekend" ] == "Y" ) {

                $newEvent[ "title" ] = "Отмена приема";
                $newEvent[ "background" ] = "danger";

            }

            $generatedGraph[ $ruleEventDate ][ "events" ][] = $newEvent;

        }

    } // foreach. $events

} // foreach. $response[ "data" ]


/**
 * Фильтруем события. Если в дате имеется отмена дня
 * тогда все события удаляются
 */
foreach ( $generatedGraph as $eventDate => $ruleObject ) {

    $eventList = [];
    $hasIndividualRule = $ruleObject[ "hasIndividualRule" ] ?? false;

    foreach ( $ruleObject[ "events" ] as $event ) {

        if ( $event[ "is_weekend" ] === 'Y'  ) {
            $eventList = [ $event ];
            break;
        }

        if ( $event[ "is_rule" ] === 'Y' && $hasIndividualRule ) continue;
        $eventList[] = $event;

    }

    $generatedGraph[ $eventDate ] = $eventList;

}


$response[ "data" ] = $generatedGraph;
