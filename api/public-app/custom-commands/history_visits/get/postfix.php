<?php

foreach ( $response[ "data" ] as $key => $visit ) {

    $visit[ "period" ] = date( 'Y-m-d H:i', strtotime( $visit[ "start_at" ] ) ) . " - " . date( "H:i", strtotime( $visit[ "end_at" ] ) );

    foreach ( $visit[ "clients_id" ] as $client_key => $item ) {

        $item[ "href" ] = "clients/update/{$item[ "value" ]}";
        $visit[ "clients_id" ][ $client_key ] = $item;

    }

    $sale_id = $API->DB->from( "saleVisits" )
        ->where( "visit_id", $visit[ "id" ] )
        ->orderBy( "id DESC" )
        ->fetch()[ "sale_id" ];


    if ( $sale_id ) {

        $saleDetails = $API->DB->from( "salesList" )
            ->where( "id", $sale_id )
            ->fetch();

        if ( $saleDetails ) {

            $with_discount = round( $saleDetails[ "sum_deposit" ] + $saleDetails[ "sum_card" ] + $saleDetails[ "sum_cash" ] + $saleDetails[ "sum_entity" ], 2 );
            $without_discount = round( $with_discount + $saleDetails[ "sum_bonus" ], 2 );

            if ( $with_discount == $without_discount ) {

                $visit[ "price" ] = [
                    "new_price" => $with_discount,
                    "old_price" => $without_discount
                ];

            }

            $visit[ "price" ] = $without_discount;


            $payMethod = $saleDetails[ "pay_method" ];

            $visit[ "paymentMethod" ] = $payMethod;
            if ( $payMethod == "cash" ) $visit[ "paymentMethod" ] = "Наличные";
            if ( $payMethod == "card" ) $visit[ "paymentMethod" ] = "Безналичные";
            if ( $payMethod == "online" ) $visit[ "paymentMethod" ] = "Онлайн";
            if ( $payMethod == "parts" ) $visit[ "paymentMethod" ] = "Раздельная";
            if ( $payMethod == "legalEntity" ) $visit[ "paymentMethod" ] = "Юридическое лицо";

        }

    }

    $response[ "data" ][ $key ] = $visit;

}

