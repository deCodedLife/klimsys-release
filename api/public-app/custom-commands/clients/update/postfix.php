<?php


$client = $API->DB->from( "clients" )
    ->where( "id", $requestData->id )
    ->limit( 1 )
    ->fetch();

$user = $API::$userDetail;

/**
 * Бонусы - пополнение
 */

if ( $requestData->bonuses_replenishment ) {

    $changeValue = $requestData->bonuses_replenishment;
    if ( $requestData->bonus_action === "decrease" ) $changeValue = -$changeValue;

    $API->DB->update( "clients" )
    ->set( [
        "bonuses" => $client['bonuses'] + $changeValue
    ] )
    ->where( "id", $requestData->id )
    ->execute();


    $API->DB->insertInto( "bonusHistory" )
    ->values( [
        "user_id" => $user->id,
        "client_id" => $requestData->id,
        "replenished" => $changeValue
    ] )
    ->execute();


    /**
     * Отправка события
     */
    $API->addEvent( "bonusHistory" );

} // if ( $requestData->bonuses_replenishment )

/**
 * Депозит
 */

if ($requestData->deposit_replenishment) {

    $API->DB->update( "clients" )
        ->set( [
            "deposit" => $client['deposit'] + $requestData->deposit_replenishment
        ] )
        ->where( "id", $requestData->id )
        ->execute();


    $API->DB->insertInto( "depositHistory" )
        ->values( [
            "user_id" => $user->id,
            "client_id" => $requestData->id,
            "replenished" => $requestData->deposit_replenishment
        ] )
        ->execute();


    /**
     * Отправка события
     */
    $API->addEvent( "depositHistory" );

}

$API->returnResponse( true );
