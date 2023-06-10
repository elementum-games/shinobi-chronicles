<?php

require_once __DIR__ . '/../ReportManager.php';
require_once __DIR__ . '/ChatPostDto.php';

class ChatManager {
    const MAX_POST_LENGTH = 350;
    const MAX_POSTS_PER_PAGE = 15;

    public function __construct(
        public System $system,
        public User $player
    ) {}

    /**
     * @throws Exception
     */
    public function loadPosts(?int $current_page_post_id = null): array {
        $result = $this->system->query("SELECT MAX(`post_id`) as `latest_post_id`, MIN(`post_id`) as `first_post_id` FROM `chat`");
        if($this->system->db_last_num_rows) {
            $bookend_posts = $this->system->db_fetch($result);
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
        $posts = $this->fetchPosts($current_page_post_id);

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
     * @return ChatPostDto[]
     */
    private function fetchPosts(?int $starting_post_id = null, int $max_posts = self::MAX_POSTS_PER_PAGE): array {
        if($starting_post_id != null) {
            $query = "SELECT * FROM `chat` WHERE `post_id` <= $starting_post_id ORDER BY `post_id` DESC LIMIT $max_posts";
        }
        else {
            $query = "SELECT * FROM `chat` ORDER BY `post_id` DESC LIMIT $max_posts";
        }
        $result = $this->system->query($query);

        $posts = [];
        while($row = $this->system->db_fetch($result)) {
            $post = ChatPostDto::fromDb($row);

            //Skip post if user blacklisted
            $blacklisted = false;
            foreach($this->player->blacklist as $id => $blacklist) {
                if($post->user_name == $blacklist[$id]['user_name']) {
                    $blacklisted = true;
                    break;
                }
            }

            //Base data
            $post->avatar = './images/default_avatar.png';

            //Fetch user data
            $user_data = false;
            $user_result = $this->system->query("SELECT `staff_level`, `premium_credits_purchased`, `chat_effect`, `avatar_link` FROM `users`
            WHERE `user_name` = '{$this->system->clean($post->user_name)}'");
            if($this->system->db_last_num_rows) {
                $user_data = $this->system->db_fetch($user_result);
                //If blacklisted block content, only if blacklisted user is not currently a staff member
                if($blacklisted && $user_data['staff_level'] == StaffManager::STAFF_NONE) {
                    continue;
                }
            }
            else {
                if($blacklisted) {
                    continue;
                }
            }

            //Format posts
            $post->user_link_class_names = ["userLink"];
            if($user_data) {
                if($user_data['premium_credits_purchased'] && $user_data['chat_effect'] == 'sparkles') {
                    $post->user_link_class_names[] = "premiumUser";
                }
                $post->user_link_class_names[] =
                $post->avatar = $user_data['avatar_link'];
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

            if($this->player->censor_explicit_language) {
                $post->message = $this->system->explicitLanguageReplace($post->message);
            }
            $post->message = nl2br($this->system->html_parse($post->message, false, true));

            $posts[] = $post;
        }

        return $posts;
    }

    public function submitPost(string $message) {
        $chat_max_post_length = $this->maxPostLength();

        $message_length = strlen(preg_replace('/[\\n\\r]+/', '', trim($message)));

        try {
            $result = $this->system->query("SELECT `message` FROM `chat` 
                 WHERE `user_name` = '{$this->player->user_name}' ORDER BY  `post_id` DESC LIMIT 1");
            if($this->system->db_last_num_rows) {
                $post = $this->system->db_fetch($result);
                if($post['message'] == $message) {
                    throw new Exception("You cannot post the same message twice in a row!");
                }
            }
            if($message_length < 3) {
                throw new Exception("Message is too short!");
            }
            if($message_length > $chat_max_post_length) {
                throw new Exception("Message is too long!");
            }
            //Failsafe, prevent posting if ban
            if($this->player->checkBan(StaffManager::BAN_TYPE_CHAT)) {
                throw new Exception("You are currently banned from the chat!");
            }

            $title = $this->player->rank->name;
            $staff_level = $this->player->staff_level;
            $supported_colors = $this->player->getNameColors();

            $user_color = '';
            if(isset($supported_colors[$this->player->chat_color])) {
                $user_color = $supported_colors[$this->player->chat_color];
            }

            $sql = "INSERT INTO `chat`
                    (`user_name`, `message`, `title`, `village`, `staff_level`, `user_color`, `time`, `edited`) VALUES
                           ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";
            $this->system->query(sprintf(
                $sql, $this->player->user_name, $message, $title, $this->player->village->name, $staff_level, $user_color, time(), 0
            ));
            if($this->system->db_last_affected_rows) {
                $this->system->message("Message posted!");
            }
        } catch(Exception $e) {
            $this->system->message($e->getMessage());
        }

        return ChatAPIPresenter::submitPostResponse(
            $this->system,
            $this->fetchPosts(0 )
        );
    }

    /**
     * @throws Exception
     */
    public function deletePost(int $post_id): array {
        $this->system->query("DELETE FROM `chat` WHERE `post_id` = $post_id LIMIT 1");

        if($this->system->db_last_affected_rows == 0) {
            throw new Exception("Error deleting post!");
        }

        return ChatAPIPresenter::deletePostResponse(
            $this->system,
            $this->fetchPosts(0 )

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

