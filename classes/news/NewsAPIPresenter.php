<?php

class NewsApiPresenter {
    /**
     * @param NewsPostDto[] $newsPosts
     * @return array
     */
    public static function newsPostResponse(NewsAPIManager $newsManager): array
    {
        return array_map(
            function (NewsPostDto $post) {
                return [
                    'post_id' => $post->post_id,
                    'sender' => $post->sender,
                    'title' => $post->title,
                    'message' => $post->message,
                    'time' => $post->time,
                    'tags' => $post->tags,
                ];
            },
            $newsManager->getLatestPosts()
        );
    }
}
