<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Topic;
use ForumBundle\Form\Type\ForumTopicType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class ForumTopicController extends RestController
{
    /**
     * @Rest\Get(name="get_forum_forum_topics", options={ "method_prefix" = false })
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
     *     description="Max number of topics per page"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all topics of a forum",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Topic::class)
     *     )
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
    public function getTopicsAction($id, ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();

        $forum = $em->getRepository('ForumBundle:Forum')->find($id);

        if (null === $forum) {
            throw $this->createNotFoundException();
        }

        return $em->getRepository('ForumBundle:Topic')->getByForum(
            $id,
            $paramFetcher->get('per_page'),
            $paramFetcher->get('page'),
            $paramFetcher->get('order')
        );
    }

    /**
     * @Rest\Get(name="get_forum_forum_topic", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets a topic by it's forum and id",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Topic::class)
     *     )
     * )
     * @SWG\Parameter(
     *     name="forumId",
     *     description="The forum id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Parameter(
     *     name="topicId",
     *     description="The topic id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function getTopicAction($forumId, $topicId)
    {
        $topic = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Topic')
            ->getOneByForum($forumId, $topicId);

        if (null === $topic) {
            throw $this->createNotFoundException();
        }

        return $topic;
    }

    /**
     * @Rest\Post(name="post_forum_forum_topic", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new topic"
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
    public function postTopicAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $forum = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Forum')
            ->find($id);

        if (null === $forum) {
            throw $this->createNotFoundException();
        }

        $topic = new Topic();
        $topic->setForum($forum);

        return $this->processForm($topic, $request, ForumTopicType::class, 'get_forum_topic');
    }

    /**
     * @Rest\Put(name="put_forum_forum_topic", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Updates a topic"
     * )
     * @SWG\Parameter(
     *     name="forumId",
     *     description="The forum id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Parameter(
     *     name="topicId",
     *     description="The topic id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function putTopicAction($forumId, $topicId, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $topic = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Topic')
            ->getOneByForum($forumId, $topicId);

        if (null === $topic) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($topic, $request, ForumTopicType::class);
    }

    /**
     * @Rest\Delete(name="delete_forum_forum_topic", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Deletes a topic"
     * )
     * @SWG\Parameter(
     *     name="forumId",
     *     description="The forum id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Parameter(
     *     name="topicId",
     *     description="The topic id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function deleteTopicAction($forumId, $topicId)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        $topic = $em->getRepository('ForumBundle:Topic')->getOneByForum($forumId, $topicId);

        if (null === $topic) {
            throw $this->createNotFoundException();
        }

        $em->remove($topic);
        $em->flush();

        return null;
    }
}
