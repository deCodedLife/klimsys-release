<?php

function getAge( $dob ) {

    $today = date( "Y-m-d" );
    $diff = date_diff( date_create( $dob ), date_create( $today ) );
    return $diff->y;

}

/**
 * Подстановка ФИО
 */

foreach ( $response[ "data" ] as $key => $row ) {

    $row[ "fio" ] = $row[ "last_name" ] . " " . $row[ "first_name" ] . " " . $row[ "patronymic" ];
    $row[ "fullYears" ] = $age = getAge( $row[ "birthday" ] );
    $response[ "data" ][ $key ] = $row;

} // foreach. $response[ "data" ]


if ( $API->isPublicAccount() ) {

    $siteClients = [];

    foreach ( $response[ "data" ] as $client ) {

        $phoneFormat = "+" . sprintf("%s (%s) %s-%s-%s",
                substr( $client[ "phone" ], 0, 1 ),
                substr( $client[ "phone" ], 1, 3 ),
                substr( $client[ "phone" ], 4, 3 ),
                substr( $client[ "phone" ], 7, 2 ),
                substr( $client[ "phone" ], 9 )
            );

        $siteClients[] = [

            "id" => $client[ "id" ],
            "fio" => $client[ "fio" ],
            "phone" => $phoneFormat,

        ];

    }

    $response[ "data" ] = $siteClients;

}