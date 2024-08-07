<?php

/**
 * @file
 * Проверка дедлайнов задач
 */


$overdueTasks = $API->DB->from( "tasks" )
    ->where( [
        "deadline <= ?" => date( "Y-m-d H:i:s" ),
        "is_active" => "Y"
    ] );


foreach ( $overdueTasks as $overdueTask ) {

    if ( $overdueTask[ "status" ] == "set" ) {

        $API->DB->update( "tasks" )
            ->set( [
                "status" => "overdue"
            ] )
            ->where( [
                "id" => $overdueTask[ "id" ]
            ] )
            ->execute();

    } else if ( $overdueTask[ "status" ] == "overdue" ) {

        if ( $overdueTask[ "notifyEvery" ] != 0 && $overdueTask[ "reminderDate" ] === null ) {

            $API->DB->update( "tasks" )
                ->set( [
                    "reminderDate" => date("Y-m-d H:i:s")
                ] )
                ->where( [
                    "id" => $overdueTask[ "id" ]
                ] )
                ->execute();

            $taskId = $overdueTask[ "id" ];

            $API->addNotification(
                "system_alerts",
                "Статус задачи",
                "Задача $taskId просрочена",
                "info",
                $overdueTask[ "performer_id" ],
                ""
            );

            $API->addEvent( "notifications" );

        }

        if ( $overdueTask[ "notifyEvery" ] != 0 && date('Y-m-d H:i:s', strtotime($overdueTask[ "reminderDate" ] . ' +'. $overdueTask[ "notifyEvery" ] . ' minutes')) <= date("Y-m-d H:i:s") ) {

            $API->DB->update( "tasks" )
                ->set( [
                    "reminderDate" => date("Y-m-d H:i:s")
                ] )
                ->where( [
                    "id" => $overdueTask[ "id" ]
                ] )
                ->execute();

            $taskId = $overdueTask[ "id" ];

            $API->addNotification(
                "system_alerts",
                "Статус задачи",
                "Задача $taskId просрочена",
                "info",
                $overdueTask[ "performer_id" ],
                ""
            );

            $API->addEvent( "notifications" );

        }

    }

}
