<?php
/**
 * Сформированный список
 */
$returnVisits = [];

foreach ( $response[ "data" ] as $client ) {

    if ( $client[ "geolocation" ] ) {

        $client[ "title" ] = $client[ "last_name" ] . " " . $client[ "first_name" ] . " " .  $client[ "patronymic" ];
        $client[ "url" ] = "/clients/update/" . $client[ "id" ];

        $returnVisits[] = [
            "title" => $client[ "title" ],
            "url" => $client[ "url" ],
            "geolocation" => $client[ "geolocation" ]
        ];

    }

}

$response[ "data" ] = $returnVisits;
