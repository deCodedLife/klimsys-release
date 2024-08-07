<?php

/**
 * @file
 * Удаление файла
 */


/**
 * Получение пути к файлу
 */
$filePath = $API::$configs[ "paths" ][ "uploads" ] . "/" . $API::$configs[ "company" ];
$filePath .= "/$requestData->object/$requestData->row_id/$requestData->title";


if ( file_exists( $filePath ) ) unlink( $filePath );