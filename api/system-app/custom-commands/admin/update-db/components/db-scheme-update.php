<?php


/**
 * Обход схем таблиц
 */

foreach ( $generatedDBScheme as $tableArticle => $tableScheme ) {

    $tableRowsKey = $tableScheme[ "rows_key" ];
    $tableRows = $tableScheme[ "rows" ];

    $tableScheme = $tableScheme[ "properties" ];


    /**
     * Структура текущей таблицы базы данных
     */
    $currentTableStructure = [];

    /**
     * Устаревшие колонки таблицы
     */
    $deprecatedColumns = [];


    /**
     * Получение структуры текущей таблицы базы данных
     */

    try {

        $tableColumns = mysqli_query( $DB_connection, "SHOW COLUMNS FROM `$tableArticle`" );

    } catch ( Exception $e ) {

        $tableColumns = null;

    } // try. mysqli_query. SHOW COLUMNS FROM `$tableArticle`

    foreach ( $tableColumns as $tableColumn ) {

        /**
         * Исключение системных полей
         */
        if ( $tableColumn[ "Field" ] == "id" ) continue;


        /**
         * Обновление информации о текущей структуры таблицы
         */
        $deprecatedColumns[] = $tableColumn[ "Field" ];
        $currentTableStructure[ $tableColumn[ "Field" ] ] = $tableColumn;

    } // foreach. $tableColumns


    /**
     * Создание таблицы
     */

    if ( !$tableColumns ) {

        if ( $isTest ) {

            $updateReport[] = "Создается таблица '$tableArticle'";

        } else {

            mysqli_query(
                $DB_connection,
                "CREATE TABLE `$tableArticle` ( id INT PRIMARY KEY AUTO_INCREMENT )"
            );

        } // if. $isTest

    } // if. !$tableColumns


    /**
     * Обход св-в таблицы
     */

    foreach ( $tableScheme as $tablePropertyArticle => $tableProperty ) {

        /**
         * Удаление св-ва из списка неактуальных
         */
        foreach ( $deprecatedColumns as $deprecatedColumnKey => $deprecatedColumn )
            if ( $deprecatedColumn == $tablePropertyArticle )
                unset( $deprecatedColumns[ $deprecatedColumnKey ] );


        /**
         * Формирование названия св-ва таблицы.
         * Используется для добавления/изменения в таблице базы данных
         */

        $tablePropertyTitle = "$tablePropertyArticle " . $tableProperty[ "type" ];

//        if (
//            $tableProperty[ "is_required" ] ||
//            $tableProperty[ "is_required" ] != "N"
//        ) $tablePropertyTitle .= " NOT NULL";

        if ( $tableProperty[ "is_required" ] == "Y" )  $tablePropertyTitle .= " NOT NULL";
        else if ( $tableProperty[ "is_required" ] == "N" ) $tablePropertyTitle .= " NULL";
//        else if ( $tableProperty ) $tablePropertyTitle .= " NOT NULL";
//        else $tablePropertyTitle .= " NULL";


        if ( !isset( $currentTableStructure[ $tablePropertyArticle ] ) ) {

            /**
             * Добавление св-ва таблицы
             */


            if ( $isTest ) {

                $updateReport[] = [
                    "event" => "[ $tableArticle ] Добавлено св-во '$tablePropertyArticle'",
                    "detail" => [
                        "type" => $tableProperty[ "type" ],
                        "is_required" => $tableProperty[ "is_required" ]
                    ]
                ];
                continue;

            } // if. $isTest


            /**
             * Формирование запроса на добавления св-ва таблицы
             */
            $addPropertyQuery = "ALTER TABLE `$tableArticle` ADD $tablePropertyTitle";
            $addPropertyQuery .= " COMMENT '" . $tableProperty[ "title" ] . "'";


            /**
             * Добавление значения по умолчанию
             */
            if (
                ( $tableProperty[ "default" ] !== null ) &&
                ( $tableProperty[ "default" ] !== "" )
            ) {

                if ( !in_array( $tableProperty[ "default" ], [ "CURRENT_TIMESTAMP", "CURDATE()" ] ) )
                    $tableProperty[ "default" ] = "'" . $tableProperty[ "default" ] . "'";

                $addPropertyQuery .= " DEFAULT " . $tableProperty[ "default" ];

            } // if. $tablePropertyScheme[ "default" ] !== ""


            $updateReport[] = $addPropertyQuery;
            mysqli_query( $DB_connection, $addPropertyQuery );

        } else {

            /**
             * Редактирование св-ва таблицы
             */


            /**
             * Структура текущего св-ва
             */
            $currentPropertyStructure = $currentTableStructure[ $tablePropertyArticle ];


            /**
             * Перевод IS NULL в boolean тип
             */

            if (
                ( $currentPropertyStructure[ "Null" ] === "YES" ) ||
                ( $currentPropertyStructure[ "Null" ] === true )
            ) $currentPropertyStructure[ "is_required" ] = false;
            else $currentPropertyStructure[ "is_required" ] = true;

            if ( $tableProperty[ "is_required" ] === "Y" ) $tableProperty[ "is_required" ] = true;
            else $tableProperty[ "is_required" ] = false;


            /**
             * Проверка наличия изменений
             */
            if (
                ( $tableProperty[ "type" ] !== $currentPropertyStructure[ "Type" ] ) ||
                ( $tableProperty[ "is_required" ] !== $currentPropertyStructure[ "is_required" ] )
            ) {

                /**
                 * Обновление св-ва
                 */

                if ( $isTest ) {

                    $updateReport[] = [
                        "event" => "[ $tableArticle ] Изменено св-во '$tablePropertyArticle'",
                        "current" => [
                            "type" => $currentPropertyStructure[ "Type" ],
                            "is_required" => $currentPropertyStructure[ "is_required" ]
                        ],
                        "edited" => [
                            "type" => $tableProperty[ "type" ],
                            "is_required" => $tableProperty[ "is_required" ]
                        ]
                    ];
                    continue;

                } // if. $isTest


                $updateReport[] =
                    "ALTER TABLE `$tableArticle` MODIFY COLUMN $tablePropertyTitle COMMENT '" . $tableProperty[ "title" ] . "'";

                mysqli_query(
                    $DB_connection,
                    "ALTER TABLE `$tableArticle` MODIFY COLUMN $tablePropertyTitle COMMENT '" . $tableProperty[ "title" ] . "'"
                );

            } // if. Наличие изменений


            /**
             * Проверка наличия изменений в значении по умолчанию
             */
            if ( $tableProperty[ "default" ] != $currentPropertyStructure[ "Default" ] ) {

                if ( $isTest ) {

                    $updateReport[] = [
                        "event" => "[ $tableArticle ] Изменено св-во по умолчанию '$tablePropertyArticle'",
                        "current" => $currentPropertyStructure[ "Default" ],
                        "edited" => $tableProperty[ "default" ]
                    ];
                    continue;

                } // if. $isTest


                /**
                 * Формирование запроса на изменение значения по умолчанию
                 */

                $updateDefaultValueQuery = "ALTER TABLE `$tableArticle` MODIFY COLUMN $tablePropertyTitle";

                if ( $tableProperty[ "default" ] !== "" && $tableProperty[ "default" ] != null ) {

                    if ( !in_array( $tableProperty[ "default" ], [ "CURRENT_TIMESTAMP", "CURDATE()" ] ) )
                        $tableProperty[ "default" ] = "'" . $tableProperty[ "default" ] . "'";

                    $updateDefaultValueQuery .= " DEFAULT " . $tableProperty[ "default" ];

                } else {

                    $updateDefaultValueQuery .= " DEFAULT NULL";

                } // if. $tablePropertyScheme[ "default" ] !== ""


                $updateReport[] = $updateDefaultValueQuery;
                mysqli_query( $DB_connection, $updateDefaultValueQuery );

            } // if. $tablePropertyScheme[ "default" ] != $currentPropertyStructure[ "Default" ]

        } // if. !isset( $currentTableStructure[ $tablePropertyArticle ] )

    } // foreach. $tableScheme[ "properties" ]


    /**
     * Удаление устаревших св-в
     */

    foreach ( $deprecatedColumns as $deprecatedColumn ) {

        if ( $isTest ) {

            $updateReport[] = "[ $tableArticle ] Удалено св-во '$deprecatedColumn'";
            continue;

        } // if. $isTest


        $updateReport[] = "ALTER TABLE `$tableArticle` DROP COLUMN $deprecatedColumn";
        mysqli_query(
            $DB_connection, "ALTER TABLE `$tableArticle` DROP COLUMN $deprecatedColumn"
        );

    } // foreach. $deprecatedColumns


    /**
     * Проверка наличия обязательных записей
     */
    if ( !$tableRows || !$tableRowsKey ) continue;


    /**
     * Обработка обязательных записей
     */

    foreach ( $tableRows as $row ) {

        /**
         * Поиск записи
         */

        $rowDetail = $API->DB->from( $tableArticle )
            ->where( $tableRowsKey, $row[ $tableRowsKey ] )
            ->limit( 1 )
            ->fetch();


        if ( !$rowDetail || !$rowDetail[ "id" ] ) {

            /**
             * Добавление записи
             */


            /**
             * Значения для вставки
             */
            $insertValues = [];


            /**
             * Добавление полей в запрос
             */

            foreach ( $row as $rowPropertyArticle => $rowPropertyValue )
                $insertValues[ $rowPropertyArticle ] = $rowPropertyValue;

            if ( !$insertValues ) continue;


            $API->DB->insertInto( $tableArticle )
                ->values( $insertValues )
                ->execute();

        } else {

            /**
             * Обновление существующей записи
             */


            /**
             * Значения для вставки
             */
            $updateValues = [];


            /**
             * Добавление полей в запрос
             */

            foreach ( $row as $rowPropertyArticle => $rowPropertyValue )
                $updateValues[ $rowPropertyArticle ] = $rowPropertyValue;

            if ( !$updateValues ) continue;


            $API->DB->update( $tableArticle )
                ->set( $updateValues )
                ->where( [
                    "id" => $rowDetail[ "id" ]
                ] )
                ->execute();

        } // if. !$rowDetail || !$rowDetail[ "id" ]

    } // foreach. $tableScheme[ "rows" ]

} // foreach. $generatedDBScheme