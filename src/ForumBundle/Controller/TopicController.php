<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Topic;
use ForumBundle\Form\Type\TopicType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class TopicController extends RestController
{
    /**
     * @Rest\Get(name="get_forum_topics", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all forum topics",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Topic::class)
     *     )
     * )
     * @SWG\Tag(name="forum")
     */
    public function getTopicsAction()
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Topic')
            ->findAll();
    }

    /**
     * @Rest\Get(name="get_forum_topic", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets a forum topic by it's id",
     *     @Model(type=Topic::class)
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The topic id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function getTopicAction($id)
    {
        $topic = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Topic')
            ->find($id);

        if (null === $topic) {
            throw $this->createNotFoundException();
        }

        return $topic;
    }

    /**
     * @Rest\Post(name="post_forum_topic", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new forum topic"
     * )
     * @SWG\Tag(name="forum")
     */
    public function postTopicAction(Request $request)
    {
        return $this->processForm(new Topic(), $request, TopicType::class, 'get_forum_topic');
    }

    /**
     * @Rest\Put(name="put_forum_topic", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Updates a forum topic"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The topic id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function putTopicAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $topic = $em->getRepository('ForumBundle:Topic')->find($id);

        if (null === $topic) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($topic, $request, TopicType::class);
    }

    /**
     * @Rest\Delete(name="delete_forum_topic", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Deletes a forum topic"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The topic id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function deleteTopicAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $topic = $em->getRepository('ForumBundle:Topic')->find($id);

        if (null === $topic) {
            throw $this->createNotFoundException();
        }

        $em->remove($topic);
        $em->flush();

        return null;
    }
}
