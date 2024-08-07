<?php

const OBJECTS_CATEGORIES = [
    "target",
    "clients"
];
$formFieldValues = [];

$details = $API->DB->from( "promotions" )
    ->where( "id", $pageDetail[ "row_id" ] )
    ->fetch();

$objects = $API->DB->from( "promotionObjects" )
    ->where( "promotion_id", $details[ "id" ] );

function sortObjects( $promotion, $type, $object ): array {

    if ( $object[ "is_group" ] == 'Y' ) $type .= "Groups";

    if ( $object[ "is_excluded" ] == 'Y' ) {
        $promotion[ "excluded" . $type ][ "value" ][] = (int) $object[ "object_id" ];
        return $promotion;
    }

    if ( $object[ "is_required" ] == 'Y' ) {
        $promotion[ "required" . $type ][ "value" ][] = (int) $object[ "object_id" ];
        return $promotion;
    }

    $promotion[ lcfirst( $type ) ][ "value" ][] = (int) $object[ "object_id" ];
    return $promotion;

} // sortObjects $promotion, $type, $object




foreach ( $objects as $object )
    $formFieldValues = sortObjects( $formFieldValues, ucfirst( $object[ "type" ] ), $object );