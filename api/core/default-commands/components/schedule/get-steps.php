<?php

/**
 * @file
 * Формирование списка временных отрезков
 */

while ( $currentStep <= $workdayEnd ) {

    /**
     * Время текущего отрезка
     */
    $currentStepTime = date( "H:i", $currentStep );


    /**
     * Пополнение нестандартных временных отрезков
     */
    foreach ( $eventTimes as $eventTimeKey => $eventTime ) {

        if ( $eventTime >= $currentStepTime ) continue;
        if ( $stepsList && ( $stepsList[ count( $stepsList ) - 1 ] > $eventTime ) ) continue;

        $stepsList[] = $eventTime;

    } // foreach. $eventTimes


    /**
     * Пополнение списка временных отрезков
     */
    $stepsList[] = $currentStepTime;

    /**
     * Обновление текущего временного отрезка
     */
    $currentStep = strtotime( "+$minutesPerStep minutes", $currentStep );

} // while. $currentStep <= $workdayEnd

/**
 * Очистка дублей
 */
$stepsList = array_values(
    array_unique( $stepsList )
);