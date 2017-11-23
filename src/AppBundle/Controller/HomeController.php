<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;

class HomeController extends RestController
{
    /**
     * @Rest\View
     */
    public function getAction()
    {
        return [
            'forum' => [
                'categories' => $this->generateUrl('get_forum_categories'),
                'forums'     => $this->generateUrl('get_forum_forums'),
                'topics'     => $this->generateUrl('get_forum_topics'),
                'posts'      => $this->generateUrl('get_forum_posts')
            ],
            'news' => [
                'categories' => $this->generateUrl('get_article_categories'),
                'articles'   => $this->generateUrl('get_articles')
            ],
            'oauth' => $this->generateUrl('fos_oauth_server_token'),
            'users' => $this->generateUrl('get_users')
        ];
    }
}
