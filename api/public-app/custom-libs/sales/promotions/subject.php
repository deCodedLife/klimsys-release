<?php

namespace Sales;

class Subject
{

    public string $Type;
    public int $ID;
    public float $Price;
    public array $GroupsIn;


    /**
     * @param string $type
     * @param int $id
     * @param array $groupsIn
     */
    public function __construct(
        string $type,
        int $id,
        float $price,
        array $groupsIn
    ) {

        $this->Type = $type;
        $this->ID = $id;
        $this->Price = $price;
        $this->GroupsIn = $groupsIn;

    }

}