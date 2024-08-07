<?php

if ( $requestData->employees ) {

    $serviceGroupEmployees = $API->DB->from( "serviceGroupEmployees" )
        ->where( "groupID", $requestData->id );

    $afterUsers = [];

    foreach ( $serviceGroupEmployees as $serviceGroupEmployee ) {

        $afterUsers[] = $serviceGroupEmployee[ "employeeID" ];

    }

    $beforeUsers = [];

    foreach ( $requestData->employees as $employee ) {

        $beforeUsers[] = $employee;

    }

    $output = array_merge( array_diff( $afterUsers, $beforeUsers ), array_diff( $beforeUsers, $afterUsers ) );

    foreach ( $output as $employee ) {

        if ( !in_array( $employee, $afterUsers ) ) {

            $services = $API->DB->from( "services" )
                ->where( "category_id", $requestData->id );

            foreach ( $services as $service ) {

                $servicesUser = $API->DB->from( "services_users" )
                    ->where( [
                        "service_id" => $service[ "id" ],
                        "user_id" => $employee,
                    ] )
                    ->limit( 1 )
                    ->fetch();

                if ( !$servicesUser ) {

                    $API->DB->insertInto( "services_users" )
                        ->values( [
                            "service_id" => $service[ "id" ],
                            "user_id" => $employee,
                        ] )
                        ->execute();

                }

            }

        }

    }

}