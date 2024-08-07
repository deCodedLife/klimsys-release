<?php

$sort_by = $requestData->sort_by;
$sort_order = $requestData->sort_order;
$limit = $requestData->limit;

unset($requestData->sort_by);
unset($requestData->sort_order);
