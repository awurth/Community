<?php

namespace Tests\ForumBundle\Controller;

use ForumBundle\Entity\Post;
use ForumBundle\Entity\Topic;
use Tests\WebTestCase;
use UserBundle\Entity\User;

class PostControllerTest extends WebTestCase
{
    const RESOURCE_URI = '/forum/posts';

    /**
     * @var Topic
     */
    protected $topic;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            $this->getFixtureClass('Forum', 'Category'),
            $this->getFixtureClass('Forum', 'Forum'),
            $this->getFixtureClass('Forum', 'Topic'),
            $this->getFixtureClass('Forum', 'Post'),
            $this->getFixtureClass('User', 'User')
        ]);

        $this->topic = $this->findFirst('ForumBundle:Topic');
    }

    public function testGetPosts()
    {
        $response = $this->get($this->makeClient(), self::RESOURCE_URI);

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertJsonResourcesCount($response, 15);
    }

    public function testGetPost()
    {
        $post = $this->findFirst('ForumBundle:Post');

        $response = $this->get($this->makeClient(), self::RESOURCE_URI . '/' . $post->getId());

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson($post, $response->getContent());
    }

    public function testGetPostNotFound()
    {
        $response = $this->get($this->makeClient(), self::RESOURCE_URI . '/a');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testPostPost()
    {
        $topic = '{
            "forum_post": {
                "content": "This is a post",
                "topic": ' . $this->topic->getId() . '
            }
        }';

        $client = $this->makeClient();

        $token = $this->logIn($client, 'awurth', 'awurth');

        $response = $this->post($client, self::RESOURCE_URI, $topic, $token);

        // Test Response
        $this->assertIsCreated($response);
        $this->assertJsonResponse($response, false);

        // Test created Post
        $response = $this->get($client, $response->headers->get('Location'));

        $this->assertIsOk($response);
        $this->assertContains('"content":"This is a post"', $response->getContent());
    }

    public function testPostPostBadRole()
    {
        $response = $this->post($this->makeClient(), self::RESOURCE_URI, '');

        $this->assertIsUnauthorized($response);
    }

    public function testPostPostWithErrors()
    {
        $post = '{
            "forum_post": {
                "content": "",
                "topic": ""
            }
        }';

        $response = $this->post($this->createLoggedClient(), self::RESOURCE_URI, $post);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutPost()
    {
        $user = $this->em->getRepository('UserBundle:User')->findOneBy(['username' => 'awurth']);

        $postToUpdate = $this->createPost($this->topic, $user, 'New post');

        $jsonPost = '{
            "forum_post": {
                "content": "This is an updated post",
                "topic": ' . $this->topic->getId() . '
            }
        }';

        $client = $this->makeClient();

        $token = $this->logIn($client, 'awurth', 'awurth');

        $response = $this->put($client, self::RESOURCE_URI . '/' . $postToUpdate->getId(), $jsonPost, $token);

        // Test Response
        $this->assertIsNoContent($response);

        // Test updated Post
        $this->em->clear();
        $updatedPost = $this->em->getRepository('ForumBundle:Post')->find($postToUpdate->getId());

        $this->assertSame('This is an updated post', $updatedPost->getContent());
        $this->assertNotNull($updatedPost->getUpdatedAt());
    }

    public function testPutPostBadRole()
    {
        $post = $this->findFirst('ForumBundle:Post');

        // Not logged in
        $response = $this->put($this->makeClient(), self::RESOURCE_URI . '/' . $post->getId(), '');

        $this->assertIsUnauthorized($response);
        $this->assertJsonResponse($response);

        // Wrong user
        $response = $this->put($this->createLoggedClient(), self::RESOURCE_URI . '/' . $post->getId(), '');

        $this->assertIsForbidden($response);
        $this->assertJsonResponse($response);
    }

    public function testPutPostWithErrors()
    {
        $post = '{
            "forum_post": {
                "content": "",
                "topic": ""
            }
        }';

        $postToUpdate = $this->findFirst('ForumBundle:Post');

        $client = $this->makeClient();

        $token = $this->logIn($client, 'awurth', 'awurth');

        $response = $this->put($client, self::RESOURCE_URI . '/' . $postToUpdate->getId(), $post, $token);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutPostNotFound()
    {
        $response = $this->put($this->createLoggedClient(), self::RESOURCE_URI . '/a', '');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testDeletePost()
    {
        $postToDelete = $this->findFirst('ForumBundle:Post');

        $client = $this->makeClient();

        $token = $this->logIn($client, 'awurth', 'awurth');

        $response = $this->delete($client, self::RESOURCE_URI . '/' . $postToDelete->getId(), $token);

        // Test Response
        $this->assertIsNoContent($response);

        // Test deleted Post
        $this->em->clear();
        $deletedPost = $this->em->getRepository('ForumBundle:Post')->find($postToDelete->getId());

        $this->assertNull($deletedPost);
    }

    public function testDeletePostBadRole()
    {
        $post = $this->findFirst('ForumBundle:Post');

        // Not logged in
        $response = $this->delete($this->makeClient(), self::RESOURCE_URI . '/' . $post->getId());

        $this->assertIsUnauthorized($response);
        $this->assertJsonResponse($response);

        // Wrong user
        $response = $this->delete($this->createLoggedClient(), self::RESOURCE_URI . '/' . $post->getId());

        $this->assertIsForbidden($response);
        $this->assertJsonResponse($response);
    }

    public function testDeletePostNotFound()
    {
        $response = $this->delete($this->createLoggedClient(), self::RESOURCE_URI . '/a');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
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
}
