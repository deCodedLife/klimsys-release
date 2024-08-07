<?php

namespace Sales;



class Modifier
{

    public string $Type;

    public int $ObjectID;
    public int $PromotionID;
    public bool $IsGroup;
    public bool $IsRequired;
    public bool $IsExcluded;

    

    /**
     * Конструктор класса
     *
     * @param int|null $objectID
     * @param bool|null $isGroup
     * @param bool|null $isRequired
     * @param bool|null $isExcluded
     */
    public function __construct(
        int    $objectID = null,
        string $type = null,
        bool   $isGroup = null,
        bool   $isRequired = null,
        bool   $isExcluded = null
    ) {

        $this->Type = $type ?? "";
        $this->ObjectID = $objectID ?? 0;

        $this->IsGroup = $isGroup ?? false;
        $this->IsRequired = $isRequired ?? false;
        $this->IsExcluded = $isExcluded ?? false;

    } // public function __construct



    /**
     * Запись объектов акции в совмещённую таблицу
     *
     * @param int $promotion_id
     * @param Sales\Modifier $modifier
     * @return void
     */
    public static function writeModifier( int $promotion_id, Modifier $modifier ): void {

        global $API;

        $API->DB->insertInto( "promotionObjects" )
            ->values( [
                "promotion_id" => $promotion_id,
                "type" => $modifier->Type,
                "object_id" => $modifier->ObjectID,
                "is_excluded" => $modifier->IsExcluded ? 'Y' : 'N',
                "is_required" => $modifier->IsRequired ? 'Y' : 'N',
                "is_group" => $modifier->IsGroup ? 'Y' : 'N',
            ] )
            ->execute();


    } // writeModifier. Сashbox\IModifier $modifier


    /**
     * Удаление модификаторов
     *
     * @param int $promotion_id
     * @param Sales\IModifier $pattern
     * @return void
     */
    public static function removeByPattern( int $promotion_id, Modifier $pattern ): void {

        global $API;

        $API->DB->deleteFrom( "promotionObjects" )
            ->where( [
                "promotion_id" => $promotion_id,
                "is_excluded" => $pattern->IsExcluded ? 'Y' : 'N',
                "is_required" => $pattern->IsRequired ? 'Y' : 'N',
                "is_group" => $pattern->IsGroup ? 'Y' : 'N'
            ] )
            ->execute();

    }

}