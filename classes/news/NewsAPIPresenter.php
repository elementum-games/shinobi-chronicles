<?php

class NewsApiPresenter {
    /**
     * @param NewsPostDto[] $newsPosts
     * @return array
     */
    public static function newsPostResponse(NewsManager $newsManager, System $system): array
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
                ];
            },
            $newsManager->getLatestPosts()
        );
    }
}
