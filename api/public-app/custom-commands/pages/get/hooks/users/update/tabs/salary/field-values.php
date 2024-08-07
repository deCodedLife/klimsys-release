<?php

$user = $API->DB->from( "users" )
    ->where( "id", $pageDetail[ "row_detail" ][ "id" ] )
    ->fetch();


switch ( $user[ "salary_type" ] ) {

    case "rate_percent":
        $generatedTab["settings"]["areas"][0]["blocks"][0]["fields"][2]["is_visible"] = true;
        break;

    case "rate_kpi":
        $generatedTab["settings"]["areas"][0]["blocks"][0]["fields"][3]["is_visible"] = true;
        $generatedTab["settings"]["areas"][0]["blocks"][0]["fields"][4]["is_visible"] = true;
        $generatedTab["settings"]["areas"][0]["blocks"][0]["fields"][5]["is_visible"] = true;
        break;

} // switch ( $user[ "salary_type" ] )



/**
 * Услуги врача
 */
$userServices = [];


/**
 * Текущие услуги
 */
foreach ($generatedTab["settings"]["areas"][0]["blocks"][0]["fields"][2]["value"] as $userService)
    $userServices[] = $userService;


/**
 * Связанные группы услуг
 */

$userServiceGroups = $API->DB->from("serviceGroupEmployees")
    ->where("employeeID", $pageDetail["row_detail"]["id"]);

foreach ($userServiceGroups as $userServiceGroup) {

    $userGroupServices = $API->DB->from("services")
        ->where([
            "category_id" => $userServiceGroup["groupID"],
            "is_active" => "Y"
        ]);


    foreach ($userGroupServices as $userGroupService) {

        /**
         * Проверка привязки услуги
         */

        $isContinueService = false;

        foreach ($userServices as $userService)
            if ($userService["service_id"] == $userGroupService["id"]) $isContinueService = true;

        if ($isContinueService) continue;


        /**
         * Проверка активности услуги
         */

        $serviceDetail = $API->DB->from("services")
            ->where("id", $userGroupService["id"])
            ->limit(1)
            ->fetch();

        if ($serviceDetail["is_active"] === "N") continue;


        $userServices[] = [
            "service_id" => $userGroupService["id"],
            "percent" => 0,
            "fix_sum" => 0
        ];

    } // foreach. $userGroupServices

} // foreach. $userServiceGroups


/**
 * Связанные услуги
 */

$joinUserServices = $API->DB->from("services_users")
    ->where("user_id", $pageDetail["row_detail"]["id"]);

foreach ($joinUserServices as $joinUserService) {

    /**
     * Проверка привязки услуги
     */

    $isContinueService = false;

    foreach ($userServices as $userService)
        if ($userService["service_id"] == $joinUserService["service_id"]) $isContinueService = true;

    if ($isContinueService) continue;


    /**
     * Проверка активности услуги
     */

    $serviceDetail = $API->DB->from("services")
        ->where("id", $joinUserService["service_id"])
        ->limit(1)
        ->fetch();

    if ($serviceDetail["is_active"] === "N") continue;


    $userServices[] = [
        "service_id" => $joinUserService["service_id"],
        "percent" => 0,
        "fix_sum" => 0
    ];

} // foreach. $joinUserServices

$generatedTab["settings"]["areas"][0]["blocks"][0]["fields"][2]["value"] = $userServices;
