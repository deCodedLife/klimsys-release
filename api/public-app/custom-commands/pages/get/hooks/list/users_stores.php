<?php

$resultBlockFieldList = [];


foreach ( $blockField[ "list" ] as $blockFieldProperty ) {

    /**
     * Получение значения св-ва
     */
    $blockFieldPropertyDetail = $API->DB->from( "stores" )
        ->where( "id", $blockFieldProperty[ "joined_field_value" ] )
        ->limit( 1 )
        ->fetch();

    if ( !$blockFieldPropertyDetail[ "title" ] ) continue;


    $blockFieldProperty[ "title" ] = $blockFieldPropertyDetail[ "title" ];
    $blockFieldProperty[ "user_id" ] = $API->request->data->context->row_id;

    $resultBlockFieldList[] = $blockFieldProperty;

} // foreach. $blockField[ "list" ]


$blockField[ "list" ] = $resultBlockFieldList;