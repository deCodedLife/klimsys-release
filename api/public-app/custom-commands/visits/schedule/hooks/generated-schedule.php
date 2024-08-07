<?php // C

/**
 * Получение филиалов сотрудников
 */

$usersStores = [];
$usersStoresRows = $API->DB->from( "users_stores" );

foreach ( $usersStoresRows as $usersStoresRow )
    $usersStores[ $usersStoresRow[ "user_id" ] ] = $usersStoresRow[ "store_id" ];



/**
 * Обход дат расписания
 */
foreach ( $resultSchedule as $scheduleDateKey => $scheduleDateDetail ) {

    /**
     * Обход Исполнителей в расписании за текущую дату
     */
    foreach ( $scheduleDateDetail as $schedulePerformerKey => $schedulePerformerDetail ) {

        /**
         * Расписание Исполнителя на текущую дату
         */
        $performerSchedule = $schedulePerformerDetail[ "schedule" ];

        /**
         * Обновленное расписание
         */
        $updatedSchedule = [];

        /**
         * Свободный день
         */
        $notWorkDay = true;

        /**
         * Добавлен кабинет к сотруднику
         */
        $isCabinet = false;


        /**
         * Учет графика работ Исполнителя
         */
//        $API->returnResponse( $schedulePerformerDetail );

        foreach ( $performerSchedule as $performerEventKey => $performerEvent ) {

            /**
             * Игнорирование занятого времени
             */
            if ( $performerEvent[ "status" ] !== "available" ) {

                $updatedSchedule[ $performerEvent[ "steps" ][ 0 ] ] = [
                    "steps" => [ $performerEvent[ "steps" ][ 0 ], $performerEvent[ "steps" ][ 1 ] ],
                    "event" => $performerEvent[ "event" ],
                    "status" => $performerEvent[ "status" ]
                ];

                $notWorkDay = false;

                continue;

            } // if. $performerEvent[ "status" ] !== "available"

            /**
             * Шаги в рабочем графике
             */
            $workedScheduleSteps = [];


            /**
             * Определение шагов в рамках рабочего времени Сотрудника
             */

            foreach ( $performersWorkSchedule[ $schedulePerformerKey ][ $scheduleDateKey ] as $performerWorkSchedule ) {

                $workScheduleStepFromKey = getStepKey( $performerWorkSchedule[ "from" ] );
                $workScheduleStepToKey = getStepKey( $performerWorkSchedule[ "to" ] ) - 1;

                foreach ( $stepsList as $stepKey => $step )
                    if (
                        ( $stepKey >= $workScheduleStepFromKey ) && ( $stepKey <= $workScheduleStepToKey )
                    ) $workedScheduleSteps[] = $stepKey;


                /**
                 * Подстановка филиала
                 */
                $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "initials" ][ "store_id" ] = $requestData->store_id ?? $usersStores[ $schedulePerformerKey ];
                $cabinet_id = null;

                /**
                 * Указание кабинета сотрудника
                 */
//                $API->returnResponse( $schedulePerformerKey );
                if ( $performerWorkSchedule[ "cabinet_id" ] ) {

                    $cabinetDetail = mysqli_fetch_array(
                        mysqli_query(
                            $API->DB_connection,
                            "SELECT * FROM cabinets WHERE id = {$performerWorkSchedule[ 'cabinet_id' ]}"
                        )
                    );


                    if ( !$isCabinet ) $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "performer_title" ] .= " [Каб. " . $cabinetDetail[ "title" ] . "]";
                    $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "initials" ][ "cabinet_id" ] = $cabinetDetail[ "id" ];
                    $cabinet_id = $cabinetDetail[ "id" ];

                    $isCabinet = true;

                } // if. $performerWorkSchedule[ "cabinet_id" ]

                foreach ( $stepsList as $stepKey => $step )
                    if (
                        ( $stepKey >= $workScheduleStepFromKey ) &&
                        ( $stepKey <= $workScheduleStepToKey ) &&
                        $cabinet_id
                    ) {
                        $workedScheduleStepsWithCabinets[ $scheduleDateKey ][ $schedulePerformerKey ][] = [
                            "step" => $stepKey,
                            "cabinet_id" => $cabinet_id
                        ];
                    }

                /**
                 * Подстановка сотрудника
                 */
                $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "initials" ][ "user_id" ] =
                    $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "performer_id" ];

            } // foreach. $performersWorkSchedule[ $schedulePerformerKey ][ $scheduleDateKey ]


            /**
             * Игнорирование Сотрудников без графика работ
             */

            if ( count( $workedScheduleSteps ) < 1 ) {

                unset( $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ] );


                /**
                 * Игнорирование пустых дней
                 */
                if ( !$resultSchedule[ $scheduleDateKey ] ) unset( $resultSchedule[ $scheduleDateKey ] );

                continue;

            } // if. count( $workedScheduleSteps ) < 1


            /**
             * Удаление текущего блока.
             * Далее вместо него будут созданы блоки со статусами "available" и "empty" с учетом
             * графика работ Сотрудника
             */
            unset( $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "schedule" ][ $performerEventKey ] );


            /**
             * Последний добавленный шаг.
             * Используется для добавления блоков empty
             */
            $lastAddedStep = $performerEvent[ "steps" ][ 0 ];

            /**
             * Текущий статус.
             * Используется для добавления блоков empty
             */
            $currentStatus = "empty";
            if ( in_array( $performerEvent[ "steps" ][ 0 ], $workedScheduleSteps ?? [] ) ) $currentStatus = "available";


            /**
             * Обход шагов блока
             */

            for (
                $scheduleBlockStepKey = $performerEvent[ "steps" ][ 0 ];
                $scheduleBlockStepKey <= $performerEvent[ "steps" ][ 1 ];
                $scheduleBlockStepKey++
            ) {

                /**
                 * Проверка, входит ли шаг в график работы
                 */
                $scheduleBlockStepStatus = "empty";
                if ( in_array( $scheduleBlockStepKey, $workedScheduleSteps ?? [] ) ) $scheduleBlockStepStatus = "available";


                if (
                    (
                        ( $scheduleBlockStepStatus !== $currentStatus ) &&
                        ( $lastAddedStep < $scheduleBlockStepKey )
                    ) ||
                    ( $scheduleBlockStepKey >= $performerEvent[ "steps" ][ 1 ] )
                ) {

                    /**
                     * Добавление блока
                     */
                    $updatedSchedule[ $lastAddedStep ] = [
                        "steps" => [ $lastAddedStep, $scheduleBlockStepKey - 1 ],
                        "status" => $currentStatus
                    ];
                    $scheduleBlockCabinet = null;

                    foreach ( $workedScheduleStepsWithCabinets[ $scheduleDateKey ][ $schedulePerformerKey ] as $row ) {

                        if ( $currentStatus != "available" ) break;

                        $updatedSchedule[ $lastAddedStep ] = [
                            "steps" => [ $lastAddedStep, $scheduleBlockStepKey - 1 ],
                            "status" => $currentStatus
                        ];

                        if ( $row[ "step" ] >= ($scheduleBlockStepKey - 1) ) {

                            $scheduleBlockCabinet = intval( $row[ "cabinet_id" ] );
                            break;

                        }

                    }

                    if ( $scheduleBlockCabinet )
                        $updatedSchedule[ $lastAddedStep ][ "initials" ][ "cabinet_id" ] = $scheduleBlockCabinet;


                    /**
                     * Заглушка.
                     * Если блок последний, и после него идет блок другого типа - то фиксируем его
                     */
                    if ( $performerEvent[ "steps" ][ 1 ] == $scheduleBlockStepKey )
                        $updatedSchedule[ $scheduleBlockStepKey ] = [
                            "steps" => [ $scheduleBlockStepKey, $scheduleBlockStepKey ],
                            "status" => $scheduleBlockStepStatus
                        ];


                    /**
                     * Обновление последнего добавленного шага
                     */
                    $lastAddedStep = $scheduleBlockStepKey;

                    /**
                     * Обновление текущего статуса
                     */
                    $currentStatus = $scheduleBlockStepStatus;

                } // if. $scheduleBlockStepStatus !== $currentStatus

            } // foreach. $updatedSchedule


            /**
             * Обновление расписания с учетом графика работ
             */
            $updatedSchedule = array_values( $updatedSchedule );
            if ( $updatedSchedule ) $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ][ "schedule" ] = $updatedSchedule;

        } // foreach. $performerSchedule


        /**
         * Обрезка пустых столбцов, при фильтре по клиенту
         */
        if ( $requestData->clients_id && $notWorkDay && $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ] )
            unset( $resultSchedule[ $scheduleDateKey ][ $schedulePerformerKey ] );

    } // foreach. $scheduleDateDetail

} // foreach. $resultSchedule