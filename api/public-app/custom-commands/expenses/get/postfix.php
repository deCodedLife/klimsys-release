<?php

foreach ( $response[ "data" ] as $key => $row ) {

    $row[ "price" ] = $row[ "price" ] * -1;
    $response[ "data" ][ $key ] = $row;

}
