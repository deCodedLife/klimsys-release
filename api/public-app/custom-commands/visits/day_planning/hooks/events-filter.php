<?php

$requestSettings[ "sqlQuery" ] = "SELECT * FROM `visits` WHERE is_active = 'Y' AND (user_id = " . $API::$userDetail->id . ") AND start_at BETWEEN '" . $requestData->day . " 00:00:00" . "' AND '" . $requestData->day . " 23:59:59" . "';";
//  OR assist_id = " .  $API::$userDetail->id . "