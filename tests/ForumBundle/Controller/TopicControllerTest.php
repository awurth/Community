<?php

namespace Tests\ForumBundle\Controller;

use ForumBundle\Entity\Category;
use ForumBundle\Entity\Forum;
use ForumBundle\Entity\Topic;
use Tests\WebTestCase;

class TopicControllerTest extends WebTestCase
{
    /**
     * @var Forum
     */
    protected $forum;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->emptyTable('forum_topic');
        $this->emptyTable('forum_forum');
        $this->emptyTable('forum_category');

        $category = $this->createCategory();
        $this->forum = $this->createForum($category, 'First forum', 'This is the first forum');
    }

    public function testGetTopics()
    {
        $client = static::createClient();

        $client->request('GET', '/forum/topics');
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson([], $response->getContent());
    }

    public function testGetTopicNotFound()
    {
        $client = static::createClient();

        $client->request('GET', '/forum/topics/1');
        $response = $client->getResponse();

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testGetTopic()
    {
        $topic = $this->createTopic($this->forum);

        $client = static::createClient();

        $client->request('GET', '/forum/topics/' . $topic->getId());
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson($topic, $response->getContent());
    }

    public function testPostTopic()
    {
        $content = '{
            "forum_topic": {
                "title": "Topic title",
                "description": "This is a topic",
                "forum": ' . $this->forum->getId() . '
            }
        }';

        $client = static::createClient();

        $response = $this->post($client, '/forum/topics', $content);

        // Test Response
        $this->assertIsCreated($response);
        $this->assertJsonResponse($response, false);

        // Test created Topic
        $client->request('GET', $response->headers->get('Location'));
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertContains('"title":"Topic title"', $response->getContent());
    }

    /**
     * @dataProvider wrongTopicProvider
     */
    public function testPostTopicWithErrors($topic)
    {
        $client = static::createClient();

        $response = $this->post($client, '/forum/topics', $topic);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutTopic()
    {
        $client = static::createClient();

        $topicToUpdate = $this->createTopic($this->forum, 'New topic');

        $createdTopic = $this->em->getRepository('ForumBundle:Topic')->find($topicToUpdate->getId());
        $this->assertSame('New topic', $createdTopic->getTitle());
        $this->assertNull($createdTopic->getUpdatedAt());

        $updatedTopic = '{
            "forum_topic": {
                "title": "Updated topic",
                "description": "This is an updated topic",
                "forum": ' . $this->forum->getId() . '
            }
        }';

        $response = $this->put($client, '/forum/topics/' . $topicToUpdate->getId(), $updatedTopic);

        // Test Response
        $this->assertIsNoContent($response);

        // Test updated Topic
        $this->em->clear();
        $updatedTopic = $this->em->getRepository('ForumBundle:Topic')->find($topicToUpdate->getId());

        $this->assertSame('Updated topic', $updatedTopic->getTitle());
        $this->assertNotNull($updatedTopic->getUpdatedAt());
    }

    /**
     * @dataProvider wrongTopicProvider
     */
    public function testPutTopicWithErrors($topic)
    {
        $client = static::createClient();

        $topicToUpdate = $this->createTopic($this->forum);

        $response = $this->put($client, '/forum/topics/' . $topicToUpdate->getId(), $topic);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutTopicNotFound()
    {
        $client = static::createClient();

        $response = $this->put($client, '/forum/topics/1', '');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteTopic()
    {
        $client = static::createClient();

        $topicToDelete = $this->createTopic($this->forum, 'Topic to delete');

        $createdTopic = $this->em->getRepository('ForumBundle:Topic')->find($topicToDelete->getId());
        $this->assertSame('Topic to delete', $createdTopic->getTitle());
        $this->assertNull($createdTopic->getUpdatedAt());

        $client->request('DELETE', '/forum/topics/' . $topicToDelete->getId());
        $response = $client->getResponse();

        // Test Response
        $this->assertIsNoContent($response);

        // Test deleted Topic
        $this->em->clear();
        $deletedTopic = $this->em->getRepository('ForumBundle:Topic')->find($topicToDelete->getId());

        $this->assertNull($deletedTopic);
    }

    public function testDeleteTopicNotFound()
    {
        $client = static::createClient();

        $client->request('DELETE', '/forum/topics/1');
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

    public function wrongTopicProvider()
    {
        $topic = '{
            "forum_topic": {
                "title": "%s",
                "description": "%s",
                "forum": %s
            }
        }';

        $longString = str_repeat('a', 101);

        return [
            [sprintf($topic, $longString, 'aaaa', 50)],
            [sprintf($topic, $longString, '', 50)],
            [sprintf($topic, 'aaaa', $longString, 50)],
            [sprintf($topic, '', $longString, 50)],
            [sprintf($topic, 'aaaa', 'aaaa', 50)]
        ];
    }
}
