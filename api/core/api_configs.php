<?php

/**
 * @file
 * Конфигурация API.
 * Содержит системные настройки. Общая для всех Приложений
 */


/**
 * Часовой пояс по умолчанию
 */
date_default_timezone_set( "Europe/Moscow" );


/**
 * Определение артикула компании
 */

$API::$configs[ "company" ] = explode( ".", $_SERVER[ "HTTP_HOST" ] )[ 0 ];


/**
 * Пути
 */

$API::$configs[ "paths" ][ "root" ] = $_SERVER[ "DOCUMENT_ROOT" ];

$API::$configs[ "paths" ][ "core" ] = $API::$configs[ "paths" ][ "root" ] . "/core";
$API::$configs[ "paths" ][ "public_app" ] = $API::$configs[ "paths" ][ "root" ] . "/public-app";
$API::$configs[ "paths" ][ "system_app" ] = $API::$configs[ "paths" ][ "root" ] . "/system-app";
$API::$configs[ "paths" ][ "uploads" ] = $API::$configs[ "paths" ][ "root" ] . "/uploads";
$API::$configs[ "paths" ][ "libs" ] = $API::$configs[ "paths" ][ "root" ] . "/libs";

$API::$configs[ "paths" ][ "company_uploads" ] = $API::$configs[ "paths" ][ "uploads" ] . "/" . $API::$configs[ "company" ];
$API::$configs[ "paths" ][ "settings_file" ] = $API::$configs[ "paths" ][ "company_uploads" ] . "/settings.json";

$API::$configs[ "paths" ][ "default_commands" ] = $API::$configs[ "paths" ][ "core" ] . "/default-commands";
$API::$configs[ "paths" ][ "functions" ] = $API::$configs[ "paths" ][ "core" ] . "/functions";

$API::$configs[ "paths" ][ "analytic_widgets" ] = $API::$configs[ "paths" ][ "public_app" ] . "/analytic-widgets";
$API::$configs[ "paths" ][ "public_db_schemes" ] = $API::$configs[ "paths" ][ "public_app" ] . "/db-schemes";
$API::$configs[ "paths" ][ "public_command_schemes" ] = $API::$configs[ "paths" ][ "public_app" ] . "/command-schemes";
$API::$configs[ "paths" ][ "public_object_schemes" ] = $API::$configs[ "paths" ][ "public_app" ] . "/object-schemes";
$API::$configs[ "paths" ][ "public_page_schemes" ] = $API::$configs[ "paths" ][ "public_app" ] . "/page-schemes";
$API::$configs[ "paths" ][ "public_user_schemes" ] = $API::$configs[ "paths" ][ "public_app" ] . "/user-schemes";
$API::$configs[ "paths" ][ "public_custom_commands" ] = $API::$configs[ "paths" ][ "public_app" ] . "/custom-commands";
$API::$configs[ "paths" ][ "public_langs" ] = $API::$configs[ "paths" ][ "public_app" ] . "/langs";
$API::$configs[ "paths" ][ "public_modules" ] = $API::$configs[ "paths" ][ "public_app" ] . "/modules";
$API::$configs[ "paths" ][ "public_libs" ] = $API::$configs[ "paths" ][ "public_app" ] . "/libs";
$API::$configs[ "paths" ][ "public_cron" ] = $API::$configs[ "paths" ][ "public_app" ] . "/cron";

$API::$configs[ "paths" ][ "system_db_schemes" ] = $API::$configs[ "paths" ][ "system_app" ] . "/db-schemes";
$API::$configs[ "paths" ][ "system_command_schemes" ] = $API::$configs[ "paths" ][ "system_app" ] . "/command-schemes";
$API::$configs[ "paths" ][ "system_object_schemes" ] = $API::$configs[ "paths" ][ "system_app" ] . "/object-schemes";
$API::$configs[ "paths" ][ "system_page_schemes" ] = $API::$configs[ "paths" ][ "system_app" ] . "/page-schemes";
$API::$configs[ "paths" ][ "system_custom_commands" ] = $API::$configs[ "paths" ][ "system_app" ] . "/custom-commands";
$API::$configs[ "paths" ][ "system_langs" ] = $API::$configs[ "paths" ][ "system_app" ] . "/langs";


/**
 * Клиентские настройки
 */

$API::$configs[ "settings" ] = [];

$companySettingsFile = file_get_contents( $API::$configs[ "paths" ][ "settings_file" ] );

if ( $companySettingsFile )
    $API::$configs[ "settings" ] = (array) json_decode( $companySettingsFile );


/**
 * JWT ключ
 */
$API::$configs[ "jwt_key" ] = "GK8VSb4TnWP8VXjD";