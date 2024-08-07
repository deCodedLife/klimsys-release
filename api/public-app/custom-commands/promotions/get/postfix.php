<?php

const OBJECTS_CATEGORIES = [
    "services",
    "clients"
];


function sortObjects( $promotion, $type, $object ): array {

    if ( $object[ "is_group" ] == 'Y' ) $type .= "Groups";

    if ( $object[ "is_excluded" ] == 'Y' ) {
        $promotion[ "excluded" . $type ][] = (int) $object[ "object_id" ];
        return $promotion;
    }

    if ( $object[ "is_required" ] == 'Y' ) {
        $promotion[ "required" . $type ][] = (int) $object[ "object_id" ];
        return $promotion;
    }

    $promotion[ lcfirst( $type ) ][] = (int) $object[ "object_id" ];
    return $promotion;

}

function isPartialOverlap ( $start1, $end1, $start2, $end2 ) {

    $dateTime1 = new DateTime( $start1 );
    $dateTime2 = new DateTime( $end1 );
    $dateTime3 = new DateTime( $start2 );
    $dateTime4 = new DateTime( $end2 );

    if( $dateTime1 <= $dateTime3 && $dateTime2 >= $dateTime4 ) {

        return true; // Период 1 полностью содержит период 2

    } elseif ( $dateTime1 >= $dateTime3 && $dateTime2 <= $dateTime4 ) {

        return true; // Период 1 полностью входит в период 2

    } elseif ( $dateTime1 <= $dateTime3 && $dateTime2 >= $dateTime3 ) {

        return true; // Часть периода 1 находится в периоде 2

    } elseif ( $dateTime1 <= $dateTime4 && $dateTime2 >= $dateTime4 ) {

        return true; // Часть периода 1 находится в периоде

    } else {

        return false; // Периоды не пересекаются

    }
}

foreach ( $response[ "data" ] as $key => $promotion ) {

    $promotionEnd_at = $promotion[ "end_at" ];

    if ( $promotion[ "end_at" ] == null ) $promotionEnd_at = date('d.m.Y H:i:s', strtotime('+100000 day', strtotime($promotion[ "begin_at" ] ) ) );

    if( $begin_at && $end_at && !isPartialOverlap( $begin_at, $end_at, $promotion[ "begin_at" ], $promotion[ "end_at" ] ) ) {

        unset( $response[ "data" ][ $key ] );
        continue;

    }

    $promotionObjects = $API->DB->from( "promotionObjects" )
        ->where( "promotion_id", $promotion[ "id" ] );

    $promotion[ "services" ] = [];

    foreach ( $promotionObjects as $promotionObject ) {

        $promotion = sortObjects( $promotion, ucfirst( $promotionObject[ "type" ] ), $promotionObject );
        $response[ "data" ][ $key ] = $promotion;

    }

}

if ( $requestData->context->block === "list" || $requestData->context->block === "select" ) {

    /**
     * Подстановка периода и типа акции
     */

    $returnRows = [];

    foreach ( $response[ "data" ] as $row ) {

        /**
         * Детальная информация
         */
        $detailPromotion = $API->DB->from( "promotions" )
            ->where( "id", $row[ "id" ] )
            ->limit( 1 )
            ->fetch();

        $start = date( 'Y-m-d', strtotime( $row[ "begin_at" ] ) );

        $end = date( 'Y-m-d', strtotime( $row[ "end_at" ] ) );

        if ( $row[ "end_at" ] == null ) {

            $end = "по н.в.";

        }


        $row[ "period" ] = $start . " - " . $end ;

        if ( $detailPromotion[ "promotion_type" ] == "percent" )
            $row[ "value" ] =  $row[ "value" ] . "%";
        else if ( $row[ "promotion_type" ] == "fixed" )
            $row[ "value" ] =  $row[ "value" ] . "₽";


        $returnRows[] = $row;

    } // foreach. $response[ "data" ]

    $response[ "data" ] = $returnRows;

} // if. $requestData->context->block === "list"

function array_sort ( $array, $on, $order=SORT_ASC ) {

    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;

}

if ( $sort_by == "period" ) {

    if ( $sort_order == "desc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "period", SORT_DESC ) );
    if ( $sort_order == "asc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "period", SORT_ASC ) );

}

if ( $sort_by == "title" ) {

    if ( $sort_order == "desc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "title", SORT_DESC ) );
    if ( $sort_order == "asc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "title", SORT_ASC ) );

}

if ( $sort_by == "value" ) {

    if ( $sort_order == "desc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "value", SORT_DESC ) );
    if ( $sort_order == "asc" ) $response[ "data" ] = array_values( array_sort( $response[ "data" ], "value", SORT_ASC ) );

}
