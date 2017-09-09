<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Forum;
use ForumBundle\Form\Type\ForumType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class ForumController extends RestController
{
    /**
     * @Rest\Get(name="get_forum_forums", options={ "method_prefix" = false })
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
     *     description="Max number of forums per page"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all forums",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Forum::class)
     *     )
     * )
     * @SWG\Tag(name="forum")
     */
    public function getForumsAction(ParamFetcher $paramFetcher)
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Forum')
            ->getCollection(
                $paramFetcher->get('per_page'),
                $paramFetcher->get('page'),
                $paramFetcher->get('order')
            );
    }

    /**
     * @Rest\Get(name="get_forum_forum", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets a forum by it's id",
     *     @Model(type=Forum::class)
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The forum id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function getForumAction($id)
    {
        $forum = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Forum')
            ->find($id);

        if (null === $forum) {
            throw $this->createNotFoundException();
        }

        return $forum;
    }

    /**
     * @Rest\Post(name="post_forum_forum", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new forum"
     * )
     * @SWG\Tag(name="forum")
     */
    public function postForumAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->processForm(new Forum(), $request, ForumType::class, 'get_forum_forum');
    }

    /**
     * @Rest\Put(name="put_forum_forum", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Updates a forum"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The forum id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function putForumAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        $forum = $em->getRepository('ForumBundle:Forum')->find($id);

        if (null === $forum) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($forum, $request, ForumType::class);
    }

    /**
     * @Rest\Delete(name="delete_forum_forum", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Deletes a forum"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The forum id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function deleteForumAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        $forum = $em->getRepository('ForumBundle:Forum')->find($id);

        if (null === $forum) {
            throw $this->createNotFoundException();
        }

        $em->remove($forum);
        $em->flush();

        return null;
    }
}
