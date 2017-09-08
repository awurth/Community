<?php

namespace Tests\ForumBundle\Controller;

use ForumBundle\Entity\Category;
use ForumBundle\Entity\Forum;
use Tests\WebTestCase;

class ForumControllerTest extends WebTestCase
{
    const RESOURCE_URI = '/forum/forums';

    /**
     * @var Category
     */
    protected $category;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            $this->getFixtureClass('Forum', 'Category'),
            $this->getFixtureClass('Forum', 'Forum')
        ]);

        $this->category = $this->findFirst('ForumBundle:Category');
    }

    public function testGetForums()
    {
        $response = $this->get($this->makeClient(), self::RESOURCE_URI);

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertJsonResourcesCount($response, 9);
    }

    public function testGetForum()
    {
        $forum = $this->findFirst('ForumBundle:Forum');

        $response = $this->get($this->makeClient(), self::RESOURCE_URI . '/' . $forum->getId());

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson($forum, $response->getContent());
    }

    public function testGetForumNotFound()
    {
        $response = $this->get($this->makeClient(), self::RESOURCE_URI . '/a');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testPostForum()
    {
        $content = '{
            "forum_forum": {
                "title": "Forum title",
                "description": "This is a forum",
                "category": ' . $this->category->getId() . '
            }
        }';

        $client = $this->createAdminClient();

        $response = $this->post($client, self::RESOURCE_URI, $content);

        // Test Response
        $this->assertIsCreated($response);
        $this->assertJsonResponse($response, false);

        // Test created Category
        $response = $this->get($client, $response->headers->get('Location'));

        $this->assertIsOk($response);
        $this->assertContains('"title":"Forum title"', $response->getContent());
    }

    public function testPostForumBadRole()
    {
        $response = $this->post($this->makeClient(), self::RESOURCE_URI, '');

        $this->assertIsUnauthorized($response);

        $response = $this->post($this->createLoggedClient(), self::RESOURCE_URI, '');

        $this->assertIsForbidden($response);
    }

    public function testPostForumWithErrors()
    {
        $forum = '{
            "forum_forum": {
                "title": "",
                "description": "",
                "category": ""
            }
        }';

        $response = $this->post($this->createAdminClient(), self::RESOURCE_URI, $forum);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutForum()
    {
        $forumToUpdate = $this->createForum($this->category, 'New forum');

        $this->assertNull($forumToUpdate->getUpdatedAt());

        $jsonForum = '{
            "forum_forum": {
                "title": "Updated forum",
                "description": "This is an updated forum",
                "category": ' . $this->category->getId() . '
            }
        }';

        $client = $this->createAdminClient();

        $response = $this->put($client, self::RESOURCE_URI . '/' . $forumToUpdate->getId(), $jsonForum);

        // Test Response
        $this->assertIsNoContent($response);

        // Test updated Forum
        $this->em->clear();
        $updatedForum = $this->em->getRepository('ForumBundle:Forum')->find($forumToUpdate->getId());

        $this->assertSame('Updated forum', $updatedForum->getTitle());
        $this->assertNotNull($updatedForum->getUpdatedAt());
    }

    public function testPutForumBadRole()
    {
        $response = $this->put($this->makeClient(), self::RESOURCE_URI . '/1', '');

        $this->assertIsUnauthorized($response);
        $this->assertJsonResponse($response);

        $response = $this->put($this->createLoggedClient(), self::RESOURCE_URI . '/1', '');

        $this->assertIsForbidden($response);
        $this->assertJsonResponse($response);
    }

    public function testPutForumWithErrors()
    {
        $forum = '{
            "forum_forum": {
                "title": "",
                "description": "",
                "category": ""
            }
        }';

        $forumToUpdate = $this->findFirst('ForumBundle:Forum');

        $response = $this->put($this->createAdminClient(), self::RESOURCE_URI . '/' . $forumToUpdate->getId(), $forum);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutForumNotFound()
    {
        $response = $this->put($this->createAdminClient(), self::RESOURCE_URI . '/a', '');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteForum()
    {
        $forumToDelete = $this->findFirst('ForumBundle:Forum');

        $response = $this->delete($this->createAdminClient(), self::RESOURCE_URI . '/' . $forumToDelete->getId());

        // Test Response
        $this->assertIsNoContent($response);

        // Test deleted Forum
        $this->em->clear();
        $deletedForum = $this->em->getRepository('ForumBundle:Forum')->find($forumToDelete->getId());

        $this->assertNull($deletedForum);
    }

    public function testDeleteForumBadRole()
    {
        $response = $this->delete($this->makeClient(), self::RESOURCE_URI . '/a');

        $this->assertIsUnauthorized($response);
        $this->assertJsonResponse($response);

        $response = $this->delete($this->createLoggedClient(), self::RESOURCE_URI . '/a');

        $this->assertIsForbidden($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteForumNotFound()
    {
        $response = $this->delete($this->createAdminClient(), self::RESOURCE_URI . '/a');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
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
}
