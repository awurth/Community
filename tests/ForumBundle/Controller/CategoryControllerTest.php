<?php

namespace Tests\ForumBundle\Controller;

use ForumBundle\Entity\Category;
use Tests\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->emptyTable('forum_forum');
        $this->emptyTable('forum_category');
    }

    public function testGetCategories()
    {
        $client = static::createClient();

        $client->request('GET', '/forum/categories');
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson([], $response->getContent());
    }

    public function testGetCategoryNotFound()
    {
        $client = static::createClient();

        $client->request('GET', '/forum/categories/1');
        $response = $client->getResponse();

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testGetCategory()
    {
        $category = $this->createCategory();

        $client = static::createClient();

        $client->request('GET', '/forum/categories/' . $category->getId());
        $response = $client->getResponse();

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson($category, $response->getContent());
    }

    public function testPostCategory()
    {
        $content = '{
            "forum_category": {
                "title": "Category title",
                "description": "This is a category"
            }
        }';

        $client = static::createClient();

        $response = $this->post($client, '/forum/categories', $content);

        // Test Response
        $this->assertIsCreated($response);
        $this->assertJsonResponse($response, false);

        // Test created Category
        $client->request('GET', $response->headers->get('Location'));

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertContains('"title":"Category title"', $client->getResponse()->getContent());
    }

    /**
     * @dataProvider wrongCategoryProvider
     */
    public function testPostCategoryWithErrors($category)
    {
        $client = static::createClient();

        $response = $this->post($client, '/forum/categories', $category);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutCategory()
    {
        $client = static::createClient();

        $categoryToUpdate = $this->createCategory('New category');

        $createdCategory = $this->em->getRepository('ForumBundle:Category')->find($categoryToUpdate->getId());
        $this->assertSame('New category', $createdCategory->getTitle());
        $this->assertNull($createdCategory->getUpdatedAt());

        $updatedCategory = '{
            "forum_category": {
                "title": "Updated category",
                "description": "This is an updated category"
            }
        }';

        $response = $this->put($client, '/forum/categories/' . $categoryToUpdate->getId(), $updatedCategory);

        // Test Response
        $this->assertIsNoContent($response);

        // Test updated Category
        $this->em->clear();
        $updatedCategory = $this->em->getRepository('ForumBundle:Category')->find($categoryToUpdate->getId());

        $this->assertSame('Updated category', $updatedCategory->getTitle());
        $this->assertNotNull($updatedCategory->getUpdatedAt());
    }

    /**
     * @dataProvider wrongCategoryProvider
     */
    public function testPutCategoryWithErrors($category)
    {
        $client = static::createClient();

        $categoryToUpdate = $this->createCategory();

        $response = $this->put($client, '/forum/categories/' . $categoryToUpdate->getId(), $category);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutCategoryNotFound()
    {
        $client = static::createClient();

        $response = $this->put($client, '/forum/categories/1', '');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteCategory()
    {
        $client = static::createClient();

        $categoryToDelete = $this->createCategory('Category to delete');

        $createdCategory = $this->em->getRepository('ForumBundle:Category')->find($categoryToDelete->getId());
        $this->assertSame('Category to delete', $createdCategory->getTitle());
        $this->assertNull($createdCategory->getUpdatedAt());

        $client->request('DELETE', '/forum/categories/' . $categoryToDelete->getId());
        $response = $client->getResponse();

        // Test Response
        $this->assertIsNoContent($response);

        $this->em->clear();
        $deletedCategory = $this->em->getRepository('ForumBundle:Category')->find($categoryToDelete->getId());

        $this->assertNull($deletedCategory);
    }

    public function testDeleteCategoryNotFound()
    {
        $client = static::createClient();

        $client->request('DELETE', '/forum/categories/1');
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

    public function wrongCategoryProvider()
    {
        $category = '{
            "forum_category": {
                "title": "%s",
                "description": "%s"
            }
        }';

        $longString = str_repeat('a', 101);

        return [
            [sprintf($category, $longString, 'aaaa')],
            [sprintf($category, $longString, '')],
            [sprintf($category, 'aaaa', $longString)],
            [sprintf($category, '', $longString)]
        ];
    }
}
