<?php

/**
 * Принудительная фильтрация по Роли
 */
$requestData->role_id = $API::$userDetail->role_id;
$requestData->sort_by = "created_at";
$requestData->sort_order = "desc";
$requestData->limit = 20;