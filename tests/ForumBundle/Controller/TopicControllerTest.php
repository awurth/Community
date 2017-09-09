<?php

namespace Tests\ForumBundle\Controller;

use ForumBundle\Entity\Forum;
use ForumBundle\Entity\Topic;
use Tests\WebTestCase;

class TopicControllerTest extends WebTestCase
{
    const RESOURCE_URI = '/forum/topics';

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

        $this->loadFixtures([
            $this->getFixtureClass('Forum', 'Category'),
            $this->getFixtureClass('Forum', 'Forum'),
            $this->getFixtureClass('Forum', 'Topic')
        ]);

        $this->forum = $this->findFirst('ForumBundle:Forum');
    }

    public function testGetTopics()
    {
        $response = $this->get($this->makeClient(), self::RESOURCE_URI);

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertJsonResourcesCount($response, 15);
    }

    public function testGetTopic()
    {
        $topic = $this->findFirst('ForumBundle:Topic');

        $response = $this->get($this->makeClient(), self::RESOURCE_URI . '/' . $topic->getId());

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson($topic, $response->getContent());
    }

    public function testGetTopicNotFound()
    {
        $response = $this->get($this->makeClient(), self::RESOURCE_URI . '/a');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testPostTopic()
    {
        $topic = '{
            "forum_topic": {
                "title": "Topic title",
                "description": "This is a topic",
                "forum": ' . $this->forum->getId() . '
            }
        }';

        $client = $this->createLoggedClient();

        $response = $this->post($client, self::RESOURCE_URI, $topic);

        // Test Response
        $this->assertIsCreated($response);
        $this->assertJsonResponse($response, false);

        // Test created Topic
        $response = $this->get($client, $response->headers->get('Location'));

        $this->assertIsOk($response);
        $this->assertContains('"title":"Topic title"', $response->getContent());
    }

    public function testPostTopicBadRole()
    {
        $response = $this->post($this->makeClient(), self::RESOURCE_URI, '');

        $this->assertIsUnauthorized($response);
    }

    public function testPostTopicWithErrors()
    {
        $topic = '{
            "forum_topic": {
                "title": "",
                "description": "",
                "forum": ""
            }
        }';

        $response = $this->post($this->createLoggedClient(), self::RESOURCE_URI, $topic);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutTopic()
    {
        $topicToUpdate = $this->createTopic($this->forum, 'New topic');

        $this->assertNull($topicToUpdate->getUpdatedAt());

        $jsonTopic = '{
            "forum_topic": {
                "title": "Updated topic",
                "description": "This is an updated topic",
                "forum": ' . $this->forum->getId() . '
            }
        }';

        $client = $this->createAdminClient();

        $response = $this->put($client, self::RESOURCE_URI . '/' . $topicToUpdate->getId(), $jsonTopic);

        // Test Response
        $this->assertIsNoContent($response);

        // Test updated Topic
        $this->em->clear();
        $updatedTopic = $this->em->getRepository('ForumBundle:Topic')->find($topicToUpdate->getId());

        $this->assertSame('Updated topic', $updatedTopic->getTitle());
        $this->assertNotNull($updatedTopic->getUpdatedAt());
    }

    public function testPutTopicBadRole()
    {
        $response = $this->put($this->makeClient(), self::RESOURCE_URI . '/1', '');

        $this->assertIsUnauthorized($response);
        $this->assertJsonResponse($response);

        $response = $this->put($this->createLoggedClient(), self::RESOURCE_URI . '/1', '');

        $this->assertIsForbidden($response);
        $this->assertJsonResponse($response);
    }

    public function testPutTopicWithErrors()
    {
        $topic = '{
            "forum_topic": {
                "title": "",
                "description": "",
                "forum": ""
            }
        }';

        $topicToUpdate = $this->findFirst('ForumBundle:Topic');

        $response = $this->put($this->createAdminClient(), self::RESOURCE_URI . '/' . $topicToUpdate->getId(), $topic);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutTopicNotFound()
    {
        $response = $this->put($this->createAdminClient(), self::RESOURCE_URI . '/a', '');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteTopic()
    {
        $topicToDelete = $this->findFirst('ForumBundle:Topic');

        $response = $this->delete($this->createAdminClient(), self::RESOURCE_URI . '/' . $topicToDelete->getId());

        // Test Response
        $this->assertIsNoContent($response);

        // Test deleted Topic
        $this->em->clear();
        $deletedTopic = $this->em->getRepository('ForumBundle:Topic')->find($topicToDelete->getId());

        $this->assertNull($deletedTopic);
    }

    public function testDeleteTopicBadRole()
    {
        $response = $this->delete($this->makeClient(), self::RESOURCE_URI . '/a');

        $this->assertIsUnauthorized($response);
        $this->assertJsonResponse($response);

        $response = $this->delete($this->createLoggedClient(), self::RESOURCE_URI . '/a');

        $this->assertIsForbidden($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteTopicNotFound()
    {
        $response = $this->delete($this->createAdminClient(), self::RESOURCE_URI . '/a');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
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
}
