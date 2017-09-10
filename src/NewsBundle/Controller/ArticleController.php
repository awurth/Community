<?php

namespace NewsBundle\Controller;

use AppBundle\Controller\RestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use NewsBundle\Entity\Article;
use NewsBundle\Form\Type\ArticleType;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends RestController
{
    /**
     * @Rest\Get(name="get_news_articles", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     default="1",
     *     description="The current page"
     * )
     * @Rest\QueryParam(
     *     name="per_page",
     *     requirements="\d+",
     *     default="15",
     *     description="Max number of articles per page"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all articles",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Article::class)
     *     )
     * )
     * @SWG\Tag(name="news")
     */
    public function getArticlesAction(ParamFetcher $paramFetcher)
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('NewsBundle:Article')
            ->getCollection(
                $paramFetcher->get('per_page'),
                $paramFetcher->get('page'),
                $paramFetcher->get('order')
            );
    }

    /**
     * @Rest\Get(name="get_news_article", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets an article by it's id",
     *     @Model(type=Article::class)
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The article id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="news")
     */
    public function getArticleAction($id)
    {
        $article = $this->getDoctrine()
            ->getManager()
            ->getRepository('NewsBundle:Article')
            ->find($id);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        return $article;
    }

    /**
     * @Rest\Post(name="post_news_article", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new article"
     * )
     * @SWG\Tag(name="news")
     */
    public function postArticleAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_AUTHOR');

        $article = new Article();
        $article->setAuthor($this->getUser());

        return $this->processForm($article, $request, ArticleType::class, 'get_news_article');
    }

    /**
     * @Rest\Put(name="put_news_article", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Updates an article"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The article id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="news")
     */
    public function putArticleAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_AUTHOR');

        $article = $this->getDoctrine()
            ->getManager()
            ->getRepository('NewsBundle:Article')
            ->find($id);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($article, $request, ArticleType::class);
    }

    /**
     * @Rest\Delete(name="delete_news_article", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Deletes an article"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The article id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="news")
     */
    public function deleteArticleAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_AUTHOR');

        $em = $this->getDoctrine()->getManager();

        $article = $em->getRepository('NewsBundle:Article')->find($id);

        if (null === $article) {
            throw $this->createNotFoundException();
        }

        $em->remove($article);
        $em->flush();

        return null;
    }
}
