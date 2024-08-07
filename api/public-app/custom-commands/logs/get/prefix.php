<?php

/**
 * Фильтр логов за день
 */
if ( $requestData->created_at )
    $requestSettings[ "filter" ][ "created_at <= ?" ] = $requestData->created_at . " 23:59:59";


/**
 * Принудительная сортировка
 */
$requestData->sort_by = "id";
$requestData->sort_order = "desc";
