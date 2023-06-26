<?php

class NewsApiPresenter {
    /**
     * @param NewsPostDto[] $newsPosts
     * @return array
     */
    public static function newsPostResponse(NewsManager $newsManager, System $system, int $num_posts = 8): array
    {
        return array_map(
            function (NewsPostDto $post) use ($system) {
                $message = $post->message;
                $message = str_replace("\n", "<br />", $message);
                $message = wordwrap($system->html_parse(stripslashes($message), true), 90, "\n", true);
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
