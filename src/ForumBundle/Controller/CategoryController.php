<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Category;
use ForumBundle\Form\Type\CategoryType;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends RestController
{
    /**
     * @Get(name="get_forum_categories", options={ "method_prefix" = false })
     * @View
     * @QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     default="1",
     *     description="The current page"
     * )
     * @QueryParam(
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
     * @Get(name="get_forum_category", options={ "method_prefix" = false })
     * @View
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
     * @Post(name="post_forum_category", options={ "method_prefix" = false })
     * @View
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new forum category"
     * )
     * @SWG\Tag(name="forum")
     */
    public function postCategoryAction(Request $request)
    {
        return $this->processForm(new Category(), $request, CategoryType::class, 'get_forum_category');
    }

    /**
     * @Put(name="put_forum_category", options={ "method_prefix" = false })
     * @View
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
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository('ForumBundle:Category')->find($id);

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($category, $request, CategoryType::class);
    }

    /**
     * @Delete(name="delete_forum_category", options={ "method_prefix" = false })
     * @View
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
