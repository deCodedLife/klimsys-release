<?php

/**
 * Обход каталога схем таблиц
 */
foreach ( $databaseSchemesCatalog as $databaseSchemeArticle => $databaseScheme ) {

    /**
     * Сформированная схема таблицы
     */
    $generatedTableScheme = [];


    /**
     * Чтение публичной схемы таблицы
     */
    if ( $databaseScheme[ "path" ][ "public" ] )
        $generatedTableScheme = readScheme(
            $generatedTableScheme, $databaseScheme[ "path" ][ "public" ]
        );

    /**
     * Чтение системной схемы таблицы
     */
    if ( $databaseScheme[ "path" ][ "system" ] )
        $generatedTableScheme = readScheme(
            $generatedTableScheme, $databaseScheme[ "path" ][ "system" ]
        );


    /**
     * Добавление обязательных системных полей
     */

    $generatedTableScheme[ "properties" ][ "is_system" ] = [
        "title" => "Системное поле",
        "article" => "is_system",
        "type" => "char(1)",
        "is_required" => "Y",
        "default" => "N"
    ];


    /**
     * Обновление сформированной схемы базы данных
     */
    $generatedDBScheme[ $databaseSchemeArticle ] = $generatedTableScheme;

} // foreach. $databaseSchemesCatalog