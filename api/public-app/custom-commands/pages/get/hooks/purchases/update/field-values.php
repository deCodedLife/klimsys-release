<?php
/**
 * Отображение скрытых полей если они заполнены
 */
if ( $pageDetail[ "row_detail" ][ "purchases_products" ] ) $formFieldValues[ "purchases_products" ][ "is_visible" ] = true;
if ( $pageDetail[ "row_detail" ][ "purchases_consumables" ] ) $formFieldValues[ "purchases_consumables" ][ "is_visible" ] = true;

if ( $pageDetail[ "row_detail" ][ "purchases_consumables" ] && $pageDetail[ "row_detail" ][ "purchases_products" ] ) {

    if ( $pageDetail[ "row_detail" ][ "purchases_consumables" ] == "products" ) {

        $formFieldValues[ "purchases_products" ][ "is_visible" ] = true;
        $formFieldValues[ "purchases_consumables" ][ "is_visible" ] = false;

    } else {

        $formFieldValues[ "purchases_consumables" ][ "is_visible" ] = true;
        $formFieldValues[ "purchases_products" ][ "is_visible" ] = false;

    }


}
