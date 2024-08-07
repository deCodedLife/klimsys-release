<?php

/**
 * @file
 * Обновление базы данных
 */


/**
 * Подключение вспомогательных функций
 */
require_once( "functions.php" );


/**
 * Токен разработчиков
 */
$tokenDevelopers = "0mv0H1UWorjo9I";

/**
 * Токен администраторский
 */
$tokenAdmin = "QH910ec4EyKoJI3v";

/**
 * Каталог схем баз данных
 */
$databaseSchemesCatalog = [];

/**
 * Отчет об обновлении
 */
$updateReport = [];

/**
 * Сформированная схема базы данных
 */
$generatedDBScheme = [];

/**
 * Тестовый режим
 */
$isTest = true;

/**
 * Подключение базы данных
 */
$DB_connection = mysqli_connect(
    $API::$configs[ "db" ][ "host" ],
    $API::$configs[ "db" ][ "user" ],
    $API::$configs[ "db" ][ "password" ],
    $API::$configs[ "db" ][ "name" ]
);
if ( !$DB_connection ) $API->returnResponse( "Не удалось подключится к базе данных" );


/**
 * Проверка токена Пользователя
 */
if (
    ( $requestData->token !== $tokenDevelopers ) &&
    ( $requestData->token !== $tokenAdmin )
)
    $API->returnResponse( "Неверный токен", 403 );


/**
 * Отключение тестового режима
 * @todo Раскомментировать, когда запустим проект на бой
 */
//if (
//    ( $requestData->token === $tokenAdmin ) &&
//    ( $requestData->is_test === "N" )
//) $isTest = false;
if ( $requestData->is_test === "N" ) $isTest = false;


/**
 * Загрузка публичных схем Баз данных
 */
loadDatabaseSchemesDir( $API::$configs[ "paths" ][ "public_db_schemes" ], "public" );

/**
 * Загрузка системных схем Баз данных
 */
loadDatabaseSchemesDir( $API::$configs[ "paths" ][ "system_db_schemes" ], "system" );


/**
 * Формирование схемы базы данных
 */
require_once( "components/db-scheme-generator.php" );

/**
 * Обновление схемы базы данных
 */
require_once( "components/db-scheme-update.php" );


$API->returnResponse( $updateReport );