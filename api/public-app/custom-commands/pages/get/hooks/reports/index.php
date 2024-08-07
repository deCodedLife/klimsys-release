<?php

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "daily_report" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date( 'Y-m-d' )
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ]
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "sum_all_services" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date("Y-m-d", strtotime("-1 months"))
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ]
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "sum_all_services" ][ "body" ][ 1 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date("Y-m-d", strtotime("-1 months"))
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ]
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "userServices" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date("Y-m-d", strtotime("-1 months"))
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ]
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "visit_clients" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date("Y-m-d", strtotime("-1 months"))
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ]
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "promotions" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "begin_at",
        "value" => date("Y-m-d", strtotime("-1 months"))
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ]
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "advertise_fixed" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date("Y-m-d", strtotime("-1 months")),
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ]
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "clients" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date("Y-m-d", strtotime("-1 months"))
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ]
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "userCalls" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "start_at",
        "value" => date("Y-m-d", strtotime("-1 months"))
    ],
    [
        "property" => "end_at",
        "value" => date( 'Y-m-d' )
    ]
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "visitsCancelled" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "cancelledDate_start",
        "value" => date("Y-m-d", strtotime("-1 months"))
    ],
    [
        "property" => "cancelledDate_end",
        "value" => date( 'Y-m-d' )
    ]
];

$pageScheme[ "structure" ][ 1 ][ "settings" ][ "equipmentVisitsCancelled" ][ "body" ][ 0 ][ "settings" ][ "filters" ] = [
    [
        "property" => "cancelledDate_start",
        "value" => date("Y-m-d", strtotime("-1 months"))
    ],
    [
        "property" => "cancelledDate_end",
        "value" => date( 'Y-m-d' )
    ]
];
