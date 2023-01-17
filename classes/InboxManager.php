<?php

require_once __DIR__ . '/InboxUser.php';

class InboxManager {
    private System $system;
    private User $user;

    /**
     * @param System $system
     * @param User   $user
     */
    public function __construct(System $system, User $user) {
        $this->system = $system;
        $this->user = $user;
    }

    /**
     * @param
     * @return int sql COUNT();
     */
    public function checkIfUnreadMessages(): int {
        $sql = "SELECT COUNT(`convos_users`.`entry_id`)
                FROM `convos_users`
                INNER JOIN `convos_messages`
                ON `convos_users`.`convo_id`=`convos_messages`.`convo_id`
                WHERE `convos_users`.`last_read`<`convos_messages`.`time`
                AND `convos_users`.`user_id`='{$this->user->user_id}'
                ORDER BY `convos_messages`.`time` DESC";
        $result = $this->system->query($sql);
        $count = $result->fetch_row();
        return $count[0];
    }

    /**
     * @return int COUNT
     */
    public function checkIfUnreadAlerts(): int {
        $sql = "SELECT COUNT(`alert_id`) FROM `convos_alerts` WHERE `target_id`='{$this->user->user_id}' AND `unread`=1 AND `alert_deleted`=0";
        $result = $this->system->query($sql);
        $count = $result->fetch_row();
        return $count[0];
    }

    /**
     * @param int $convo_id
     * @param int $owner_id
     * @param InboxUser[] $convo_members
     * @return bool|int db_last_affected_rows
     */
    public function leaveConversation(int $convo_id, int $owner_id, $convo_members): bool|int {
        // if the convo is just the player and 1 user
        // remove the other player and mark the convo as deleted
        if (count($convo_members) === 2) {
            foreach($convo_members as $member) {
                if (!$this->removePlayerFromConvo($convo_id, $member->user_id)) {
                    return false;
                }
            }
            // mark the convo as deleted
            return $this->deleteConvo($convo_id);
        // if the user is the owner
        // remove all other members as well
        } else if ($owner_id == $this->user->user_id) {
            foreach($convo_members as $member) {
                if (!$this->removePlayerFromConvo($convo_id, $member->user_id)) {
                    return false;
                }
            }
            // mark the convo as deleted
            return $this->deleteConvo($convo_id);
        }

        // remove the player from the conversation
        if (!$this->removePlayerFromConvo($convo_id, $this->user->user_id)) {
            return false;
        }

        return true;
    }

    public function deleteConvo(int $convo_id): int {
        return Inbox::deleteConvo($this->system, $convo_id);
    }

    /**
     * @param int $convo_id
     * @return array $convo_data
     */
    public function getConversation(int $convo_id): array {
        $sql = "SELECT * FROM `convos` WHERE `convo_id`='{$convo_id}' AND `active`=1";
        $result = $this->system->query($sql);
        $convo_data = $this->system->db_fetch($result);

        // get all members
        $convo_data['convo_members'] = $this->getConvoMembers($convo_id);
        $convo_data['self'] = $this->user->user_id;
        $convo_data['profile_link'] = $this->system->links['members'] . '&user=';
        $convo_data['report_link'] = $this->system->links['report'] . '&report_type=2&content_id=';

        // send the max character limit
        $convo_data['max_characters'] = $this->getMaxMessageLength();

        // get all messages
        $convo_data['all_messages'] = $this->getMessages($convo_id);

        return $convo_data;
    }

    public function getConvoMembers(int $convo_id): bool|array {
        return Inbox::getConvoMembers($this->system, $convo_id);
    }

    public function getMaxMessageLength(): int {
        return Inbox::checkMaxMessageLength($this->user->forbidden_seal, $this->user->staff_level);
    }

    public function getMessages(int $convo_id): bool|array {
        return Inbox::getMessages($this->system, $this->user, $convo_id);
    }

    public function removePlayerFromConvo(int $convo_id, int $user_id): bool {
        return Inbox::removePlayerFromConvo($this->system, $convo_id, $user_id);
    }
}