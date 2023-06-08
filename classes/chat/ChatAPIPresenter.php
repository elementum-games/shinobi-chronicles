<?php

class ChatAPIPresenter {
    /**
     * @throws Exception
     */
    public static function chatPostResponse(System $system, ChatPostDto $chatPost) {
        return [
            'id' => $chatPost->id,
            'userName' => $chatPost->user_name,
            'message' => $chatPost->message,
            'userTitle' => $chatPost->title,
            'userVillage' => $chatPost->village,
            'avatarLink' => $chatPost->avatar,
            'postTime' => $chatPost->time,
            'timeString' => $chatPost->time_string,
            'userProfileLink' => $system->router->getUrl('members', ['user' => $chatPost->user_name]),
            'reportLink' => $system->router->getUrl('report', [
                'report_type' => ReportManager::REPORT_TYPE_CHAT, 'content_id' => $chatPost->id
            ]),
            'staffBannerName' => $chatPost->staff_banner_name,
            'staffBannerColor' => $chatPost->staff_banner_color,
        ];
    }

    public static function banInfoResponse(System $system, User $player) {
        $ban_type = StaffManager::BAN_TYPE_CHAT;
        if(empty($player->ban_data[$ban_type])) {
            return [
                'isBanned' => false,
                'banName' => "",
                'banDescription' => "",
                'banTimeRemaining' => "",
            ];
        }

        $expire_int = $player->ban_data[$ban_type];
        $time_remaining = ($expire_int == StaffManager::PERM_BAN_VALUE
            ? $expire_int
            : $system->time_remaining($player->ban_data[StaffManager::BAN_TYPE_CHAT] - time())
        );

        return [
            'isBanned' => true,
            'banName' => "Chat Ban",
            'banDescription' => $time_remaining == StaffManager::PERM_BAN_VALUE
                ? "You are indefinitely banned from the chat."
                : "You are currently banned from the chat.",
            'banTimeRemaining' => max($time_remaining, 0),
        ];
    }

    /**
     * @param System        $system
     * @param ChatPostDto[] $posts
     * @param int|null      $previous_page_index
     * @param mixed         $current_page_index
     * @param int|null      $next_page_index
     * @param int           $max_post_index
     * @return array
     * @throws Exception
     */
    public static function loadPostsResponse(
        System $system,
        array $posts,
        ?int $previous_page_index,
        int $current_page_index,
        ?int $next_page_index,
        int $max_post_index
    ): array {
        return [
            'posts' => array_map(function(ChatPostDto $post) use($system) {
                return self::chatPostResponse($system, $post);
            }, $posts),
            'previousPageIndex' => $previous_page_index,
            'currentPageIndex' => $current_page_index,
            'nextPageIndex' => $next_page_index,
            'maxPostIndex' => $max_post_index,
        ];
    }

    /**
     * @throws Exception
     */
    public static function submitPostResponse(System $system, array $posts) {
        return [
            'posts' => array_map(function(ChatPostDto $post) use($system) {
                return self::chatPostResponse($system, $post);
            }, $posts),
            'previousPageIndex' => 0,
            'currentPageIndex' => 0
        ];
    }

    /**
     * @throws Exception
     */
    public static function deletePostResponse(System $system, array $posts) {
        return [
            'posts' => array_map(function(ChatPostDto $post) use($system) {
                return self::chatPostResponse($system, $post);
            }, $posts),
        ];
    }
}