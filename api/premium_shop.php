<?php

require_once __DIR__ . "/../classes.php";

$system = API::init(row_lock: true);

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch(RuntimeException $e) {
    API::exitWithException($e, system: $system);
}

try {
    if(isset($_POST['request'])) {
        $request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
    else {
        throw new RuntimeException("No request made!");
    }

    $PremiumAPIResponse = new PremiumAPIResponse();

    switch($request) {
        case 'LoadCosts':
            $PremiumAPIResponse->data = [
                'costs' => PremiumAPIPresenter::getCosts($player),
            ];
            break;
        default:
            API::exitWithError(message: "Invalid request!", system: $system);
    }

    API::exitWithData(
        data: $PremiumAPIResponse->data,
        errors: $PremiumAPIResponse->errors,
        debug_messages: $system->debug_messages,
        system: $system
    );

} catch (Throwable $e) {
    API::exitWithException($e, system: $system);
}