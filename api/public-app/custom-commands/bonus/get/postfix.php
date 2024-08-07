<?php

if ( $requestData->context->block === "list" ) {

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

        $start = date( 'Y-m-d', strtotime( $row[ "start" ] ) );

        $end = date( 'Y-m-d', strtotime( $row[ "end" ] ) );

        if ( $row[ "end" ] == null ) {

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


function array_sort($array, $on, $order = SORT_ASC)
{
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

if ($sort_by == "title") {

    if ($sort_order == "desc") $response["data"] = array_values(array_sort($response["data"], "title", SORT_DESC));
    if ($sort_order == "asc") $response["data"] = array_values(array_sort($response["data"], "title", SORT_ASC));

}

if ($sort_by == "period") {

    if ($sort_order == "desc") $response["data"] = array_values(array_sort($response["data"], "period", SORT_DESC));
    if ($sort_order == "asc") $response["data"] = array_values(array_sort($response["data"], "period", SORT_ASC));

}
