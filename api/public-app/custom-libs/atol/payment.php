<?php

namespace Сashbox;

/**
 * Объект оплаты
 * {
 *      "sum": 10,
 *      "type": "cash"
 * }
 * @var float           $sum    Внесённые средства
 * @var string | int    $type   Тип оплаты
 */
class IPayment
{
    public string $type;
    public float $sum;



    public function __construct( string $type, float $sum ) {

        $this->sum = $sum;
        $this->type = $type;

    }


    public function ToJSON(): array
    {

        return [
            "sum" => $this->sum,
            "type" => $this->type
        ];

    }
}