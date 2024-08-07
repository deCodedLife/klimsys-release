<?php

/**
 * @file
 * Вспомогательные функции
 */


/**
 * Загрузка схем Баз данных.
 * Заполнение каталога схем Баз данных $databaseSchemesCatalog
 *
 * @param $dbSchemesDirPath  string  Путь к директории со схемами Баз данных
 * @param $schemesType       string  Тип схем Баз данных (system / public)
 *
 * @return boolean
 */

function loadDatabaseSchemesDir ( $dbSchemesDirPath, $schemesType ) {

    /**
     * Каталог схем баз данных
     */
    global $databaseSchemesCatalog;


    /**
     * Обход директории схем Баз данных
     */

    if ( !is_dir( $dbSchemesDirPath ) ) return false;

    $dbSchemesDir = opendir( $dbSchemesDirPath );

    while ( false !== ( $dbSchemeFile = readdir( $dbSchemesDir ) ) ) {

        if (
            !$dbSchemeFile ||
            ( $dbSchemeFile == "." ) ||
            ( $dbSchemeFile == ".." )
        ) continue;


        /**
         * Получение пути к схеме базы данных
         */
        $dbSchemeDirPath = "$dbSchemesDirPath/$dbSchemeFile";

        /**
         * Получение названия таблицы
         */
        $tableTitle = substr( $dbSchemeFile, 0, strpos( $dbSchemeFile, "." ) );


        /**
         * Добавление таблицы в очередь на обработку
         */

        $databaseSchemesCatalog[ $tableTitle ][ "path" ][ $schemesType ] = $dbSchemeDirPath;

    } // readdir. $dbSchemesDir


    return true;

} // function. loadDatabaseSchemesDir


/**
 * Чтение схемы таблицы
 *
 * @param $generatedTableScheme  array   Сформированная схема
 * @param $schemePath            string  Путь к схеме
 *
 * @return array
 */
function readScheme ( $generatedTableScheme, $schemePath ) {

    /**
     * Ядро API
     */
    global $API;

    /**
     * Отчет об обновлении
     */
    global $updateReport;


    /**
     * Получение схемы
     */
    $tableScheme = file_get_contents( $schemePath );
    if ( !$tableScheme ) $API->returnResponse( "Ошибка загрузки схемы: $schemePath", 500 );

    /**
     * Декодирование схемы таблицы
     */
    $tableScheme = json_decode( $tableScheme, true );
    if ( $tableScheme === null ) $API->returnResponse( "Ошибка обработки схемы: $schemePath", 500 );


    /**
     * Обход св-в схемы таблицы
     */
    foreach ( $tableScheme[ "properties" ] as $schemeProperty )
        $generatedTableScheme[ "properties" ][ $schemeProperty[ "article" ] ] = $schemeProperty;


    /**
     * Обработка системных записей
     */

    if ( $tableScheme[ "rows_key" ] && $tableScheme[ "rows" ] ) {

        $generatedTableScheme[ "rows_key" ] = $tableScheme[ "rows_key" ];


        foreach ( $tableScheme[ "rows" ] as $row ) {

            /**
             * Проверка на наличие обязательных св-в
             */
            if ( !$row[ $tableScheme[ "rows_key" ] ] ) continue;


            /**
             * Получение ключа записи
             */
            $rowKey = "_" . $row[ $tableScheme[ "rows_key" ] ];


            /**
             * Запретить затирание св-в title
             */
            if ( $generatedTableScheme[ "rows" ][ $rowKey ][ "title" ] )
                $row[ "title" ] = $generatedTableScheme[ "rows" ][ $rowKey ][ "title" ];

            $generatedTableScheme[ "rows" ][ $rowKey ] = $row;

        } // foreach. $tableScheme[ "rows" ]

    } // if. $tableScheme[ "rows_key" ] && $tableScheme[ "rows" ]


    return $generatedTableScheme;

} // function. readScheme