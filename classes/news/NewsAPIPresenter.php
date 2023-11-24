<?php

class NewsApiPresenter {
    /**
     * @param NewsManager $newsManager
     * @param System      $system
     * @param int         $num_posts
     * @return array
     */
    public static function newsPostResponse(NewsManager $newsManager, System $system, int $num_posts = 8): array {
        return array_map(
            function (NewsPostDto $post) use ($system) {
                $message = $system->parseMarkdown(stripslashes($post->message), true);

                return [
                    'post_id' => $post->post_id,
                    'sender' => $post->sender,
                    'title' => $post->title,
                    'message' => $message,
                    'time' => $post->time,
                    'tags' => $post->tags,
                    'version' => $post->version,
                ];
            },
            $newsManager->getNewsPosts($num_posts)
        );
    }
}
