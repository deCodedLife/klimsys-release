<?php

/**
 * @file Интеграция с кассой Атол
 */

namespace Сashbox;

require_once "product.php";
require_once "payment.php";
require_once "operator.php";



define( "TAXATION_TYPES", [
    "osn",              // общая
    "usnIncome",        // упрощенная (Доход)
    "usnIncomeOutcome", // упрощенная (Доход минус Расход)
    "esn",              // единый сельскохозяйственный налог
    "patent"            // патентная система налогообложения
] );



class Atol
{
    public IOperator $operator;
    public array $items;
    public array $payments;
    public string $taxationType;
    public float $total;
    public string $type;
    public string $uuid;

    public array $sales; // TODO: Убрать старый api в Atol_server
    public string $sale_type;
    public array $clientInfo;
    public bool $electronically;
    public float $summary;
    public string $pay_method;



    /**
     * Конструктор класса
     */
    public function __construct() {

        $this->items = [];
        $this->payments = [];
        $this->operator = new IOperator;
        $this->taxationType = "osn";
        $this->total = 0;
        $this->type = "sell";
        $this->uuid = 0;
        $this->sales = [];
        $this->electronically = false;
        $this->clientInfo = [];

    } // function. __construct



    /**
     * Расчёт стоимости всех элементов
     * @return float
     */
    private function getItemsSummary(): float
    {
        global $API;
        $summary = 0;

        foreach ( $this->items as $item ) {
            $summary += $item->amount ?? 0;
        }

        if ( $summary != $this->summary ) {

            $difference = $this->summary - $summary;
            $this->items[ 0 ]->amount += $difference;
            $this->items[ 0 ]->amount = round( $this->items[ 0 ]->amount, 2 );
            $summary += $difference;

        }

        return $summary;
    } //  function. getItemsSummary



    /**
     * Возвращает полный объект для печати чека
     * @return array
     */
    public function GetReciept()
    {
        global $API;

        $barcode = new IProduct;
        $barcode->SetBarcode( "90311017", "EAN8", $this->getItemsSummary() );

        $this->items[] = $barcode;
        $this->total = $this->getItemsSummary();
        $this->uuid =  $this->sale_type . "-id-" . $this->uuid ?? "0";

        $return = [
            "sale_type" => $this->sale_type == "sellReturn" ? "return" : "sell",
            "sales" => $this->sales,
            "hash" => null,
            "code_return" => "",
            "pay_method" => $this->pay_method,
            "request" => [
                "callbacks" => [
                    "resultUrl" => "http://127.0.0.1:80/receive",
                ],
                "request" => [
                    "items" => $this->items,
                    "operator" => $this->operator,
                    "payments" => $this->payments,
                    "taxationType" => $this->taxationType,
                    "total" => $this->total,
                    "type" => $this->sale_type
                ],
                "uuid" => $this->uuid
            ],
        ];


        if ( count( $this->clientInfo ) > 0 ) $return[ "request" ][ "request" ][ "clientInfo" ] = $this->clientInfo;
        if ( $this->electronically ) $return[ "request" ][ "request" ][ "electronically" ] = $this->electronically;

        return $return;

    } // function. GetReciept


    /**
     * Задать форму налогообложения
     *
     * @param $type string
     * @return bool|string
     */
    public function setTaxationType( string $type )
    {

        if ( !in_array( $type, TAXATION_TYPES ) )
            return "Данный тип налогообложения не поддерживается";

        $this->taxationType = $type;
        return true;

    } // public function setTaxationType

}