<?php

require __DIR__ . '/NewsPostDto.php';

class NewsManager {
    private System $system;
    private ?User $player;

    public function __construct(System $system, User $player = null) {
        $this->system = $system;
        $this->player = $player;
    }

    /**
     * @return NewsPostDto[]
     */
    public function getNewsPosts(int $max_posts = 8) : array {
        $return_arr = [];

        $result = $this->system->db->query("SELECT * FROM `news_posts` ORDER BY `post_id` DESC LIMIT $max_posts");

        while ($post = $this->system->db->fetch($result)) {
            $return_arr[] = NewsPostDto::fromDb($post);
        }

        return $return_arr;
    }

    /**
     * @return bool
     */
    public function saveNewsPost(NewsPostDto $newsPost): bool
    {
        if ($this->player->isHeadAdmin()) {
            $tags = json_encode($newsPost->tags);
            $time = time();
            if ($newsPost->post_id == 0) {
                $this->system->db->query("INSERT INTO `news_posts` (`sender`, `title`, `message`, `time`, `tags`, `version`)
                    VALUES ('{$this->player->user_name}', '{$newsPost->title}', '{$newsPost->message}', '{$time}', '{$tags}', '{$newsPost->version}')");
            } else {
                $this->system->db->query("UPDATE `news_posts` SET `title` = '{$newsPost->title}', `message` = '{$newsPost->message}', `tags` = '{$tags}', `version` = '{$newsPost->version}' WHERE `post_id` = '{$newsPost->post_id}'");
            }

            return $this->system->db->last_affected_rows;
        }
        return false;
    }
}