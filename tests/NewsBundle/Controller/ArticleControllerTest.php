<?php

namespace Tests\NewsBundle\Controller;

use NewsBundle\Entity\Article;
use NewsBundle\Entity\Category;
use Tests\WebTestCase;
use Traversable;
use UserBundle\Entity\User;

class ArticleControllerTest extends WebTestCase
{
    const RESOURCE_URI = '/news/articles';

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
            $this->getFixtureClass('User', 'User'),
            $this->getFixtureClass('News', 'Category'),
            $this->getFixtureClass('News', 'Article')
        ]);

        $this->category = $this->findFirst('NewsBundle:Category');
    }

    public function testGetArticles()
    {
        $response = $this->get($this->makeClient(), self::RESOURCE_URI);

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertJsonResourcesCount($response, 9);
    }

    public function testGetArticle()
    {
        $forum = $this->findFirst('NewsBundle:Article');

        $response = $this->get($this->makeClient(), self::RESOURCE_URI . '/' . $forum->getId());

        $this->assertIsOk($response);
        $this->assertJsonResponse($response);
        $this->assertEqualsJson($forum, $response->getContent());
    }

    public function testGetArticleNotFound()
    {
        $response = $this->get($this->makeClient(), self::RESOURCE_URI . '/a');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testPostArticle()
    {
        $content = '{
            "news_article": {
                "title": "Article title",
                "content": "This is an article",
                "published": true,
                "category": ' . $this->category->getId() . '
            }
        }';

        $client = $this->makeClient();
        $token = $this->logIn($client, 'author', 'author');

        $response = $this->post($client, self::RESOURCE_URI, $content, $token);

        $this->assertEquals('', $response->getContent());

        // Test Response
        $this->assertIsCreated($response);
        $this->assertJsonResponse($response, false);

        // Test created Article
        $response = $this->get($client, $response->headers->get('Location'));

        $this->assertIsOk($response);
        $this->assertContains('"title":"Article title"', $response->getContent());
    }

    public function testPostArticleBadRole()
    {
        $response = $this->post($this->makeClient(), self::RESOURCE_URI, '');

        $this->assertIsUnauthorized($response);

        $client = $this->makeClient();
        $token = $this->logIn($client, 'awurth', 'awurth');

        $response = $this->post($this->createLoggedClient(), self::RESOURCE_URI, '', $token);

        $this->assertIsForbidden($response);
    }

    public function testPostArticleWithErrors()
    {
        $article = '{
            "news_article": {
                "title": "",
                "content": "",
                "category": ""
            }
        }';

        $response = $this->post($this->createAdminClient(), self::RESOURCE_URI, $article);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutArticle()
    {
        $user = $this->em->getRepository('UserBundle:User')->findOneBy(['username' => 'author']);

        $articleToUpdate = $this->createArticle($this->category, $user, 'New article');

        $this->assertNull($articleToUpdate->getUpdatedAt());

        $jsonArticle = '{
            "news_article": {
                "title": "Updated article",
                "content": "This is an updated article",
                "category": ' . $articleToUpdate->getCategory()->getId() . '
            }
        }';

        $client = $this->makeClient();
        $token = $this->logIn($client, 'author', 'author');

        $response = $this->put($client, self::RESOURCE_URI . '/' . $articleToUpdate->getId(), $jsonArticle, $token);

        // Test Response
        $this->assertIsNoContent($response);

        // Test updated Article
        $this->em->clear();
        $updatedArticle = $this->em->getRepository('NewsBundle:Article')->find($articleToUpdate->getId());

        $this->assertSame('Updated article', $updatedArticle->getTitle());
        $this->assertNotNull($updatedArticle->getUpdatedAt());
    }

    public function testPutArticleBadRole()
    {
        $response = $this->put($this->makeClient(), self::RESOURCE_URI . '/1', '');

        $this->assertIsUnauthorized($response);
        $this->assertJsonResponse($response);

        $response = $this->put($this->createLoggedClient(), self::RESOURCE_URI . '/1', '');

        $this->assertIsForbidden($response);
        $this->assertJsonResponse($response);
    }

    public function testPutArticleWithErrors()
    {
        $article = '{
            "news_article": {
                "title": "",
                "content": "",
                "category": ""
            }
        }';

        $articleToUpdate = $this->findFirst('NewsBundle:Article');

        $response = $this->put($this->createAdminClient(), self::RESOURCE_URI . '/' . $articleToUpdate->getId(), $article);

        $this->assertIsBadRequest($response);
        $this->assertJsonResponse($response);
    }

    public function testPutArticleNotFound()
    {
        $response = $this->put($this->createAdminClient(), self::RESOURCE_URI . '/a', '');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteArticle()
    {
        $articleToDelete = $this->findFirst('NewsBundle:Article');

        $response = $this->delete($this->createLoggedClient(['ROLE_AUTHOR']), self::RESOURCE_URI . '/' . $articleToDelete->getId());

        // Test Response
        $this->assertIsNoContent($response);

        // Test deleted Article
        $this->em->clear();
        $deletedArticle = $this->em->getRepository('NewsBundle:Article')->find($articleToDelete->getId());

        $this->assertNull($deletedArticle);
    }

    public function testDeleteArticleBadRole()
    {
        $response = $this->delete($this->makeClient(), self::RESOURCE_URI . '/a');

        $this->assertIsUnauthorized($response);
        $this->assertJsonResponse($response);

        $response = $this->delete($this->createLoggedClient(), self::RESOURCE_URI . '/a');

        $this->assertIsForbidden($response);
        $this->assertJsonResponse($response);
    }

    public function testDeleteArticleNotFound()
    {
        $response = $this->delete($this->createAdminClient(), self::RESOURCE_URI . '/a');

        $this->assertIsNotFound($response);
        $this->assertJsonResponse($response);
    }

    /**
     * Creates a new Article.
     *
     * @param Category $category
     * @param User     $author
     * @param string   $title
     * @param string   $content
     * @param bool     $published
     * @param Traversable $tags
     *
     * @return Article
     */
    public function createArticle(Category $category, User $author, $title = null, $content = null, $published = true, Traversable $tags = null)
    {
        $article = new Article();
        $article->setTitle($title ?: 'Article title');
        $article->setContent($content ?: 'This is an article');
        $article->setPublished($published);
        $article->setCategory($category);
        $article->setAuthor($author);

        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $article->addTag($tag);
            }
        }

        $this->em->persist($article);
        $this->em->flush();

        return $article;
    }
}
