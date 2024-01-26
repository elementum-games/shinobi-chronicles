<?php

require_once __DIR__ . '/../ReportManager.php';
require_once __DIR__ . '/ChatPostDto.php';
require_once __DIR__ . '/../notification/BlockedNotificationManager.php';

class ChatManager {
    const MAX_POST_LENGTH = 300;
    const POST_LENGTH_BUFFER = 50;
    const MAX_POSTS_PER_PAGE = 15;

    public function __construct(
        public System $system,
        public User $player
    ) {}

    /**
     * @throws RuntimeException
     */
    public function loadPosts(?int $current_page_post_id = null): array {
        $query = "SELECT MAX(`post_id`) as `latest_post_id`, MIN(`post_id`) as `first_post_id` FROM `chat`";

        $result = $this->system->db->query($query);
        if($this->system->db->last_num_rows) {
            $bookend_posts = $this->system->db->fetch($result);
            $latest_post_id = $bookend_posts['latest_post_id'];
            $first_post_id = $bookend_posts['first_post_id'];
        }
        else {
            $latest_post_id = 0;
            $first_post_id = 0;
        }

        //Pagination
        if($current_page_post_id != null && $current_page_post_id < $latest_post_id) {
            $previous_page_post_id = min($current_page_post_id + self::MAX_POSTS_PER_PAGE, $latest_post_id);
            $next_page_post_id = $current_page_post_id - self::MAX_POSTS_PER_PAGE;
        }
        else {
            $previous_page_post_id = null;
            $next_page_post_id = max($latest_post_id - self::MAX_POSTS_PER_PAGE, 0);
        }

        if($next_page_post_id < $first_post_id) {
            $next_page_post_id = null;
        }

        //Set post query limit
        $posts = $this->fetchPosts($current_page_post_id, is_quote: false);

        return ChatAPIPresenter::loadPostsResponse(
            system: $this->system,
            posts: $posts,
            previous_page_post_id: $previous_page_post_id,
            next_page_post_id: $next_page_post_id,
            latest_post_id: $latest_post_id,
        );
    }

