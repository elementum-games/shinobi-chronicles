<?php

class ChatAPIPresenter {
    /**
     * @throws RuntimeException
     */
    public static function chatPostResponse(System $system, ChatPostDto $chatPost) {
        return [
            'id' => $chatPost->id,
            'userId' => $chatPost->user_id,
            'userName' => $chatPost->user_name,
            'message' => $chatPost->message,
            'userTitle' => $chatPost->title,
            'userVillage' => $chatPost->village,
            'avatarLink' => $chatPost->avatar,
            'avatarStyle' => $chatPost->avatar_style,
            'avatarFrame' => $chatPost->avatar_frame,
            'postTime' => $chatPost->time,
            'timeString' => $chatPost->time_string,
            'userProfileLink' => $system->router->getUrl('members', ['user' => $chatPost->user_name]),
            'reportLink' => $system->router->getUrl('report', [
                'report_type' => ReportManager::REPORT_TYPE_CHAT, 'content_id' => $chatPost->id
            ]),
            'staffBannerName' => $chatPost->staff_banner_name,
            'staffBannerColor' => $chatPost->staff_banner_color,
            'userLinkClassNames' => $chatPost->user_link_class_names,
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
     * @param int|null      $previous_page_post_id
     * @param mixed         $current_page_post_id
     * @param int|null      $next_page_post_id
     * @param int           $latest_post_id
     * @return array
     * @throws RuntimeException
     */
    public static function loadPostsResponse(
        System $system,
        array $posts,
        ?int $previous_page_post_id,
        ?int $next_page_post_id,
        ?int $latest_post_id
    ): array {
        return [
            'posts' => array_map(function(ChatPostDto $post) use($system) {
                return self::chatPostResponse($system, $post);
            }, $posts),
            'previousPagePostId' => $previous_page_post_id,
            'nextPagePostId' => $next_page_post_id,
            'latestPostId' => $latest_post_id,
        ];
    }

    /**
     * @throws RuntimeException
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
     * @throws RuntimeException
     */
    public static function deletePostResponse(System $system, array $posts) {
        return [
            'posts' => array_map(function(ChatPostDto $post) use($system) {
                return self::chatPostResponse($system, $post);
            }, $posts),
        ];
    }
}