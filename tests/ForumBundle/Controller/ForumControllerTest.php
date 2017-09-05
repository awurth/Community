<?php

namespace Tests\ForumBundle\Controller;

use ForumBundle\Entity\Category;
use ForumBundle\Entity\Forum;
use Tests\WebTestCase;

class ForumControllerTest extends WebTestCase
{
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

        $this->emptyTable('forum_topic');
        $this->emptyTable('forum_forum');
        $this->emptyTable('forum_category');

        $this->category = $this->createCategory();
    }

    public function testGetForums()
    {
        $client = static::createClient();

        $client->request('GET', '/forum/forums');
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson([], $response->getContent());
    }

    public function testGetForumNotFound()
    {
        $client = static::createClient();

        $client->request('GET', '/forum/forums/1');
        $response = $client->getResponse();

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testGetForum()
    {
        $forum = $this->createForum($this->category);

        $client = static::createClient();

        $client->request('GET', '/forum/forums/' . $forum->getId());
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson($forum, $response->getContent());
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

        $client = static::createClient();

        $response = $this->post($client, '/forum/forums', $content);

        // Test Response
        $this->assertIsCreated($response);
        $this->assertJsonResponse($response, false);

        // Test created Category
        $client->request('GET', $response->headers->get('Location'));
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertContains('"title":"Forum title"', $response->getContent());
    }

    /**
     * @dataProvider wrongForumProvider
     */
    public function testPostForumWithErrors($forum)
    {
        $client = static::createClient();

        $response = $this->post($client, '/forum/forums', $forum);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutForum()
    {
        $client = static::createClient();

        $forumToUpdate = $this->createForum($this->category, 'New forum');

        $createdForum = $this->em->getRepository('ForumBundle:Forum')->find($forumToUpdate->getId());
        $this->assertSame('New forum', $createdForum->getTitle());
        $this->assertNull($createdForum->getUpdatedAt());

        $updatedForum = '{
            "forum_forum": {
                "title": "Updated forum",
                "description": "This is an updated forum",
                "category": ' . $this->category->getId() . '
            }
        }';

        $response = $this->put($client, '/forum/forums/' . $forumToUpdate->getId(), $updatedForum);

        // Test Response
        $this->assertIsNoContent($response);

        // Test updated Forum
        $this->em->clear();
        $updatedForum = $this->em->getRepository('ForumBundle:Forum')->find($forumToUpdate->getId());

        $this->assertSame('Updated forum', $updatedForum->getTitle());
        $this->assertNotNull($updatedForum->getUpdatedAt());
    }

    /**
     * @dataProvider wrongForumProvider
     */
    public function testPutForumWithErrors($forum)
    {
        $client = static::createClient();

        $forumToUpdate = $this->createForum($this->category);

        $response = $this->put($client, '/forum/forums/' . $forumToUpdate->getId(), $forum);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutForumNotFound()
    {
        $client = static::createClient();

        $response = $this->put($client, '/forum/forums/1', '');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteForum()
    {
        $client = static::createClient();

        $forumToDelete = $this->createForum($this->category, 'Forum to delete');

        $createdForum = $this->em->getRepository('ForumBundle:Forum')->find($forumToDelete->getId());
        $this->assertSame('Forum to delete', $createdForum->getTitle());
        $this->assertNull($createdForum->getUpdatedAt());

        $client->request('DELETE', '/forum/forums/' . $forumToDelete->getId());
        $response = $client->getResponse();

        // Test Response
        $this->assertIsNoContent($response);

        // Test deleted Forum
        $this->em->clear();
        $deletedForum = $this->em->getRepository('ForumBundle:Forum')->find($forumToDelete->getId());

        $this->assertNull($deletedForum);
    }

    public function testDeleteForumNotFound()
    {
        $client = static::createClient();

        $client->request('DELETE', '/forum/forums/1');
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

    public function wrongForumProvider()
    {
        $forum = '{
            "forum_forum": {
                "title": "%s",
                "description": "%s",
                "category": %s
            }
        }';

        $longString = str_repeat('a', 101);

        return [
            [sprintf($forum, $longString, 'aaaa', 50)],
            [sprintf($forum, $longString, '', 50)],
            [sprintf($forum, 'aaaa', $longString, 50)],
            [sprintf($forum, '', $longString, 50)],
            [sprintf($forum, 'aaaa', 'aaaa', 50)]
        ];
    }
}
