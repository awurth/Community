<?php

namespace Tests\ForumBundle\Controller;

use ForumBundle\Entity\Category;
use ForumBundle\Entity\Forum;
use ForumBundle\Entity\Post;
use ForumBundle\Entity\Topic;
use Tests\WebTestCase;
use UserBundle\Entity\User;

class PostController extends WebTestCase
{
    /**
     * @var Topic
     */
    protected $topic;

    /**
     * @var User
     */
    protected $author;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->emptyTable('forum_post');
        $this->emptyTable('forum_topic');
        $this->emptyTable('forum_forum');
        $this->emptyTable('forum_category');
        $this->emptyTable('user');

        $category = $this->createCategory();
        $forum = $this->createForum($category);
        $this->topic = $this->createTopic($forum);
        $this->author = new User();
    }

    public function testGetPosts()
    {
        $client = static::createClient();

        $client->request('GET', '/forum/posts');
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson([], $response->getContent());
    }

    public function testGetPostNotFound()
    {
        $client = static::createClient();

        $client->request('GET', '/forum/posts/1');
        $response = $client->getResponse();

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testGetPost()
    {
        $post = $this->createPost($this->topic, $this->author);

        $client = static::createClient();

        $client->request('GET', '/forum/posts/' . $post->getId());
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson($post, $response->getContent());
    }

    public function testPostPost()
    {
        $content = '{
            "forum_post": {
                "content": "This is a post",
                "topic": ' . $this->topic->getId() . '
            }
        }';

        $client = static::createClient();

        $response = $this->post($client, '/forum/posts', $content);

        // Test Response
        $this->assertIsCreated($response);
        $this->assertJsonResponse($response, false);

        // Test created Post
        $client->request('GET', $response->headers->get('Location'));
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertContains('"content":"This is a post"', $response->getContent());
    }

    /**
     * @dataProvider wrongPostProvider
     */
    public function testPostPostWithErrors($post)
    {
        $client = static::createClient();

        $response = $this->post($client, '/forum/posts', $post);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutPost()
    {
        $client = static::createClient();

        $postToUpdate = $this->createPost($this->topic, $this->author, 'New post');

        $createdPost = $this->em->getRepository('ForumBundle:Post')->find($postToUpdate->getId());
        $this->assertSame('New post', $createdPost->getContent());
        $this->assertNull($createdPost->getUpdatedAt());

        $updatedPost = '{
            "forum_post": {
                "content": "This is an updated post",
                "topic": ' . $this->topic->getId() . '
            }
        }';

        $response = $this->put($client, '/forum/posts/' . $postToUpdate->getId(), $updatedPost);

        // Test Response
        $this->assertIsNoContent($response);

        // Test updated Post
        $this->em->clear();
        $updatedPost = $this->em->getRepository('ForumBundle:Post')->find($postToUpdate->getId());

        $this->assertSame('This is an updated post', $updatedPost->getContent());
        $this->assertNotNull($updatedPost->getUpdatedAt());
    }

    /**
     * @dataProvider wrongPostProvider
     */
    public function testPutPostWithErrors($post)
    {
        $client = static::createClient();

        $postToUpdate = $this->createPost($this->topic, $this->author);

        $response = $this->put($client, '/forum/posts/' . $postToUpdate->getId(), $post);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutPostNotFound()
    {
        $client = static::createClient();

        $response = $this->put($client, '/forum/posts/1', '');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testDeletePost()
    {
        $client = static::createClient();

        $postToDelete = $this->createPost($this->topic, $this->author, 'Post to delete');

        $createdPost = $this->em->getRepository('ForumBundle:Post')->find($postToDelete->getId());
        $this->assertSame('Post to delete', $createdPost->getContent());
        $this->assertNull($createdPost->getUpdatedAt());

        $client->request('DELETE', '/forum/posts/' . $postToDelete->getId());
        $response = $client->getResponse();

        // Test Response
        $this->assertIsNoContent($response);

        // Test deleted Post
        $this->em->clear();
        $deletedPost = $this->em->getRepository('ForumBundle:Post')->find($postToDelete->getId());

        $this->assertNull($deletedPost);
    }

    public function testDeletePostNotFound()
    {
        $client = static::createClient();

        $client->request('DELETE', '/forum/posts/1');
        $response = $client->getResponse();

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    /**
     * Creates a new Category.
     *
     * @param string $title
     * @param string $description
     *
     * @return Category
     */
    public function createCategory($title = null, $description = null)
    {
        $category = new Category();
        $category->setTitle($title ? $title : 'Category title');
        $category->setDescription($description ? $description : 'This is a category');

        $this->em->persist($category);
        $this->em->flush();

        return $category;
    }

    /**
     * Creates a new Forum.
     *
     * @param Category $category
     * @param string   $title
     * @param string   $description
     *
     * @return Forum
     */
    public function createForum(Category $category, $title = null, $description = null)
    {
        $forum = new Forum();
        $forum->setTitle($title ? $title : 'Forum title');
        $forum->setDescription($description ? $description : 'This is a forum');
        $forum->setCategory($category);

        $this->em->persist($forum);
        $this->em->flush();

        return $forum;
    }

    /**
     * Creates a new Topic.
     *
     * @param Forum  $forum
     * @param string $title
     * @param string $description
     *
     * @return Topic
     */
    public function createTopic(Forum $forum, $title = null, $description = null)
    {
        $topic = new Topic();
        $topic->setTitle($title ? $title : 'Topic title');
        $topic->setDescription($description ? $description : 'This is a topic');
        $topic->setForum($forum);

        $this->em->persist($topic);
        $this->em->flush();

        return $topic;
    }

    /**
     * Creates a new Post.
     *
     * @param Topic  $topic
     * @param User   $author
     * @param string $content
     *
     * @return Post
     */
    public function createPost(Topic $topic, User $author, $content = null)
    {
        $post = new Post();
        $post->setContent($content ? $content : 'This is a post');
        $post->setTopic($topic);
        $post->setAuthor($author);

        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    public function wrongPostProvider()
    {
        $post = '{
            "forum_post": {
                "title": "%s",
                "description": "%s",
                "topic": %s
            }
        }';

        return [
            [sprintf($post, '', 'aaaa', 50)],
            [sprintf($post, 'aaaa', '', 50)],
            [sprintf($post, 'aaaa', 'aaaa', 50)]
        ];
    }
}
