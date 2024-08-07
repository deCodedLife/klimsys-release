<?php



namespace Sales;

require_once "modifiers.php";
require_once "subject.php";

class Discount
{

    public string $DB;

    public array $Subjects;
    public array $DiscountModifiers;



    /**
     * Конструктор класса
     */
    public function __construct() {

        $this->DB = "promotions";
        $this->Subjects = [];
        $this->DiscountModifiers = [];

    }



    /**
     * @return bool
     */
    public function IsValid(): bool {

        global $API;
        /**
         * Проверка на необходимые элементы
         */
        foreach ( $this->DiscountModifiers as $filter ) {

            if ( !$filter->IsRequired ) continue;
            $hasRequired = false;

            foreach ( $this->Subjects as $subject  ) {

                if ( $subject->Type != $filter->Type ) continue;

                if ( $filter->IsGroup && in_array( $filter->ObjectID, $subject->GroupsIn ) ) $hasRequired = true;
                if ( $subject->ID == $filter->ObjectID ) $hasRequired = true;

            } // foreach. $this->Subjects as $group

            if ( !$hasRequired ) return false;

        } // foreach. $this->DiscountModifiers as $filter

        return true;

    }



    /**
     * @param int $promotionID
     * @return array
     */
    public function Apply( int $promotionID ): array {

        global $API;

        $promotion = $API->DB->from( $this->DB )
            ->where( "id",  $promotionID )
            ->fetch();

//        $API->returnResponse( $this->DiscountModifiers, 500 );
        /**
         * Фильтрация
         * Перебираем все исключения и выбрасываем
         */
        foreach ( $this->DiscountModifiers as $filter ) {

            if ( !$filter->IsExcluded ) continue;

            foreach ( $this->Subjects as $index => $subject ) {

                /**
                 * Фильтрация по типу
                 */
                if ( $subject->Type != $filter->Type ) continue;

                if ( $filter->IsGroup && in_array( $filter->ObjectID, $subject->GroupsIn ) ) {
                    unset( $this->Subjects[ $index ] );
                    continue;
                }
                if ( $subject->ID == $filter->ObjectID ) unset( $this->Subjects[ $index ] );


            } // foreach. $this->Subjects as $index => $group

        } // foreach. $this->DiscountModifiers as $filter

        $excludedSubjectsObjectID = [];

        foreach ( $this->DiscountModifiers as $modifier )
            $excludedSubjectsObjectID[] = $modifier->ObjectID;


        $summary = 0;

        foreach ( $this->Subjects as $subject ) {

            if ( in_array( $subject->ID, $excludedSubjectsObjectID ) ) continue;
            $summary += $subject->Price;

        }

        if ( $summary < $promotion[ "min_order" ] ) return $this->Subjects;


//        $API->returnResponse( $promotion, 500 );
        /**
         * Применение скидок
         */
        foreach ( $this->Subjects as $subject ) {

            $shouldApply = false;

            foreach ( $this->DiscountModifiers as $filter ) {

                /**
                 * Ищем только объекты и группы объектов, на которые распространяется акция
                 */
                if ( $filter->IsRequired ) continue;
                if ( $filter->IsExcluded ) continue;

                /**
                 * Ищем совпадения
                 */
                if ( $filter->Type != $subject->Type ) continue;
                if ( $filter->IsGroup && in_array( $filter->ObjectID, $subject->GroupsIn ) ) $shouldApply = true;
                if ( !$filter->IsGroup && $filter->ObjectID == $subject->ID ) $shouldApply = true;

            } // foreach. $this->DiscountModifiers as $filter

            if ( !$shouldApply ) continue;

            /**
             * Применяем скидку на объект, если он попадает под условия акции
             */
            $API->returnResponse( $promotion, 500 );
            if ( $promotion[ "promotion_type" ] == "percent" ) $subject->Price -= ( $subject->Price / 100 * $promotion[ "value" ] );
            if ( $promotion[ "promotion_type" ] == "fixed" )   $subject->Price -= $promotion[ "value" ];

            $subject->Price = max( $subject->Price, 0 );

        } // foreach. $this->Subjects as $subject

        return $this->Subjects;

    } // function Apply( int $promotionID ): array



    /**
     * @param $groupID
     * @param $table
     * @return array
     */
    public static function getGroups( $groupID, $table ): array {

        global $API;
        return [];
        $returnIDs = [ $groupID ];

        $details = $API->DB->from( $table )
            ->where( "id", $groupID )
            ->fetch();

        if ( $details[ "parent_id" ] != null ) {

            $returnIDs = array_merge(
                $returnIDs,
                Discount::getGroups( $details[ "parent_id" ], $table )
            );

        } // if $details[ "parent_id" ] != null


        return $returnIDs;

    }



    /**
     * @param array $modifiers
     * @return array
     */
    private function sortModifiers( array $modifiers ): array {

        $sorted = [];

        foreach ( $modifiers as $modifier ) {

            if ( $modifier->IsGroup ) {

                foreach (
                    $this->getGroups(
                        $modifier->ObjectID,
                        substr( $modifier->Type, 0, -1 ) . "Groups"
                    ) as $group
                ) {

                    $sorted[] = new Modifier(
                        $group,
                        $modifier->Type,
                        $modifier->IsGroup,
                        $modifier->IsRequired,
                        $modifier->IsExcluded
                    );

                } // foreach $this->getGroups( $modifier[ "object_id" ], substr( $modifier[ "type" ], 0, -1 ) . "Groups"  as $group

                continue;

            } // if $modifier->IsGroup

            $sorted[] = $modifier;

        } // foreach $modifiers as $modifier

        return $sorted;

    } // function sortModifiers array $modifiers : array


    /**
     * @param $param
     * @param $promotion_id
     * @return void
     */
    public function GetModifiers( $param, $promotion_id ) {

        global $API;

        $modifiers = $API->DB->from( substr( $this->DB, 0, -1 ) . "Objects" )
            ->where( $param, $promotion_id );

        foreach ( $modifiers as $modifier ) {

            $this->DiscountModifiers[] = new Modifier(
                $modifier[ "object_id" ],
                $modifier[ "type" ],
                $modifier[ "is_group" ] == 'Y',
                $modifier[ "is_required" ] == 'Y',
                $modifier[ "is_excluded" ] == 'Y'
            );

        } // foreach. $modifiers as $modifier

        $this->DiscountModifiers = $this->sortModifiers( $this->DiscountModifiers );

    }



    /**
     * Получение списка действующих акций по периоду
     *
     * @param string $database
     * @return array
     */
    public static function GetActiveDiscounts( string $database ) : array {

        global $API;

        return [];

        $returnDiscounts = [];
        $discountList = $API->DB->from( $database )
            ->where( "is_active", "Y" )
            ->fetchAll();

        if ( !$discountList ) return [];

        foreach ( $discountList as $discount ) {

            if ( !$discount[ "begin_at" ] ) $discount[ "begin_at" ] = date( 'Y-m-d' );

            if ( strtotime( $discount[ "begin_at" ] ) > strtotime( date( 'Y-m-d' ) ) )
                continue;

            if ( $discount[ "end_at" ] ) {

                if ( strtotime( $discount[ "end_at" ] ) < strtotime( date( 'Y-m-d' ) ) )
                    continue;

            }

            $returnDiscounts[] = $discount;

        }

        return $returnDiscounts;

    }


}