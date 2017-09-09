<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Topic;
use ForumBundle\Form\Type\TopicType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class TopicController extends RestController
{
    /**
     * @Rest\Get(name="get_forum_topics", options={ "method_prefix" = false })
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
     *     description="Returns all forum topics",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Topic::class)
     *     )
     * )
     * @SWG\Tag(name="forum")
     */
    public function getTopicsAction(ParamFetcher $paramFetcher)
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Topic')
            ->getCollection(
                $paramFetcher->get('per_page'),
                $paramFetcher->get('page'),
                $paramFetcher->get('order')
            );
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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
