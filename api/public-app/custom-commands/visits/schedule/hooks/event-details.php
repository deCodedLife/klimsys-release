<?php //X


/**
 * Определение цвета Записи
 */

switch ( $event[ "status" ] ) {

    case "moved":
    case "planned":
    case "planning":
        $event[ "color" ] = "orange";
        break;

    case "ended":
        $event[ "color" ] = "red";
        break;

    case "process":
        $event[ "color" ] = "pink";
        break;

    case "online":
        $event[ "color" ] = "light_blue";
        break;

    case "repeated":
        $event[ "color" ] = "yellow";
        break;

    case "waited":
        $event[ "color" ] = "green";
        break;

    case "remote":
        $event[ "color" ] = "azure";
        break;

    case "prodoctorov":
        $event[ "color" ] = "blue";
        break;

} // switch. $event[ "status" ][ "value" ]

if ( $event[ "status" ] == "ended" && $event[ "is_payed" ] == "Y" ) $event[ "color" ] = "purple";


/**
 * Определение иконки Записи
 */

if ( $event[ "comment" ] != "" && $event[ "comment" ] != "null" ) $event[ "icons" ][] = "more";
if ( $event[ "is_called" ] === 'N' ) $event[ "icons" ][] = "phone";
if ( $event[ "is_payed" ] === 'Y' ) $event[ "icons" ][] = "rub";

/**
 * Получение детальной информации о пациенте
 */
$clientDetail = $API->DB->from( "clients" )
    ->where( "id", $event[ "client_id" ] )
    ->fetch();

if ( $clientDetail[ "present_first_name" ] ) {
    $presentInfo = $clientDetail[ "present_first_name" ] . " ";
    $presentInfo .= $clientDetail[ "present_last_name" ] . " ";
    $presentInfo .= $clientDetail[ "present_patronymic" ];
}

///**
// * Получение детальной информации о сотруднике
// */
//$profession = $API->DB->from( "professions" )
//    ->innerJoin( "users_professions on users_professions.profession_id = professions.id" )
//    ->where( "users_professions.user_id", $event[ "user_id" ] )
//    ->fetch()[ "title" ] ?? "";


/**
 * Получение времени начала и конца Записи
 */
$eventTime =
    date( "H:i", strtotime( $event[ "start_at" ] ) ) . " - " .
    date( "H:i", strtotime( $event[ "end_at" ] ) );

/**
 * Получение пациента
 */
$eventClient = "№" . $clientDetail[ "id" ] . " " . $clientDetail[ "last_name" ] . " " . mb_substr( $clientDetail[ "first_name" ], 0, 1, "UTF-8" ) . ". " . mb_substr( $clientDetail[ "patronymic" ], 0, 1, "UTF-8") . ".";
$eventClientDetails = "№" . $clientDetail[ "id" ] . " " . $clientDetail[ "last_name" ] . " " . $clientDetail[ "first_name" ] . " " . $clientDetail[ "patronymic" ];

/**
 * Получение услуг
 */
$eventServices = "";
foreach ( $event[ "services_id" ] as $eventService ) $eventServices .= $eventService[ "title" ] . ", ";
if ( $eventServices ) $eventServices = substr( $eventServices, 0, -2 );


$phone = [];
if ( $clientDetail[ "phone" ] ) {

    $phoneFormat = "+" . sprintf("%s (%s) %s-%s-%s",
            substr($clientDetail["phone"], 0, 1),
            substr($clientDetail["phone"], 1, 3),
            substr($clientDetail["phone"], 4, 3),
            substr($clientDetail["phone"], 7, 2),
            substr($clientDetail["phone"], 9)
        );

    $phone =  [
        "icon" => "conversation",
        "value" => $phoneFormat
    ];

}

/**
 * Заполнение описания Записи
 */
$eventDescription = [ $eventClient, $eventTime ];

/**
 * Заполнение детальной информации о Записи к врачу
 */

if ( $eventTime ) $eventDetails[] = [
    "icon" => "schedule",
    "value" => $eventTime
];

if ( $eventClientDetails ) $eventDetails[] = [
    "icon" => "customers",
    "value" => $eventClientDetails
];

if ( $phone ) $eventDetails[] = $phone;

//if ( $profession ) $eventDetails[] = [
//    "icon" => "",
//    "value" => $profession
//];

$eventDetails[] = [
    "icon" => "",
    "value" => $event[ "price" ] . "₽"
];

if ( $presentInfo ) $eventDetails[] = [
    "icon" => "",
    "value" => $presentInfo ?? ""
];

if ( $event[ "comment" ] ) $eventDetails[] = [
    "icon" => "",
    "value" => $event[ "comment" ]
];

if ( $API->isPublicAccount() ) {

    $eventDetails = null;
    $event = null;
    $eventDescription = null;

}