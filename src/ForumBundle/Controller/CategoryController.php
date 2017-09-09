<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Category;
use ForumBundle\Form\Type\CategoryType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends RestController
{
    /**
     * @Rest\Get(name="get_forum_categories", options={ "method_prefix" = false })
     * @Rest\View
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
     *     description="Returns all forum categories",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Category::class)
     *     )
     * )
     * @SWG\Tag(name="forum")
     */
    public function getCategoriesAction(ParamFetcher $paramFetcher)
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Category')
            ->getCollection(
                $paramFetcher->get('per_page'),
                $paramFetcher->get('page'),
                $paramFetcher->get('order')
            );
    }

    /**
     * @Rest\Get(name="get_forum_category", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets a forum category by it's id",
     *     @Model(type=Category::class)
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The category id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function getCategoryAction($id)
    {
        $category = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Category')
            ->find($id);

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        return $category;
    }

    /**
     * @Rest\Post(name="post_forum_category", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new forum category"
     * )
     * @SWG\Tag(name="forum")
     */
    public function postCategoryAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->processForm(new Category(), $request, CategoryType::class, 'get_forum_category');
    }

    /**
     * @Rest\Put(name="put_forum_category", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Updates a forum category"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The category id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function putCategoryAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $category = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Category')
            ->find($id);

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($category, $request, CategoryType::class);
    }

    /**
     * @Rest\Delete(name="delete_forum_category", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Deletes a forum category"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The category id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function deleteCategoryAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository('ForumBundle:Category')->find($id);

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        $em->remove($category);
        $em->flush();

        return null;
    }
}
