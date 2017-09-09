<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Forum;
use ForumBundle\Form\Type\CategoryForumType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

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
     * @SWG\Parameter(
     *     name="id",
     *     description="The category id",
     *     in="path",
     *     type="integer",
     *     required=true
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

    /**
     * @Rest\Get(name="get_forum_category_forum", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets a forum by it's category and id",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Forum::class)
     *     )
     * )
     * @SWG\Parameter(
     *     name="categoryId",
     *     description="The category id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Parameter(
     *     name="forumId",
     *     description="The forum id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function getForumAction($categoryId, $forumId)
    {
        $forum = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Forum')
            ->getOneByCategory($categoryId, $forumId);

        if (null === $forum) {
            throw $this->createNotFoundException();
        }

        return $forum;
    }

    /**
     * @Rest\Post(name="post_forum_category_forum", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new forum"
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
    public function postForumAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $category = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Category')
            ->find($id);

        if (null === $category) {
            throw $this->createNotFoundException();
        }

        $forum = new Forum();
        $forum->setCategory($category);

        return $this->processForm($forum, $request, CategoryForumType::class, 'get_forum_forum');
    }

    /**
     * @Rest\Put(name="put_forum_category_forum", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Updates a forum"
     * )
     * @SWG\Parameter(
     *     name="categoryId",
     *     description="The category id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Parameter(
     *     name="forumId",
     *     description="The forum id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function putForumAction($categoryId, $forumId, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $forum = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Forum')
            ->getOneByCategory($categoryId, $forumId);

        if (null === $forum) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($forum, $request, CategoryForumType::class);
    }

    /**
     * @Rest\Delete(name="delete_forum_category_forum", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Deletes a forum"
     * )
     * @SWG\Parameter(
     *     name="categoryId",
     *     description="The category id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Parameter(
     *     name="forumId",
     *     description="The forum id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function deleteForumAction($categoryId, $forumId)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        $forum = $em->getRepository('ForumBundle:Forum')->getOneByCategory($categoryId, $forumId);

        if (null === $forum) {
            throw $this->createNotFoundException();
        }

        $em->remove($forum);
        $em->flush();

        return null;
    }
}
