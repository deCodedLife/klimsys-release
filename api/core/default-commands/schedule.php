<?php
//ini_set( "display_errors", true );
/**
 * @file Стандартная команда schedule.
 * Используется для вывода расписания
 */

/**
 * Сформированное расписание
 */
$resultSchedule = [];

/**
 * Начало рабочего дня
 */
$workdayStart = strtotime( "00:00" );

/**
 * Конец рабочего дня
 */
$workdayEnd = strtotime( "23:59" );

/**
 * Список временных отрезков
 */
$stepsList = [];

/**
 * Время начала/завершения событий
 * Используется, для нестандартных временных отрезков
 */
$eventTimes = [];

/**
 * Кол-во минут в шаге
 */
$minutesPerStep = 60;
if ( $requestData->step ) $minutesPerStep = $requestData->step;

/**
 * Кол-во дней в диапазоне графика
 */
$daysRange = 30;
if ( $requestData->days_range ) $daysRange = $requestData->days_range;

/**
 * Текущий временной отрезок
 * Используется, для формирования списка временных отрезков графика посещений
 */
$currentStep = $workdayStart;

/**
 * Детальная информация об Исполнителях
 */
$performersDetail = [];

/**
 * Фильтр Исполнителей
 */
$performersFilter = [];

/**
 * Дата начала и окончания графика по умолчанию
 */
if ( !$requestData->start_at ) $requestData->start_at = date( "Y-m-d" );
if ( !$requestData->end_at ) $requestData->end_at = date(
    "Y-m-d", strtotime( "+$daysRange days", strtotime( $requestData->start_at ) )
);

if ( !$requestData->user_id && !$requestData->users_id ) $requestData->end_at = null;

/**
 * Принудительная сортировка
 */
$requestData->sort_by = "start_at";

/**
 * Снятие лимита
 */
$requestData->limit = 0;


/**
 * @hook
 * Объявление переменных
 */
if ( file_exists( $public_customCommandDirPath . "/hooks/after-variables-loading.php" ) )
    require( $public_customCommandDirPath . "/hooks/after-variables-loading.php" );


/**
 * Определение ключа временного отрезка
 *
 * @param $time  string  Время
 *
 * @return integer
 */
function getStepKey ( $time ) {

    global $stepsList;

    /**
     * Ключ указанного временного отрезка
     */
    $stepKeyResult = null;

    foreach ( $stepsList as $stepKey => $step ) {

        if ( $step === $time ) $stepKeyResult = $stepKey;

    } // foreach. $stepsList


    return $stepKeyResult;

} // function. getStepKey


/**
 * Отработка стандартного get запроса
 */
//$API->returnResponse( $requestData );
require( "get.php" );


/**
 * @hook
 * Отработка стандартного get запроса
 */
if ( file_exists( $public_customCommandDirPath . "/hooks/after-get-command.php" ) )
    require( $public_customCommandDirPath . "/hooks/after-get-command.php" );


/**
 * Получение детальной информации об Исполнителях
 */
require( "components/schedule/get-performers-detail.php" );


/**
 * Получение времени начала и окончания событий
 */
require( "components/schedule/get-event-times.php" );



/**
 * @hook
 * Получение событий
 */
if ( file_exists( $public_customCommandDirPath . "/hooks/get-event-times.php" ) )
    require( $public_customCommandDirPath . "/hooks/get-event-times.php" );


/**
 * Формирование списка временных отрезков
 */
require( "components/schedule/get-steps.php" );

/**
 * Привязка дат к расписанию
 */
require( "components/schedule/dates-to-schedule.php" );


/**
 * Если посещения попадают на сайт - отключаем формирование event
 */

require( "components/schedule/events-to-schedule.php" );

/**
 * Привязка свободных ячеек к расписанию
 */
require( "components/schedule/empty-to-schedule.php" );


/**
 * @hook
 * Сформированное расписание
 */
if ( file_exists( $public_customCommandDirPath . "/hooks/generated-schedule.php" ) )
    require( $public_customCommandDirPath . "/hooks/generated-schedule.php" );


$response[ "data" ] = [
    "steps_list" => $stepsList,
    "schedule" => $resultSchedule
];