<?php

class InboxUser extends Inbox {
    
    private $system;
    private $user;

    /**
     * @param System $system
     * @param User $user
     */
    public function __construct($system, $user) {
        $this->system = $system;
        $this->user = $user;
    }

    /**
     * @param none
     * @return int sql COUNT();
     */
    public function checkIfUnreadMessages() {
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
    public function checkIfUnreadAlerts() {
        $sql = "SELECT COUNT(`alert_id`) FROM `convos_alerts` WHERE `target_id`='{$this->user->user_id}' AND `unread`=1 AND `alert_deleted`=0";
        $result = $this->system->query($sql);
        $count = $result->fetch_row();
        return $count[0];
    }
    
    /**
     * @param int $convo_id
     * @param int $owner_id
     * @return int db_last_affected_rows
     */
    public function leaveConversation($convo_id, $owner_id, $convo_members) {
        // if the convo is just the player and 1 user
        // remove the other player and mark the convo as deleted
        if (count($convo_members) === 2) {
            foreach($convo_members as $member) {
                if (!$this->removePlayerFromConvo($convo_id, $member['user_id'])) {
                    return false;
                }
            }
            // mark the convo as deleted
            return $this->deleteConvo($convo_id);
        // if the user is the owner
        // remove all other members as well
        } else if ($owner_id == $this->user->user_id) {
            foreach($convo_members as $member) {
                if (!$this->removePlayerFromConvo($convo_id, $member['user_id'])) {
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

    /**
     * @param int $convo_id
     * @return array $convo_data
     */
    public function getConversation($convo_id) {
        $sql = "SELECT * FROM `convos` WHERE `convo_id`='{$convo_id}' AND `active`=1";
        $result = $this->system->query($sql);
        $convo_data = $this->system->db_fetch($result);

        // get all members
        $convo_data['convo_members'] = $this->getConvoMembers($this->system, $convo_id);
        $convo_data['self'] = $this->user->user_id;

        // send the max character limit
        $convo_data['max_characters'] = $this->checkMaxMessageLength($this->user->forbidden_seal, $this->user->staff_level);

        // get all messages
        $convo_data['all_messages'] = $this->getMessages($this->system, $this->user, $convo_id);

        return $convo_data;
    }
}