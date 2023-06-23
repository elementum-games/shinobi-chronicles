<?php

require __DIR__ . '/NewsPostDto.php';

class NewsAPIManager {
    private System $system;
    private ?User $player;

    public function __construct(System $system, User $player = null) {
        $this->system = $system;
        $this->player = $player;
    }

    /**
     * @return NewsPostDto[]
     */
    public function getLatestPosts(int $max_posts = 8) : array {
        $return_arr = [];

        $result = $this->system->db->query("SELECT * FROM `news_posts` ORDER BY `post_id` DESC LIMIT $max_posts");

        while ($post = $this->system->db->fetch($result)) {
            $return_arr[] = NewsPostDto::fromDb($post);
        }

        return $return_arr;
    }
}