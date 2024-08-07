<?php

namespace Сashbox;

/**
 * Оператора кассового аппарата
 * {
 *      "name": "Миннахматовна Э. Ц.",
 *      "vatin": "123654789507"
 * }
 * @var $name   string  Имя Оператора
 * @var $vatin  string  ИНН Оператора
 */
class IOperator
{
    public string $name;
    public string $vatin;
    
    public function ToJSON(): array
    {
        
        return [
            "name" => $this->name,
            "vatin" => $this->vatin
        ];
        
    }
}