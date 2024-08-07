<?php

require_once $publicAppPath . "/custom-libs/discounts/index.php";

use Сashbox\Discount as Discount;
use Cashbox\Subject as Subject;



foreach ( Discount::GetActiveDiscounts( "promotions" ) as $discount ) {

    // При возврате не считаем скидки
    if ( $is_return ) continue;



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
    foreach ( $API->DB->from( "clientsGroupsAssaciation" )->where( "client_id", $requestData->client_id ) as $group )
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

    foreach ( $newSubjects as $subject ) {

        foreach ( $allServices as $index => $service ) {

            if (  $subject->Type == "services" && $service[ "id" ] == $subject->ID && $service[ "price" ] != $subject->Price ) {

                $discountSum -= $subject->Price - $service[ "price" ];
                $service[ "price" ] = $service[ "price" ] - $discountSum;
                $allServices[ $index ] = $service;

                // May cause error in sale return case
                $saleServices[ $index ] = $service;

            }

        }

    }



    $saleSummary -= $discountSum;

} // foreach. Discount::GetActiveDiscounts( "promotions" ) as $discount



$saleSummary = max( $saleSummary, 0 );