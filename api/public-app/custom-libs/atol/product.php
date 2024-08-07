<?php

namespace Сashbox;

define( "PAYMENT_OBJECTS", [
    "0",                                // Пусто
    "commodity",                        // товар
    "excise",                           // подакцизный товар
    "job",                              // работа
    "service",                          // услуга
    "gamblingBet",                      // ставка азартной игры
    "gamblingPrize",                    // выигрыш азартной игры
    "lottery",                          // лотерейный билет
    "lotteryPrize",                     // выигрыш лотереи
    "intellectualActivity",             // предоставление результатов интерелектуальной деятельности
    "payment",                          // платеж
    "agentCommission",                  // агентское вознаграждение
    "pay",                              // выплата
    "another",                          // иной предмет расчета
    "proprietaryLaw",                   // имущественное право
    "nonOperatingIncome",               // внереализационный доход
    "otherContributions",               // иные платежи и взносы
    "merchantTax",                      // торговый сбор
    "resortFee",                        // курортный сбор
    "deposit",                          // залог
    "consumption",                      // расход
    "soleProprietorCPIContributions",   // взносы на ОПС ИП
    "cpiContributions",                 // взносы на ОМС ИП
    "soleProprietorCMIContributions",   // взносы на ОМС ИП
    "cmiContributions",                 // взносы на ОМС
    "csiContributions",                 // взносы на ОМС
    "casinoPayment"                     // платеж казино
] );

/**
 * Продукт
 * {
 *      "amount": 10,
 *      "name": "Прием (тест) 1",
 *      "paymentObject": "service",
 *      "piece": true,
 *      "price": 10,
 *      "quantity": 1,
 *      "tax": {
 *          "type": "none"
 *      },
 *      "type": "position"
 * },
 * @var $amount         float   Сумма (Цена * Количество)
 * @var $name           string  Название товара / услуги
 * @var $paymentObject  string  Тип (товар, услуга, депозит...)
 * @var $piece          bool    Штучный товар
 * @var $price          float   Стоимость за единицу товара
 * @var $quantity       int     Количество товара
 * @var $taxes          array   Налоги
 * @var $type           string  Тип документа
 */
class IProduct
{
    public float $amount;
    public string $name;
    public string $paymentObject;
    public bool $piece;
    public float $price;
    public int $quantity;
    public array $tax;
    public string $type;

    // FOR BARCODES
    public string $barcode;
    public string $barcodeType;
    public float $total;



    /**
     * Конвертирует продукт в JSON
     * @return IProduct
     */
    public function ToJSON() {

        return $this;

    } // public function ToJSON



    /**
     * Добавление QR кода в ленту чека
     *
     * @param $barcode      string
     * @param $barcodeType  string
     * @param $total        float
     * @return void
     */
    public function SetBarcode (
        string $barcode,
        string $barcodeType,
        float $total
    ) {

        $this->type = "barcode";
        $this->barcode = $barcode;
        $this->barcodeType = $barcodeType;
        $this->total= $total;

    } // public function SetBarcode
}