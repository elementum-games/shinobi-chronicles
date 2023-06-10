<?php

class ChatPostDto {
    public function __construct(
        public int $id,
        public string $user_name,
        public string $message,
        public string $title,
        public string $village,
        public int $time,
        public int $staff_level,
        public string $user_color,
        public string $avatar = "",
        public string $status_type = "",
        public array $user_link_class_names = [],
        public string $staff_banner_name = "",
        public string $staff_banner_color = "",
        public string $time_string = ""
    ) {}

    public static function fromDb(array $post_data): ChatPostDto {
        return new ChatPostDto(
            id: $post_data['post_id'],
            user_name: $post_data['user_name'],
            message: $post_data['message'],
            title: $post_data['title'],
            village: $post_data['village'],
            time: $post_data['time'],
            staff_level: $post_data['staff_level'],
            user_color: $post_data['user_color'],
        );
    }
}