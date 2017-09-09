<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Forum;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class CategoryForumController extends RestController
{
    /**
     * @Rest\Get(name="get_forum_category_forums", options={ "method_prefix" = false })
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
     *     description="Returns all forums of a category",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Forum::class)
     *     )
     * )
     * @SWG\Tag(name="forum")
     */
    public function getForumsAction($id, ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();

        $category = $em->getRepository('ForumBundle:Category')->find($id);

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        return $em->getRepository('ForumBundle:Forum')->getByCategory(
            $id,
            $paramFetcher->get('per_page'),
            $paramFetcher->get('page'),
            $paramFetcher->get('order')
        );
    }
}
