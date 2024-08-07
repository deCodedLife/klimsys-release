<?php

namespace telegram;

function getDefaultVisitHandlers ( $visits, $phone = null ): array {

    global $API;
    return [
        "1" => [
            "api_url" => "https://{$API::$configs[ "company" ]}.docacrm.com",
            "object" => "visits",
            "command" => "update",
            "data" => [
                "context" => [
                    "bot" => true
                ],
                "id" => $visits,
                "is_called" => true,
            ]
        ],
    ];

    /**
     * "2" => [
     * "api_url" => "https://{$API::$configs[ "company" ]}.docacrm.com",
     * "object" => "visits",
     * "command" => "update",
     * "data" => [
     * "context" => [
     * "bot" => true
     * ],
     * "id" => $visits,
     * "reason_id" => 17,
     * "is_active" => false
     * ]
     * ],
     * "3" => [
     * "api_url" => "https://{$API::$configs[ "company" ]}.docacrm.com",
     * "object" => "dom_ru",
     * "command" => "make_call",
     * "data" => [
     * "phone" => $phone,
     * "user" => "loginova_ekaterina@vpbx000000787b.domru.biz"
     * ]
     * ]
     */
}

function getClient ( $client_id ): array
{
    global $API;

    $clientDetails = $API->DB->from( "clients" )
        ->where( "id", $client_id )
        ->fetch();

    $client = [];
    $clientPhone = null;
    if ( !empty( $clientDetails[ "phone" ] ) ) $clientPhone = $clientDetails[ "phone" ];
    else if ( !empty( $clientDetails[ "second_phone" ] ) ) $clientPhone = $clientDetails[ "second_phone" ];

    $client[ "phone" ] = $clientPhone;

    if ( empty( $clientDetails[ "telegram_id" ] ) ) {

        $request = [];
        $request[ "messenger" ] = "{$API::$configs[ "company" ]}_telegram";
        $request[ "first_name" ] = $clientDetails[ "first_name" ];
        $request[ "last_name" ] = $clientDetails[ "last_name" ];
        $request[ "phone" ] = $clientPhone;

        $contacts = $API->curlRequest ( $request, "bot.docacrm.com/add_contact" );
        $contacts = (array)$contacts;
        $telegram_id = $contacts[ $API::$configs[ "company" ] . "_telegram" ]->id;

        $API->DB->update( "clients" )
            ->set( "telegram_id", $telegram_id )
            ->where( "id", $client_id )
            ->execute();

        $clientDetails[ "telegram_id" ] = $telegram_id;

    }

    $client[ "messenger_id" ] = $clientDetails[ "telegram_id" ];
    return $client;

}

function sendMessage ( string $message, array $client, array $handlers = null )
{
    global $API;
    $request = [];
    $request[ "messenger" ] = "{$API::$configs[ "company" ]}_telegram";
    $request[ "user" ] = $client;
    $request[ "message" ] = $message;
    $request[ "handlers" ] = $handlers;
    $API->curlRequest ( $request, "bot.docacrm.com/send_message" );
}