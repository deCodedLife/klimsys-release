<?php

if ( !$requestData->sort_by || $requestData->sort_by == "fio" ) {

    $requestData->sort_by = "last_name";

}

$role = $API->DB->from( "roles" )
    ->where( "id", $API::$userDetail->role_id )
    ->fetch()[ "article" ];


if ( $role == "public" || $API::$userDetail->id == 3 ) $requestData->is_visibleOnSite = "Y";