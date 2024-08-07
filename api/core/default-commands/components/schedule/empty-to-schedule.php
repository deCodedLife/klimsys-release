<?php

/**
 * @file
 * Привязка свободных ячеек к расписанию
 */


/**
 * Обход дат расписания
 */
foreach ( $resultSchedule as $scheduleDateKey => $scheduleDateDetail ) {

    /**
     * Обход Исполнителей в расписании за текущую дату
     */
    foreach ( $scheduleDateDetail as $schedulePerformerKey => $schedulePerformerDetail ) {

        /**
         * Последний обработанный шаг
         */
        $currentStep = 0;

        /**
         * Кол-во шагов
         */
        $stepsCount = count( $stepsList );

        /**
         * Расписание Исполнителя на текущую дату
         */
        $performerSchedule = $schedulePerformerDetail[ "schedule" ];


        /**
         * Учет ячеек с событиями
         */

        foreach ( $performerSchedule as $performerEvent ) {


            /**
             * Добавление свободной ячейки перед событием
             */
            if ( $currentStep < $performerEvent[ "steps" ][ 0 ] )
                $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "schedule" ][ $currentStep ] = [
                    "steps" => [ $currentStep, (int) $performerEvent[ "steps" ][ 0 ] - 1 ],
                    "status" => "available"
                ];


            /**
             * Обновление последнего обработанного шага
             */
            $currentStep = (int) $performerEvent[ "steps" ][ 1 ] + 1;

        } // foreach. $performerSchedule


        /**
         * Добавление свободной ячейки после событий
         */
        if ( $currentStep < $stepsCount )
            $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "schedule" ][ $currentStep ] = [
                "steps" => [ $currentStep, $stepsCount - 1 ],
                "status" => "available"
            ];


        /**
         * Сортировка ячеек по шагам
         */
        ksort( $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "schedule" ] );


        /**
         * Очистка графика сотрудников от ключей с шагами
         */
        $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "schedule" ] = array_values(
            $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "schedule" ]
        );

    } // foreach. $scheduleDateDetail

} // foreach. $resultSchedule