<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = API::init(row_lock: true);

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch (Exception $e) {
    API::exitWithException($e, system: $system);
}
# End standard auth

try {
    // api requires a request
    if (isset($_POST['request'])) {
        $request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    } else {
        throw new RuntimeException('No request was made!');
    }

    $response = new APIResponse();
    $ForbiddenShopManager = new ForbiddenShopManager($system, $player);

    switch ($request) {
        case "getEventData":
            $response->data = ForbiddenShopAPIPresenter::eventDataResponse();
            break;
        case "exchangeEventCurrency":
            $event_name = $system->db->clean($_POST['event_name']);
            $currency_name = $system->db->clean($_POST['currency_name']);
            $quantity = $system->db->clean($_POST['quantity']);
            $response->data = [
                'message' => ForbiddenShopAPIPresenter::exchangeEventCurrencyResponse($ForbiddenShopManager, $event_name, $currency_name, $quantity),
            ];
            break;
        case "exchangeForbiddenJutsuScroll":
            $item_type = $system->db->clean($_POST['item_type']);
            $item_id = $system->db->clean($_POST['item_id']);
            $response->data = [
                'message' => ForbiddenShopAPIPresenter::exchangeForbiddenJutsuScrollResponse($ForbiddenShopManager, $item_type, $item_id),
            ];
            break;
        default:
            API::exitWithError(message: "Invalid request!", system: $system);
    }

    API::exitWithData(
        data: $response->data,
        errors: $response->errors,
        debug_messages: $system->debug_messages,
        system: $system,
    );
} catch (Throwable $e) {
    API::exitWithException($e, system: $system);
}