    /**
     * @param int|null $starting_post_id
     * @param int      $max_posts
     * @param bool     $is_quote
     * @return ChatPostDto[]
     */
    private function fetchPosts(?int $starting_post_id = null, int $max_posts = self::MAX_POSTS_PER_PAGE, bool $is_quote = false): array {
        if($starting_post_id != null) {
            $query = "SELECT * FROM `chat` WHERE `post_id` <= $starting_post_id AND `deleted`=0";
        }
        else {
            $query = "SELECT * FROM `chat` WHERE `deleted` = 0";
        }

        $blocked_user_ids = $this->player->blacklist->blockedUserIds(exclude_staff: true);
        if(count($blocked_user_ids) > 0) {
            $query .= " AND `user_id` NOT IN (" . implode(",", $blocked_user_ids) . ")";
        }

        $query .= " ORDER BY `post_id` DESC LIMIT $max_posts";

        $result = $this->system->db->query($query);
        $result = $this->system->db->fetch_all($result);

        // Get user IDs from posts
        $user_ids = [];
        $user_data_by_id = [];
        $user_data_by_name = [];
        foreach ($result as $row) {
            // If user_id is set (newer post) add to list for batch
            if ($row['user_id'] > 0) {
                if (!in_array($row['user_id'], $user_ids)) {
                    $user_ids[] = (int)$row['user_id'];
                }
            }
            // If user_id is not set (older post) get data for that user based on user_name
            else {
                $user_data_result = $this->system->db->query("
                    SELECT `users`.`user_id`, `users`.`staff_level`, `users`.`premium_credits_purchased`, `users`.`chat_effect`, `users`.`avatar_link`,
                    `user_settings`.`avatar_style`, `user_settings`.`avatar_frame`
                    FROM `users`
                    LEFT JOIN `user_settings` ON `users`.`user_id` = `user_settings`.`user_id`
                    WHERE `users`.`user_name` = '{$this->system->db->clean($row['user_name'])}'
                ");
                $user_data_result = $this->system->db->fetch($user_data_result);
                // only add data to array if user match found
                if ($this->system->db->last_num_rows > 0) {
                    // if custom setting is not set
                    if (!isset($user_data_result['avatar_style'])) {
                        $user_data_result['avatar_style'] = "avy_round";
                    }
                    // if custom setting is not set
                    if (!isset($user_data_result['avatar_frame'])) {
                        $user_data_result['avatar_frame'] = "avy_frame_default";
                    }
                    $user_data_by_name[$row['user_name']] = $user_data_result;
                }
            }
        }
        // Get batch user data
        if (count($user_ids) > 0) {
            $user_data_result = $this->system->db->query("
                SELECT `users`.`user_id`, `users`.`staff_level`, `users`.`premium_credits_purchased`, `users`.`chat_effect`, `users`.`avatar_link`,
                `user_settings`.`avatar_style`, `user_settings`.`avatar_frame`
                FROM `users`
                LEFT JOIN `user_settings` ON `users`.`user_id` = `user_settings`.`user_id`
                WHERE `users`.`user_id` IN (" . implode(', ', $user_ids) . ")
            ");
            $user_data_result = $this->system->db->fetch_all($user_data_result);
            foreach ($user_data_result as $row) {
                // if custom setting is not set
                if (!isset($row['avatar_style'])) {
                    $row['avatar_style'] = "avy_round";
                }
                // if custom setting is not set
                if (!isset($row['avatar_frame'])) {
                    $row['avatar_frame'] = "avy_frame_default";
                }
                $user_data_by_id[$row['user_id']] = $row;
            }
        }

        $posts = [];
        foreach ($result as $row) {
            $post = ChatPostDto::fromDb($row);

            //Base data
            $post->avatar = './images/default_avatar.png';

            //Fetch user data
            $user_data = null;
            if (isset($user_data_by_id[$post->user_id])) {
                $user_data = $user_data_by_id[$post->user_id];
            }
            else if (isset($user_data_by_name[$post->user_name])) {
                $user_data = $user_data_by_name[$post->user_name];
            }

            //Format posts
            $post->user_link_class_names = ["userLink"];
            if(!empty($user_data)) {
                if($user_data['premium_credits_purchased'] && $user_data['chat_effect'] == 'sparkles') {
                    $post->user_link_class_names[] = "premiumUser";
                }
                $post->user_link_class_names[] =
                $post->avatar = $user_data['avatar_link'];
                $post->avatar_style = $user_data['avatar_style'];
                $post->avatar_frame = $user_data['avatar_frame'];
            }

            $post->user_link_class_names[] = "chat";
            if(isset($post->user_color)) {
                $post->user_link_class_names[] = $post->user_color;
            }

            if($post->staff_level) {
                $post->staff_banner_name = $this->system->SC_STAFF_COLORS[$post->staff_level]['staffBanner'];
                $post->staff_banner_color = $this->system->SC_STAFF_COLORS[$post->staff_level]['staffColor'];
            }

            $time = time() - $post->time;
            if($time >= 86400) {
                $time_string = floor($time/86400) . " day(s) ago";
            }
            elseif($time >= 3600) {
                $time_string = floor($time/3600) . " hour(s) ago";
            }
            else {
                $mins = floor($time/60);
                if($mins < 1) {
                    $mins = 1;
                }
                $time_string = "$mins min(s) ago";
            }

            $post->time_string = $time_string;

            $post->message = $this->system->explicitLanguageReplace(
                text: $post->message,
                allow_non_banned_words: !$this->player->censor_explicit_language
            );
            $post->message = nl2br($this->system->html_parse($post->message, false, true));

            // Handle Mention
            $pattern = "/@([^\r\n\s@,<>:\[\]()]+[a-zA-Z0-9_-]|$)/";
            $has_mention = preg_match_all($pattern, $post->message, $matches);
            $mention_count = 0;
            if ($has_mention) {
                if (!$is_quote) {
                    foreach ($matches[1] as $match) {
                        $mention_count++;
                        // if at limit, stop
                        if ($mention_count > 3) {
                            break;
                        }
                        // only display formatted mention if user exists
                        if(!User::userExists($this->system, str_replace("\\", "", $match))) {
                            continue;
                        }
                        // format each mention
                        $formatted_mention = "<div class='mention_container'><a class='chat_user_name userLink' href='" . $this->system->router->getURL("members", ["user" => $match]) . "'>@" . $match . "</a></div>";
                        // replace first
                        $post->message = preg_replace('/@('.$match.')(?![a-zA-Z0-9-_])/', $formatted_mention, $post->message, 1);
                    }
                }
            }
            // Handle Quotes
            $pattern = "/\[quote:\d+\]/";
            $has_quote = preg_match_all($pattern, $post->message, $matches);
            $quote_count = 0;
            if ($has_quote) {
                if (!$is_quote) {
                    foreach ($matches[0] as $match) {
                        $quote_count++;
                        // if at limit, remove
                        if ($quote_count > 3) {
                            $post->message = str_replace($matches[0], "", $post->message);
                            break;
                        }
                        // get chat post contents
                        $pattern = "/\[quote:(\d+)\]/";
                        preg_match($pattern, $match, $id);
                        $quote = $this->fetchPosts($id[1], 1, true);
                        if ($quote) {
                            // if id match
                            if ($quote[0]->id == $id[1]) {
                                // format each entry in $quotes
                                $formatted_quote = "<div class='quote_container'><a class='chat_user_name " . implode(' ', $quote[0]->user_link_class_names) . "' href='" . $this->system->router->getURL("members", ["user" => $quote[0]->user_name]) . "'>" . $quote[0]->user_name . "</a><div class='quote_message chat_meme_small'>" . $quote[0]->message . "</div></div>";
                                // replace one instance
                                $post->message = preg_replace("/" . preg_quote($match, '/') . '/', $formatted_quote, $post->message, 1);
                            }
                            else {
                                // Just remove quote tags for deleted posts/blacklisted users
                                $post->message = str_replace($matches[0], "", $post->message);
                            }
                        }
                    }
                }
                else {
                    $post->message = str_replace($matches[0], "(...)", $post->message);
                }
            }

            $posts[] = $post;
        }

        return $posts;
    }

    public function submitPost(string $message) {
        $chat_max_post_length = $this->maxPostLength();

        $message_length = strlen(preg_replace('/(\\\\[rn])+/', '', trim($message)));

        try {
            $result = $this->system->db->query(
                "SELECT `message` FROM `chat`
                     WHERE `user_id` = '{$this->player->user_id}' ORDER BY  `post_id` DESC LIMIT 1"
            );
            if($this->system->db->last_num_rows) {
                $post = $this->system->db->fetch($result);
                if($post['message'] == $message) {
                    throw new RuntimeException("You cannot post the same message twice in a row!");
                }
            }
            if($message_length < 3) {
                throw new RuntimeException("Message is too short!");
            }
            // 50 character buffer
            if($message_length > $chat_max_post_length + self::POST_LENGTH_BUFFER) {
                throw new RuntimeException("Message is too long!");
            }
            //Failsafe, prevent posting if ban
            if($this->player->checkBan(StaffManager::BAN_TYPE_CHAT)) {
                throw new RuntimeException("You are currently banned from the chat!");
            }

            $title = $this->player->rank->name;
            $seat = $this->player->village_seat;
            if (!empty($seat->seat_title)) {
                $title = $seat->seat_title;
                if ($seat->seat_type == 'kage') {
                    $title = VillageManager::KAGE_NAMES[$this->player->village->village_id];
                }
            }
            $staff_level = $this->player->staff_level;
            $supported_colors = $this->player->getNameColors();

            $user_color = '';
            if (isset($supported_colors[$this->player->chat_color])) {
                $user_color = $supported_colors[$this->player->chat_color];
            } else {
                $user_color = 'normalUser';
            }

            $sql = "INSERT INTO `chat`
                    (`user_id`, `user_name`, `message`, `title`, `village`, `staff_level`, `user_color`, `time`, `edited`) VALUES
                           (%s, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";
            $this->system->db->query(
                sprintf(
                    $sql, $this->player->user_id, $this->player->user_name, $message, $title, $this->player->village->name, $staff_level, $user_color, time(), 0
                )
            );
            $new_post_id = $this->system->db->last_insert_id;
            if($this->system->db->last_affected_rows) {
                $this->system->message("Message posted!");
            }

            // Master notification array
            $notification_blocks = [];
            // Master bl array
            $master_bl = [];

            // Handle Quotes
            $pattern = "/\[quote:\d+\]/";
            $has_quote = preg_match_all($pattern, $message, $matches);
            $quote_count = 0;
            if ($has_quote) {
                foreach ($matches[0] as $match) {
                    $quote_count++;
                    if ($quote_count > 3) {
                        break;
                    }
                    $pattern = "/\[quote:(\d+)\]/";
                    preg_match($pattern, $match, $id);
                    $quote = $this->fetchPosts($id[1], 1, true);
                    if ($quote) {
                        if ($this->player->user_name == $quote[0]->user_name) {
                            continue;
                        }
                        $result = $this->system->db->query(
                            "SELECT `user_id`, `blocked_notifications` FROM `users` WHERE `user_name`='{$quote[0]->user_name}' LIMIT 1"
                        );
			            if($this->system->db->last_num_rows == 0) {
				            throw new RuntimeException("User does not exist!");
			            }
			            $result = $this->system->db->fetch($result);

                        // Load notification blocker
                        if(!isset($notification_blocks[$result['user_id']])) {
                            $notification_blocks[$result['user_id']] =
                                BlockedNotificationManager::fromDb(
                                    system: $this->system,
                                    blocked_notifications_string: $result['blocked_notifications']
                            );
                        }
                        // Notification blocked
                        if($notification_blocks[$result['user_id']]->notificationBlocked(NotificationManager::NOTIFICATION_CHAT)) {
                            continue;
                        }

                        // Blacklist user
                        if(!isset($master_bl[$result['user_id']])) {
                            $master_bl[$result['user_id']] = Blacklist::fromDb(system: $this->system, user_id: $result['user_id']);
                        }
                        if($master_bl[$result['user_id']]->userBlocked($this->player->user_id)) {
                            continue;
                        }

                        require_once __DIR__ . '/../notification/NotificationManager.php';
                        $new_notification = new ChatNotificationDto(
                            type: "chat",
                            message: $this->player->user_name . " replied to your post!",
                            user_id: $result['user_id'],
                            created: time(),
                            expires: time() + (NotificationManager::NOTIFICATION_EXPIRATION_DAYS_CHAT * 86400),
                            alert: false,
                            post_id: $new_post_id,
                        );
                        NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_MULTIPLE, limit: 3);
                    }
                }
            }

            // Handle Mention
            $pattern = "/@([^ \n\s!?.<>:@\[\]()]+)(?=[^A-Za-z0-9_]|$)/";
            $has_mention = preg_match_all($pattern, $message, $matches);

            $mentioned_users = [];

            if ($has_mention) {
                foreach ($matches[1] as $match) {
                    if ($this->player->user_name == $match) {
                        continue;
                    }
                    if(in_array(strtolower($match), $mentioned_users)) {
                        continue;
                    }
                    if(count($mentioned_users) >= 3) {
                        break;
                    }

                    $mentioned_users[] = $this->system->db->clean(strtolower($match));
                }

                foreach($mentioned_users as $mentioned_user) {
                    $result = $this->system->db->query(
                        "SELECT `user_id`, `blocked_notifications` FROM `users` WHERE `user_name`='{$mentioned_user}' LIMIT 1"
                    );
                    if ($this->system->db->last_num_rows == 0) {
                        throw new RuntimeException("User does not exist!");
                    }
                    $result = $this->system->db->fetch($result);

                    //Blocked notifs
                    if(!isset($notification_blocks[$result['user_id']])) {
                        $notification_blocks[$result['user_id']] =
                            BlockedNotificationManager::fromDb(
                                system: $this->system,
                                blocked_notifications_string: $result['blocked_notifications']
                            );
                    }

                    if($notification_blocks[$result['user_id']]->notificationBlocked(NotificationManager::NOTIFICATION_CHAT)) {
                        continue;
                    }

                    // Blacklist user
                    if(!isset($master_bl[$result['user_id']])) {
                        $master_bl[$result['user_id']] = Blacklist::fromDb(system: $this->system, user_id: $result['user_id']);
                    }
                    if($master_bl[$result['user_id']]->userBlocked($this->player->user_id)) {
                        continue;
                    }

                    require_once __DIR__ . '/../notification/NotificationManager.php';
                    $new_notification = new ChatNotificationDto(
                        type: "chat",
                        message: $this->player->user_name . " mentioned you in chat!",
                        user_id: $result['user_id'],
                        created: time(),
                        expires: time() + (NotificationManager::NOTIFICATION_EXPIRATION_DAYS_CHAT * 86400),
                        alert: false,
                        post_id: $new_post_id,
                    );
                    NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_MULTIPLE, limit: 3);
                }
            }

            $this->player->updateLastActive();
        } catch(RuntimeException $e) {
            $this->system->message($e->getMessage());
        }

        return ChatAPIPresenter::submitPostResponse(
            $this->system,
            $this->fetchPosts(0, is_quote: false)
        );
    }

    /**
     * @throws RuntimeException
     */
    public function deletePost(int $post_id): array {
        $this->system->db->query("UPDATE `chat` SET `deleted`=1 WHERE `post_id`=$post_id LIMIT 1");
        $this->player->staff_manager->staffLog(StaffManager::STAFF_LOG_MOD,
    "{$this->player->user_name}({$this->player->user_id}) deleted post_id: $post_id"
        );

        if($this->system->db->last_affected_rows == 0) {
            throw new RuntimeException("Error deleting post!");
        }

        return ChatAPIPresenter::deletePostResponse(
            $this->system,
            $this->fetchPosts(0, is_quote: false)

        );
    }

    public function maxPostLength(): int {
        $chat_max_post_length = ChatManager::MAX_POST_LENGTH;

        // Validate post and submit to DB
        //Increase chat length limit for seal users & staff members
        if($this->player->staff_level && $this->player->forbidden_seal->level == 0) {
            $chat_max_post_length = ForbiddenSeal::$benefits[ForbiddenSeal::$STAFF_SEAL_LEVEL]['chat_post_size'];
        }
        if($this->player->forbidden_seal->level != 0) {
            $chat_max_post_length = $this->player->forbidden_seal->chat_post_size;
        }

        //If user has seal or is of staff, give them their words
        $chat_max_post_length += $this->player->forbidden_seal ? 100 : 0;

        return $chat_max_post_length;
    }
}

