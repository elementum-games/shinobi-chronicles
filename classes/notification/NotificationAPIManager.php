<?php

require __DIR__ . '/NotificationDto.php';
require_once __DIR__ . "/../../classes.php";

class NotificationAPIManager {
    private System $system;
    private User $player;

    public function __construct(System $system, User $player ) {
        $this->system = $system;
        $this->player = $player;
    }

    /**
     * @return NotificationDto[]
     */
    public function getUserNotifications(): array
    {
        $notifications = [];

        //Staff check
        if ($this->player->staff_manager->isModerator()) {
            $reportManager = new ReportManager($this->system, $this->player, true);
        }
        //Used for PM checks
        $playerInbox = new InboxManager($this->system, $this->player);

        //Battle
        if ($this->player->battle_id > 0) {
            $this->result = $this->system->query(
                "SELECT `battle_type` FROM `battles` WHERE `battle_id`='{$this->player->battle_id}' LIMIT 1"
            );
            if ($this->system->db_last_num_rows == 0) {
                $player->battle_id = 0;
            } else {
                $result = $this->system->db_fetch($result);
                $link = null;
                switch ($result['battle_type']) {
                    case Battle::TYPE_AI_ARENA:
                        $link = $this->system->router->links['arena'];
                        break;
                    case Battle::TYPE_AI_MISSION:
                        $link = $this->system->router->links['mission'];
                        break;
                    case Battle::TYPE_AI_RANKUP:
                        $link = $this->system->router->links['rankup'];
                        break;
                    case Battle::TYPE_SPAR:
                        $link = $this->system->router->links['spar'];
                        break;
                    case Battle::TYPE_FIGHT:
                        $link = $this->system->router->links['battle'];
                        break;
                    /* case Battle::TYPE_CHALLENGE:
                    $link = $this->system->router->links['spar'];
                    break;*/
                }
                if ($link) {
                    $notifications[] = new NotificationDto($link, "Battle", "In battle!", critical: true);
                }
            }
        }
        //New PM
        if ($playerInbox->checkIfUnreadMessages() || $playerInbox->checkIfUnreadAlerts()) {
            $notifications[] = new NotificationDto($this->system->router->links['inbox'], "Inbox", "You have unread PM(s)");
        }
        //Official Warning
        if ($this->player->getOfficialWarnings(true)) {
            $notifications[] = new NotificationDto($this->system->router->links['settings'] . "&view=account", "Warning", 'Official Warning(s)!');
        }
        //New Report
        if ($this->player->staff_manager->isModerator() && $reportManager->getActiveReports(true)) {
            $notifications[] = new NotificationDto($this->system->router->links['report'] . "&page=view_all_reports", "Report", 'New Report(s)!');
        }
        //New spar
        if ($this->player->challenge) {
            $notifications[] = new NotificationDto($this->system->router->links['spar'], "Spar", "Challenged!");
        }
        //Team invite
        if ($this->player->team_invite) {
            $notifications[] = new NotificationDto("{$this->system->router->base_url}?id=24", "Team", "Invited to team!");
        }
        //Proposal
        if ($this->player->spouse < 0) {
            $notifications[] = new NotificationDto($this->system->router->links['marriage'], "Marriage", "Proposal received!");
        }
        //Student Applications
        if (SenseiManager::isSensei($this->player->user_id, $this->system)) {
            if (SenseiManager::hasApplications($this->player->user_id, $this->system)) {
                $notifications[] = new NotificationDto($this->system->router->links['villageHQ'] . "&view=sensei", "Student", "Application received!");
            }
        }
        //Ongoing Mission
        if ($this->player->mission_id != 0) {
            $notifications[] = new NotificationDto($this->system->router->links['mission'], "Mission", "Mission in progress!");
        }

        //Ongoing Training
        if ($this->player->special_mission != 0) {
            $notifications[] = new NotificationDto($this->system->router->links['specialmissions'], "Special Mission", "Special Mission in progress!");
        }

        //Ongoing Special
        if ($this->player->train_time > 0) {
            $notifications[] = new NotificationDto($this->system->router->links['training'], "Training", "Training time remaining: " . System::timeRemaining($this->player->train_time - time(), 'short', false, true));
        }

        return $notifications;
    }
}