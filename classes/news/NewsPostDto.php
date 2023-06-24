<?php

class NewsPostDto {
    public function __construct(
        public int $post_id = 0,
        public string $sender = "",
        public string $title = "",
        public string $message = "",
        public int $time = 0,
        public array $tags = [],
    ) {}

    public static function fromDb($row): NewsPostDto
    {
        $post = new NewsPostDto();
        $post->post_id = $row['post_id'];
        $post->sender = $row['sender'];
        $post->title = $row['title'];
        $post->message = $row['message'];
        $post->time = $row['time'];
        // $post->tags = json_decode($row['tags'], true);

        return $post;
    }
}