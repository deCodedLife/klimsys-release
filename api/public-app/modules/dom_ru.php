<?php

/**
 * @file
 * Интеграция с IP телефонией Дом.ру
 */

class IPCallsDomRu {

    /**
     * Настройки IP телефонии
     */
    private $settings = null;

    function __construct ( $ipCallsSettings ) {

        /**
         * Подключение настроек IP телефонии
         */
        $this->settings = $ipCallsSettings;

    } // function. __construct


    /**
     * Список аккаунтов
     *
     * return bool
     */
    public function getAccounts () {

        $queryUrl = $this->settings[ "crm_url" ] . "?token=" . $this->settings[ "token" ] . "&cmd=accounts";

        return file_get_contents( $queryUrl );

    } // function. getAccounts

    /**
     * История
     *
     * return bool
     */
    public function getHistory ( $is_month = false ) {

        if ( !$is_month ) $queryUrl = $this->settings[ "crm_url" ] . "?token=" . $this->settings[ "token" ] . "&cmd=history";
        else $queryUrl = $this->settings[ "crm_url" ] . "?token=" . $this->settings[ "token" ] . "&cmd=history&period=last_month";


        return file_get_contents( $queryUrl );

    } // function. getHistory

    /**
     * Инициализация звонка
     *
     * return bool
     */
    public function makeCall ( $clientPhone, $employee ) {

        $queryUrl = $this->settings[ "crm_url" ] . "?token=" . $this->settings[ "token" ] . "&cmd=makeCall&phone=$clientPhone&user=$employee";

        $response = file_get_contents(
            $queryUrl,
            false,
            stream_context_create(
                array(
                    'http' => array(
                        'method' => 'POST'
                    )
                )
            )
        );

        return $response;

    } // function. makeCall

} // class. Services


$settings = $API->DB->from( "settings" )
    ->where( [
        "id" => 1
    ] )
    ->limit( 1 )
    ->fetch();

$IPCallsDomRu = new IPCallsDomRu( $settings );