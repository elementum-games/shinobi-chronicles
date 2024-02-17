<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = API::init(row_lock: true);

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch(RuntimeException $e) {
    API::exitWithException($e, system: $system);
}

try {
    // api requires a request
    if (isset($_POST['request'])) {
        $request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    } else {
        throw new RuntimeException('No request was made!');
    }

    $VillageAPIResponse = new VillageAPIResponse();

    switch($request) {
        case 'LoadResourceData':
            $days = $system->db->clean($_POST['days']);
            $VillageAPIResponse->response = VillageApiPresenter::resourceDataResponse($system, $player, $days);
            break;
        case 'ClaimSeat':
            $seat_type = $system->db->clean($_POST['seat_type']);
            $message = VillageManager::claimSeat($system, $player, $seat_type);
            $VillageAPIResponse->response = [
                'seatData' => VillageApiPresenter::seatDataResponse($system, $player),
                'playerSeat' => VillageManager::getPlayerSeat($system, $player),
                'response_message' => $message,
            ];
            break;
        case 'Resign':
            $message = VillageManager::resign($system, $player);
            $VillageAPIResponse->response = [
                'seatData' => VillageApiPresenter::seatDataResponse($system, $player),
                'playerSeat' => VillageManager::getPlayerSeat($system, $player),
                'response_message' => $message,
            ];
            break;
        case 'CreateProposal':
            $type = $system->db->clean($_POST['type']);
            switch ($type) {
                case "policy":
                    $policy_id = $system->db->clean($_POST['policy_id']);
                    $message = VillageManager::createPolicyProposal($system, $player, $policy_id);
                    break;
                case "declare_war":
                    $target_village_id = $system->db->clean($_POST['target_village_id']);
                    $message = VillageManager::createWarProposal($system, $player, $target_village_id);
                    break;
                case "offer_peace":
                    $target_village_id = $system->db->clean($_POST['target_village_id']);
                    $message = VillageManager::createPeaceProposal($system, $player, $target_village_id);
                    break;
                case "offer_alliance":
                    $target_village_id = $system->db->clean($_POST['target_village_id']);
                    $message = VillageManager::createAllianceProposal($system, $player, $target_village_id);
                    break;
                case "break_alliance":
                    $target_village_id = $system->db->clean($_POST['target_village_id']);
                    $message = VillageManager::createBreakAllianceProposal($system, $player, $target_village_id);
                    break;
                case "offer_trade":
                    $target_village_id = $system->db->clean($_POST['target_village_id']);
                    $offered_resources = $_POST['offered_resources'];
                    $offered_regions = isset($_POST['offered_regions']) ? $_POST['offered_regions'] : [];
                    $requested_resources = $_POST['requested_resources'];
                    $requested_regions = isset($_POST['requested_regions']) ? $_POST['requested_regions'] : [];
                    $message = VillageManager::createTradeProposal($system, $player, $target_village_id, $offered_resources, $offered_regions, $requested_resources, $requested_regions);
                    break;
                case "begin_construction":
                    $building_key = $system->db->clean($_POST['building_key']);
                    $message = VillageManager::createConstructionProposal($system, $player, $building_key);
                    break;
                case "cancel_construction":
                    $building_key = $system->db->clean($_POST['building_key']);
                    $message = VillageManager::createCancelConstructionProposal($system, $player, $building_key);
                    break;
                case "begin_research":
                    $upgrade_key = $system->db->clean($_POST['upgrade_key']);
                    $message = VillageManager::createResearchProposal($system, $player, $upgrade_key);
                    break;
                case "cancel_research":
                    $upgrade_key = $system->db->clean($_POST['upgrade_key']);
                    $message = VillageManager::createCancelResearchProposal($system, $player, $upgrade_key);
                    break;
                case "boost_construction":
                    $building_key = $system->db->clean($_POST['building_key']);
                    $message = VillageManager::createBoostConstructionProposal($system, $player, $building_key);
                    break;
                case "boost_research":
                    $upgrade_key = $system->db->clean($_POST['upgrade_key']);
                    $message = VillageManager::createBoostResearchProposal($system, $player, $upgrade_key);
                    break;
                default:
                    break;
            }
            $VillageAPIResponse->response = [
                'proposalData' => VillageApiPresenter::proposalDataResponse($system, $player),
                'response_message' => $message,
            ];
            break;
        case 'CancelProposal':
            $proposal_id = $system->db->clean($_POST['proposal_id']);
            $message = VillageManager::cancelProposal($system, $player, $proposal_id);
            $VillageAPIResponse->response = [
                'proposalData' => VillageApiPresenter::proposalDataResponse($system, $player),
                'response_message' => $message,
            ];
            break;
        case 'SubmitVote':
            $proposal_id = $system->db->clean($_POST['proposal_id']);
            $vote = $system->db->clean($_POST['vote']);
            $message = VillageManager::submitProposalVote($system, $player, $vote, $proposal_id);
            $VillageAPIResponse->response = [
                'proposalData' => VillageApiPresenter::proposalDataResponse($system, $player),
                'response_message' => $message,
            ];
            break;
        case 'CancelVote':
            $proposal_id = $system->db->clean($_POST['proposal_id']);
            $message = VillageManager::cancelProposalVote($system, $player, $proposal_id);
            $VillageAPIResponse->response = [
                'proposalData' => VillageApiPresenter::proposalDataResponse($system, $player),
                'response_message' => $message,
            ];
            break;
        case 'BoostVote':
            $proposal_id = $system->db->clean($_POST['proposal_id']);
            $message = VillageManager::boostProposalVote($system, $player, $proposal_id);
            $VillageAPIResponse->response = [
                'proposalData' => VillageApiPresenter::proposalDataResponse($system, $player),
                'response_message' => $message,
            ];
            break;
        case 'EnactProposal':
            $proposal_id = $system->db->clean($_POST['proposal_id']);
            $message = VillageManager::enactProposal($system, $player, $proposal_id);
            $VillageAPIResponse->response = [
                'proposalData' => VillageApiPresenter::proposalDataResponse($system, $player),
                'policyData' => VillageApiPresenter::policyDataResponse($system, $player),
                'strategicData' => VillageApiPresenter::strategicDataResponse($system),
                'buildingUpgradeData' => VillageApiPresenter::buildingUpgradeDataResponse($system, $player),
                'resourceData' => VillageApiPresenter::resourceDataResponse($system, $player, 1),
                'response_message' => $message,
            ];
            break;
        case 'SubmitChallenge':
            $seat_id = $system->db->clean($_POST['seat_id']);
            $selected_times = $_POST['selected_times'];
            $message = VillageManager::submitChallenge($system, $player, $seat_id, $selected_times);
            $VillageAPIResponse->response = [
                'response_message' => $message,
                'challengeData' => VillageManager::getChallengeData($system, $player),
            ];
            break;
        case 'AcceptChallenge':
            $challenge_id = $system->db->clean($_POST['challenge_id']);
            $time = $system->db->clean($_POST['time']);
            $message = VillageManager::AcceptChallenge($system, $player, $challenge_id, $time);
            $VillageAPIResponse->response = [
                'response_message' => $message,
                'challengeData' => VillageManager::getChallengeData($system, $player),
            ];
            break;
        case 'LockChallenge':
            $challenge_id = $system->db->clean($_POST['challenge_id']);
            $message = VillageManager::lockChallenge($system, $player, $challenge_id);
            $VillageAPIResponse->response = [
                'response_message' => $message,
                'challengeData' => VillageManager::getChallengeData($system, $player),
            ];
            break;
        case 'CancelChallenge':
            $message = VillageManager::cancelUserCreatedChallenges($system, $player->user_id);
            $VillageAPIResponse->response = [
                'response_message' => $message,
                'challengeData' => VillageManager::getChallengeData($system, $player),
            ];
            break;
        case 'GetGlobalWarLeaderboard':
            $page_number = (int)$_POST['page_number'];
            $VillageAPIResponse->response = [
                'warLogData' => VillageApiPresenter::playerWarLogDataResponse($system, $player, $page_number),
            ];
            break;
        case 'GetWarRecords':
            $page_number = (int)$_POST['page_number'];
            $VillageAPIResponse->response = [
                'warRecordData' => VillageApiPresenter::warRecordDataResponse($system, $player, $page_number),
            ];
            break;
        case 'ActivateUpgrade':
            $upgrade_key = $system->db->clean($_POST['upgrade_key']);
            $message = VillageUpgradeManager::activateUpgrade($system, $player->village, $upgrade_key);
            $VillageAPIResponse->response = [
                'buildingUpgradeData' => VillageApiPresenter::buildingUpgradeDataResponse($system, $player),
                'response_message' => $message,
            ];
            break;
        case 'CancelActivation':
            $upgrade_key = $system->db->clean($_POST['upgrade_key']);
            $message = VillageUpgradeManager::cancelActivation($system, $player->village, $upgrade_key);
            $VillageAPIResponse->response = [
                'buildingUpgradeData' => VillageApiPresenter::buildingUpgradeDataResponse($system, $player),
                'response_message' => $message,
            ];
            break;
        case 'DeactivateUpgrade':
            $upgrade_key = $system->db->clean($_POST['upgrade_key']);
            $message = VillageUpgradeManager::deactivateUpgrade($system, $player->village, $upgrade_key);
            $VillageAPIResponse->response = [
                'buildingUpgradeData' => VillageApiPresenter::buildingUpgradeDataResponse($system, $player),
                'response_message' => $message,
            ];
            break;
        case 'GetConstructionBoostCost':
            $building_key = $system->db->clean($_POST['building_key']);
            $message = VillageUpgradeManager::calcBoostConstructionCost($system, $player->village, $building_key);
            $VillageAPIResponse->response = [
                'response_message' => $message,
            ];
            break;
        case 'GetResearchBoostCost':
            $upgrade_key = $system->db->clean($_POST['upgrade_key']);
            $message = VillageUpgradeManager::calcBoostResearchCost($system, $player->village, $upgrade_key);
            $VillageAPIResponse->response = [
                'response_message' => $message,
            ];
            break;
        default:
            API::exitWithError(message: "Invalid request!", system: $system);
    }

    API::exitWithData(
        data: $VillageAPIResponse->response,
        errors: $VillageAPIResponse->errors,
        debug_messages: $system->debug_messages,
        system: $system,
    );
} catch (Throwable $e) {
    API::exitWithException($e, system: $system);
}

