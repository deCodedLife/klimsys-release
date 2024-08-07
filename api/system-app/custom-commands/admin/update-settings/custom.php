<?php

/**
 * @file Обновление настроек
 */


/**
 * Сформированные настройки
 */
$settings = [];


/**
 * Создание директории Клиента
 */
if ( !is_dir( $API::$configs[ "paths" ][ "company_uploads" ] ) )
    mkdir( $API::$configs[ "paths" ][ "company_uploads" ] );


/**
 * Определение файла настроек Клиента
 */
$companySettingsFile = file_get_contents( $API::$configs[ "paths" ][ "settings_file" ] );


/**
 * Добавление текущих настроек
 */
if ( $companySettingsFile )
    $settings = (array) json_decode( $companySettingsFile );


/**
 * Обновление настроек
 */

foreach ( $requestData->settings as $settingKey => $setting )
    $settings[ $settingKey ] = $setting;

file_put_contents( $API::$configs[ "paths" ][ "settings_file" ], json_encode( $settings ) );