<?php

/**
 * @file
 * Получение системных компонентов
 */

if ( !$API::$configs[ "system_components" ] )
    $API::$configs[ "system_components" ] = [];


$response[ "data" ] = $API::$configs[ "system_components" ];