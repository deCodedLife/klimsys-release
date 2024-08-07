<?php

/**
 * @file Восстановление пароля
 */


/**
 * Генератор нового пароля
 */
$generatedPassword = md5( rand() );


/**
 * Обновление пароля
 */
$API->DB->update( "users" )
    ->set( "password", md5( $generatedPassword ) )
    ->where( [
        "email" => $requestData->email,
        "is_system" => "N"
    ] )
    ->execute();


/**
 * Отправка письма с новым паролем
 */
mail( $requestData->email, "Jaunā parole no adwanto.com", $generatedPassword );