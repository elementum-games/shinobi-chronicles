<?php

require_once __DIR__ . '/Notification.php';

class Notifications {
    /**
     * @param System $system
     * @param User   $player
     * @return Notification[]
     */
    public static function getNotifications(System $system, User $player): array {
        /** @var Notification[] $notifications */
        $notifications = [];

        //Staff check
        if($player->staff_manager->isModerator()) {
            require_once 'classes/ReportManager.php';
            $reportManager = new ReportManager($system, $player, true);
        }
        //Used for PM checks
        $playerInbox = new InboxManager($system, $player);

        //Battle
        if($player->battle_id > 0) {
            $result = $system->db->query(
                "SELECT `battle_type` FROM `battles` WHERE `battle_id`='$player->battle_id' LIMIT 1"
            );
            if($system->db->last_num_rows == 0) {
                $player->battle_id = 0;
            }
            else {
                $result = $system->db->fetch($result);
                $link = null;
                switch($result['battle_type']) {
                    case Battle::TYPE_AI_ARENA:
                        $link = $system->router->links['arena'];
                        break;
                    case Battle::TYPE_AI_MISSION:
                        $link = $system->router->links['mission'];
                        break;
                    case Battle::TYPE_AI_RANKUP:
                        $link = $system->router->links['rankup'];
                        break;
                    case Battle::TYPE_SPAR:
                        $link = $system->router->links['spar'];
                        break;
                    case Battle::TYPE_FIGHT:
                        $link = $system->router->links['battle'];
                        break;
                    case Battle::TYPE_CHALLENGE:
                        $link = $system->router->links['challenge'];
                        break;
                    case Battle::TYPE_AI_WAR:
                        $link = $system->router->links['war'];
                        break;
                }
                if($link) {
                    $notifications[] = new Notification($link, "In battle!", critical: true);
                }
            }
        }
        //New PM
        if($playerInbox->checkIfUnreadMessages() || $playerInbox->checkIfUnreadAlerts()) {
            $notifications[] = new Notification($system->router->links['inbox'], "You have unread PM(s)");
        }
        //Official Warning
        if($player->getOfficialWarnings(true)) {
            $notifications[] = new Notification($system->router->getUrl('account_record'), 'Official Warning(s)!');
        }
        //New Report
        if($player->staff_manager->isModerator() && $reportManager->getActiveReports(true)) {
            $notifications[] = new Notification($system->router->links['report'] . "&page=view_all_reports", 'New Report(s)!');
        }
        //New spar
        if($player->challenge) {
            $notifications[] = new Notification($system->router->links['spar'], "Challenged!");
        }
        //Team invite
        if($player->team_invite) {
            $notifications[] = new Notification("{$system->router->base_url}?id=24", "Invited to team!");
        }
        //Proposal
        if($player->spouse < 0) {
            $notifications[] = new Notification($system->router->links['marriage'], "Proposal received!");
        }
        //Student Applications
        if(SenseiManager::isActiveSensei($player->user_id, $system)) {
            if (SenseiManager::hasApplications($player->user_id, $system)) {
                $notifications[] = new Notification($system->router->links['villageHQ'] . "&view=sensei", "Application received!");
            }
        }

        return $notifications;
    }
}