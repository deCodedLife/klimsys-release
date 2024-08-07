<?php

/**
 * Автоподстановка филиала
 */
$formFieldValues[ "clients_info" ] = [ "title" => "" ];
$formFieldValues[ "author_id" ] = [ "value" => $API::$userDetail->id ];
$formFieldValues[ "notify" ] = [ "value" => true ];
$formFieldValues[ "send_review" ] = [ "value" => true ];
$formFieldValues[ "status" ] = [ "value" => "planning" ];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "data" ][ "status" ] = "planning";