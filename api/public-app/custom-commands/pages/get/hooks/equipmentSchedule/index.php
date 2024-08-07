<?php

$userDetails = $API->DB->from( "users_stores" )
    ->innerJoin( "users on users.id = users_stores.user_id" )
    ->where( "users.id", $API::$userDetail->id )
    ->limit( 1 )
    ->fetch();

$pageScheme[ "structure" ][ 0 ][ "settings" ][ "filters" ] = [
    "performers_article" => "equipment_id",
    "performers_table" => "wearableEquipment",
    "performers_title" => "title",
    "store_id" => $userDetails[ "store_id" ] ?? $API->DB->from( "stores" )
            ->limit(1)->fetch()[ "id" ],
    "start_at" =>  date( 'Y-m-d' )
];