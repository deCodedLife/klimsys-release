<?php



/**
 * Подсчёт итоговой стоимости посещения
 */

$saleSummary = 0;



/**
 * Получение итоговой суммы продажи
 */

foreach ( $saleVisits as $visit ) {

    $visitPrice = $visit[ "price" ];

    if ( $visit[ "discount_type" ] == "fixed"   ) $visitPrice -= $visit[ "discount_value" ];
    if ( $visit[ "discount_type" ] == "percent" ) $visitPrice -= ($visitPrice / 100) * $visit[ "discount_value" ];

    $visitPrice = max( $visitPrice, 0 );
    $saleSummary += $visitPrice;

} // foreach. $saleVisits as $visit

if ( $is_return ) {

    $saleDetails = $API->DB->from( "sales" )
        ->where( "id", $requestData->id )
        ->fetch();

    $saleSummary = $saleDetails[ "summary" ];

}



/**
 * Получение списка посещений (в том числе совмещённых)
 */

require ( $publicAppPath . '/custom-commands/sales/hook/' . 'apply-discounts.php' );



/**
 * Вычет депозита и бонусов для расчёта сумм налички и карты
 */

$amountOfPhysicalPayments = 0;
$amountOfPhysicalPayments = $saleSummary - ( $requestData->bonus_sum + $requestData->deposit_sum );

$saleServicesPrice = 0;
$allServicesPrice = 0;

/**
 * Подсчёт стоимости посещения без скидок
 */

foreach ( $allServices as $service )
    $allServicesPrice += $service[ "price" ];

foreach ( $saleServices as $service )
    $saleServicesPrice += $service[ "price" ];



/**
 * Нахождение скидки для товаров по формуле (стоимость со скидками / стоимость без скидок)
 */

$discountPerProduct = $amountOfPhysicalPayments / $allServicesPrice;



/**
 * Нахождение суммы для налички и карты с учётом скидок
 */

$amountOfPhysicalPayments = $saleServicesPrice * $discountPerProduct;
$amountOfPhysicalPayments = round( $amountOfPhysicalPayments, 2 );

$saleSummary = $amountOfPhysicalPayments;

if ( $requestData->return_bonuses == "Y" ) $saleSummary += $requestData->bonus_sum;
if ( $requestData->return_deposit == "Y" ) $saleSummary += $requestData->deposit_sum;

if ( $is_return ) {

    $saleServicesPrice = 0;
    $allServicesPrice = 0;

    /**
     * Подсчёт стоимости посещения без скидок
     */

    foreach ( $allServices as $service )
        $allServicesPrice += $service[ "price" ];

    foreach ( $saleServices as $service )
        $saleServicesPrice += $service[ "price" ];



    /**
     * Нахождение скидки для товаров по формуле (стоимость со скидками / стоимость без скидок)
     */

    $discountPerProduct = $amountOfPhysicalPayments / $allServicesPrice;



    /**
     * Нахождение суммы для налички и карты с учётом скидок
     */

    $amountOfPhysicalPayments = $saleServicesPrice * $discountPerProduct;
    $amountOfPhysicalPayments = round( $amountOfPhysicalPayments, 2 );

    $saleSummary = $amountOfPhysicalPayments;

    if ( $requestData->return_bonuses == "Y" ) $saleSummary += $requestData->bonus_sum;
    if ( $requestData->return_deposit == "Y" ) $saleSummary += $requestData->deposit_sum;

} // if. $is_return




