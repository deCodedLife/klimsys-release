<?php

/**
 * Язык по умолчанию
 */
if ( !$API::$configs[ "system_components" ][ "lang" ] ) $API::$configs[ "system_components" ][ "lang" ] = "ru";


/**
 * Загрузка языков
 */

$langs = [];

if ( file_exists(
    $API::$configs[ "paths" ][ "system_langs" ] . "/" . $API::$configs[ "system_components" ][ "lang" ] . ".json"
) ) {

    $lang = json_decode(
        file_get_contents(
            $API::$configs[ "paths" ][ "system_langs" ] . "/" . $API::$configs[ "system_components" ][ "lang" ] . ".json"
        )
    );

    foreach ( $lang as $langPhraseKey => $langPhraseValue ) $langs[ $langPhraseKey ] = $langPhraseValue;

}

if ( file_exists(
    $API::$configs[ "paths" ][ "public_langs" ] . "/" . $API::$configs[ "system_components" ][ "lang" ] . ".json"
) ) {

    $lang = json_decode(
        file_get_contents(
            $API::$configs[ "paths" ][ "public_langs" ] . "/" . $API::$configs[ "system_components" ][ "lang" ] . ".json"
        )
    );

    foreach ( $lang as $langPhraseKey => $langPhraseValue ) $langs[ $langPhraseKey ] = $langPhraseValue;

}


/**
 * Вывод текста
 *
 * @param $text  string  Текст для вывода
 *
 * @return string
 */
function localizationText ( $text ) {

    global $langs;


    /**
     * Подстановка языка
     */
    if ( ($text[ 0 ] ?? "") === "=" ) {

        /**
         * Ключ фразы
         */
        $phraseKey = substr( $text, 1 );

        /**
         * Обновление текста для вывода
         */
        $text = $langs[ $phraseKey ];
        if ( !$text ) $text = "..";

    } // if. $text[ 0 ] === "="


    return $text;

} // function. localizationText