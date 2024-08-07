<?php


//foreach ( $response[ "data" ] as $key => $row ) {
//
//    $with_discount = round( $row[ "sum_deposit" ] + $row[ "sum_card" ] + $row[ "sum_cash" ] + $row[ "sum_entity" ], 2 );
//    $without_discount = round( $with_discount + $row[ "sum_bonus" ], 2 );
//
//    $row[ "summary" ] = [
//        "new_price" => $with_discount,
//        "old_price" => $without_discount
//    ];
//
//    if ( $without_discount == $with_discount )
//        $row[ "summary" ] = $with_discount;
//
//
//    $response[ "data" ][ $key ] = $row;
//
//}