<?php

namespace Tests\ForumBundle\Controller;

use ForumBundle\Entity\Category;
use Tests\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    const RESOURCE_URI = '/forum/categories';

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadFixture('Forum', 'Category');
    }

    public function testGetCategories()
    {
        $response = $this->get($this->makeClient(), self::RESOURCE_URI);

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertJsonResourcesCount($response, 3);
    }

    public function testGetCategory()
    {
        $category = $this->findFirst('ForumBundle:Category');

        $response = $this->get($this->makeClient(), self::RESOURCE_URI . '/' . $category->getId());

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson($category, $response->getContent());
    }

    public function testGetCategoryNotFound()
    {
        $response = $this->get($this->makeClient(), self::RESOURCE_URI . '/a');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testPostCategory()
    {
        $content = '{
            "forum_category": {
                "title": "Category title",
                "description": "This is a category"
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
        $this->assertContains('"title":"Category title"', $response->getContent());
    }

    public function testPostCategoryBadRole()
    {
        $response = $this->post($this->makeClient(), self::RESOURCE_URI, '');

        $this->assertIsUnauthorized($response);

        $response = $this->post($this->createLoggedClient(), self::RESOURCE_URI, '');

        $this->assertIsForbidden($response);
    }

    public function testPostCategoryWithErrors()
    {
        $category = '{
            "forum_category": {
                "title": "",
                "description": ""
            }
        }';

        $response = $this->post($this->createAdminClient(), self::RESOURCE_URI, $category);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutCategory()
    {
        $categoryToUpdate = $this->createCategory('New category');

        $this->assertNull($categoryToUpdate->getUpdatedAt());

        $jsonCategory = '{
            "forum_category": {
                "title": "Updated category",
                "description": "This is an updated category"
            }
        }';

        $client = $this->createAdminClient();

        $response = $this->put($client, self::RESOURCE_URI . '/' . $categoryToUpdate->getId(), $jsonCategory);

        // Test Response
        $this->assertIsNoContent($response);

        // Test updated Category
        $this->em->clear();
        $updatedCategory = $this->em->getRepository('ForumBundle:Category')->find($categoryToUpdate->getId());

        $this->assertSame('Updated category', $updatedCategory->getTitle());
        $this->assertNotNull($updatedCategory->getUpdatedAt());
    }

    public function testPutCategoryBadRole()
    {
        $response = $this->put($this->makeClient(), self::RESOURCE_URI . '/1', '');

        $this->assertIsUnauthorized($response);
        $this->assertJsonResponse($response);

        $response = $this->put($this->createLoggedClient(), self::RESOURCE_URI . '/1', '');

        $this->assertIsForbidden($response);
        $this->assertJsonResponse($response);
    }

    public function testPutCategoryWithErrors()
    {
        $category = '{
            "forum_category": {
                "title": "",
                "description": ""
            }
        }';

        $categoryToUpdate = $this->createCategory();

        $response = $this->put($this->createAdminClient(), self::RESOURCE_URI . '/' . $categoryToUpdate->getId(), $category);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutCategoryNotFound()
    {
        $response = $this->put($this->createAdminClient(), self::RESOURCE_URI . '/a', '');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteCategory()
    {
        $categoryToDelete = $this->createCategory('Category to delete');

        $response = $this->delete($this->createAdminClient(), self::RESOURCE_URI . '/' . $categoryToDelete->getId());

        // Test Response
        $this->assertIsNoContent($response);

        // Test deleted Category
        $this->em->clear();
        $deletedCategory = $this->em->getRepository('ForumBundle:Category')->find($categoryToDelete->getId());

        $this->assertNull($deletedCategory);
    }

    public function testDeleteCategoryBadRole()
    {
        $response = $this->delete($this->makeClient(), self::RESOURCE_URI . '/a');

        $this->assertIsUnauthorized($response);
        $this->assertJsonResponse($response);

        $response = $this->delete($this->createLoggedClient(), self::RESOURCE_URI . '/a');

        $this->assertIsForbidden($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteCategoryNotFound()
    {
        $response = $this->delete($this->createAdminClient(), self::RESOURCE_URI . '/a');

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
}
