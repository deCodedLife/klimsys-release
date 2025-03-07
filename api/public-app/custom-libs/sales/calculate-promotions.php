<?php

require_once ( "promotions/index.php" );

use Sales\Sales;
use Sales\Product;
use Sales\Discount;
use Sales\Subject;
use Sales\Modifier;


$isReturn = ( $requestData->action ?? 'sell' ) === "sellReturn";
$store_id = $requestData->store_id;

//if ( !$store_id ) $API->returnResponse( "Не указан филиал", 400 );

/**
 * Получение скидок
 */
foreach ( Discount::GetActiveDiscounts( "promotions" ) as $discount ) {

    // При возврате не считаем скидки
    if ( $isReturn ) continue;

    /**
     * Получение филиалов в которых действует акция
     */
    $stores = $API->DB->from( "promotionStores" )
        ->where( "promotion_id", $discount[ "id" ] );

    $promotionStores = [];

    foreach ( $stores as $store ) {

        $promotionStores[] = $store[ "store_id" ];

    } // foreach ( $stores as $store ) {

    if ( !in_array( $store_id, $promotionStores ) ) continue;


    $servicesGroups = [];
    $Discount = new Discount();
    $Discount->GetModifiers( "promotion_id", $discount[ "id" ] );



    /**
     * Добавляем услуги как участников акции
     */
    foreach ( $allServices as $service ) {

        $Discount->Subjects[] = new Subject(
            "services",
            $service[ "id" ],
            $service[ "price" ],
            Discount::getGroups( $service[ "category_id" ], "serviceGroups" )
        );

    } // foreach $allServices as $service



    /**
     * Не забываем про клиентов
     */
    foreach ( $API->DB->from( "clientsGroupsAssociation" )->where( "client_id", $requestData->client_id ) as $group )
        $clientGroups[] = $group[ "clientGroup_id" ];

    $Discount->Subjects[] = new Subject(
        "clients",
        $requestData->client_id,
        0,
        $clientGroups ?? []
    );



    /**
     * Смотрим, подходит акция под наши условия
     */
    if ( !$Discount->IsValid() ) continue;
    $newSubjects = $Discount->Apply( $discount[ "id" ] );
    $discountSum = 0;
//    $API->returnResponse( $newSubjects, 500 );

    foreach ( $newSubjects as $subject ) {

        foreach ( $allServices as $index => $service ) {

            if (
                $subject->Type == "services" &&
                $service[ "id" ] == $subject->ID &&
                $service[ "price" ] != $subject->Price
            ) {

                $discountSum -= $subject->Price - $service[ "price" ];
                $service[ "price" ] = $service[ "price" ] - $discountSum;
                $allServices[ $index ] = $service;

                // May cause error in sale return case
                $saleServices[ $index ] = $service;

            }

        } //  foreach ( $allServices as $index => $service ) {

    } // foreach ( $newSubjects as $subject )


    $saleSummary -= $discountSum;

} // foreach. Discount::GetActiveDiscounts( "promotions" ) as $discount

$saleSummary = max( $saleSummary, 0 );