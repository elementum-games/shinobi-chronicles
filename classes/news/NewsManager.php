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
    public function saveNewsPost(int $post_id, string $title, string $version, string $content, bool $update, bool $bugfix, bool $event): bool
    {
        if ($this->player->isHeadAdmin()) {
            $tags = [];
            if ($update) {
                array_push($tags, "update");
            }
            if ($bugfix) {
                array_push($tags, "bugfix");
            }
            if ($event) {
                array_push($tags, "event");
            }
            $tags = json_encode($tags);
            $time = microtime();
            if ($post_id == 0) {
                $this->system->db->query("INSERT INTO `news_posts` (`sender`, `title`, `message`, `time`, `tags`, `version`)
                    VALUES ('{$this->player->user_name}', '{$title}', '{$content}', '{$time}', {$tags}, '{$version}')");
            } else {
                $this->system->db->query("UPDATE `news_posts` SET `title` = '{$title}', `message` = '{$content}', `tags` = '{$tags}', `version` = '{$version}' WHERE `post_id` = '{$post_id}'");
            }

            return $this->system->db->last_affected_rows;
        }
        return false;
    }
}