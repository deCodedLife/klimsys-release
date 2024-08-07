<?php
//ini_set( "display_errors", true );
/**
 * Фильтр по Сотруднику
 */

$filteredUsers = [];

foreach ( $performersRows as $performersRow )
    $filteredUsers[] = $performersRow;

$performersRows = $filteredUsers;


if ( $requestData->user_id ) {

    /**
     * Добавление сотрудников с указанной специальностью
     * в отфильтрованный список
     */
    foreach ( $performersRows as $performersRowKey => $performersRow )
        if ( !in_array( $performersRow[ "id" ], (array) ( $requestData->user_id ?? [] ) ) ) unset( $performersRows[ $performersRowKey ] );

} // if. $requestData->users_id


/**
 * Фильтр по Специальности
 */

if ( $requestData->profession_id ) {


    /**
     * Получение списка сотрудников с указанной специальностью
     */

    $usersWithCurrentProfession = [];

    $usersProfessions = mysqli_query(
      $API->DB_connection,
      "SELECT user_id, profession_id FROM users_professions where profession_id = $requestData->profession_id"
    );

    foreach ( $usersProfessions as $userProfession ) $usersWithCurrentProfession[] = $userProfession[ "user_id" ];


    /**
     * Добавление сотрудников с указанной специальностью
     * в отфильтрованный список
     */
    foreach ( $performersRows as $performersRowKey => $performersRow )
        if ( !in_array( $performersRow[ "id" ], $usersWithCurrentProfession ?? [] ) ) unset( $performersRows[ $performersRowKey ] );


} // if. $requestData->profession_id

/**
 * Фильтр по филиалу
 */

if ( $requestData->store_id ) {

    /**
     * Добавление сотрудников с указанной специальностью
     * в отфильтрованный список
     */
    foreach ( $performersRows as $performersRowKey => $performersRow ) {

        $userStores = mysqli_fetch_array(
            mysqli_query(
                $API->DB_connection,
                "SELECT * FROM users_stores WHERE store_id = $requestData->store_id AND user_id = {$performersRow[ "id" ]}"
            )
        );

        if ( empty( $userStores ) ) unset( $performersRows[ $performersRowKey ] );

    }

} // if. $requestData->users_id