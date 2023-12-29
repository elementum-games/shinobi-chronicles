<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = API::init(row_lock: true);

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch (RuntimeException $e) {
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
        case "exchangeAllEventCurrency":
            $event_key = $system->db->clean($_POST['event_key']);

            $message = $ForbiddenShopManager->exchangeAllEventCurrency($event_key);
            $player->getInventory();

            $response->data = [
                'message' => html_entity_decode($message, ENT_QUOTES),
                'playerInventory' => UserApiPresenter::playerInventoryResponse($player)
            ];
            break;
        case "buyForbiddenJutsu":
            $jutsu_id = $system->db->clean($_POST['jutsu_id']);

            $message = $ForbiddenShopManager->buyForbiddenJutsu($jutsu_id);
            $player->getInventory();

            $response->data = [
                'message' => html_entity_decode($message, ENT_QUOTES),
                'playerInventory' => UserApiPresenter::playerInventoryResponse($player)
            ];
            break;
        case "exchangeFavor":
            $item_id = $system->db->clean((int)$_POST['item_id']);

            $message = $ForbiddenShopManager->exchangeFavor($item_id);
            $player->getInventory();

            $response->data = [
                'message' => html_entity_decode($message, ENT_QUOTES),
                'playerInventory' => UserApiPresenter::playerInventoryResponse($player)
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