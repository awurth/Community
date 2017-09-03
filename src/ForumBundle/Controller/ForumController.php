<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Forum;
use ForumBundle\Form\Type\ForumType;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class ForumController extends RestController
{
    /**
     * @Get(name="get_forum_forums", options={ "method_prefix" = false })
     * @View
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
    public function getForumsAction()
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Forum')
            ->findAll();
    }

    /**
     * @Get(name="get_forum_forum", options={ "method_prefix" = false })
     * @View
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
     * @Post(name="post_forum_forum", options={ "method_prefix" = false })
     * @View
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new forum"
     * )
     * @SWG\Tag(name="forum")
     */
    public function postForumAction(Request $request)
    {
        return $this->processForm(new Forum(), $request, ForumType::class, 'get_forum_forum');
    }

    /**
     * @Put(name="put_forum_forum", options={ "method_prefix" = false })
     * @View
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
        $em = $this->getDoctrine()->getManager();

        $forum = $em->getRepository('ForumBundle:Forum')->find($id);

        if (null === $forum) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($forum, $request, ForumType::class);
    }

    /**
     * @Delete(name="delete_forum_forum", options={ "method_prefix" = false })
     * @View
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
