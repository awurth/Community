<?php

namespace NewsBundle\Controller;

use AppBundle\Controller\RestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use NewsBundle\Entity\Category;
use NewsBundle\Form\Type\CategoryType;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends RestController
{
    /**
     * @Rest\Get(name="get_news_categories", options={ "method_prefix" = false })
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
     *     description="Max number of categories per page"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all news categories",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Category::class)
     *     )
     * )
     * @SWG\Tag(name="news")
     */
    public function getCategoriesAction(ParamFetcher $paramFetcher)
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('NewsBundle:Category')
            ->getCollection(
                $paramFetcher->get('per_page'),
                $paramFetcher->get('page'),
                $paramFetcher->get('order')
            );
    }

    /**
     * @Rest\Get(name="get_news_category", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets a news category by it's id",
     *     @Model(type=Category::class)
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The category id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="news")
     */
    public function getCategoryAction($id)
    {
        $category = $this->getDoctrine()
            ->getManager()
            ->getRepository('NewsBundle:Category')
            ->find($id);

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        return $category;
    }

    /**
     * @Rest\Post(name="post_news_category", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new news category"
     * )
     * @SWG\Tag(name="news")
     */
    public function postCategoryAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_AUTHOR');

        return $this->processForm(new Category(), $request, CategoryType::class, 'get_news_category');
    }

    /**
     * @Rest\Put(name="put_news_category", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Updates a news category"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The category id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="news")
     */
    public function putCategoryAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_AUTHOR');

        $category = $this->getDoctrine()
            ->getManager()
            ->getRepository('NewsBundle:Category')
            ->find($id);

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($category, $request, CategoryType::class);
    }

    /**
     * @Rest\Delete(name="delete_news_category", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Deletes a news category"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The category id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="news")
     */
    public function deleteCategoryAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository('NewsBundle:Category')->find($id);

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        $em->remove($category);
        $em->flush();

        return null;
    }
}
