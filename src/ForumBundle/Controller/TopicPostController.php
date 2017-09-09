<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Post;
use ForumBundle\Form\Type\TopicPostType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class TopicPostController extends RestController
{
    /**
     * @Rest\Get(name="get_forum_topic_posts", options={ "method_prefix" = false })
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
     *     description="Max number of posts per page"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all posts of a topic",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Post::class)
     *     )
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
    public function getPostsAction($id, ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();

        $topic = $em->getRepository('ForumBundle:Topic')->find($id);

        if (null === $topic) {
            throw $this->createNotFoundException();
        }

        return $em->getRepository('ForumBundle:Post')->getByTopic(
            $id,
            $paramFetcher->get('per_page'),
            $paramFetcher->get('page'),
            $paramFetcher->get('order')
        );
    }

    /**
     * @Rest\Get(name="get_forum_topic_post", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets a post by it's topic and id",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Post::class)
     *     )
     * )
     * @SWG\Parameter(
     *     name="topicId",
     *     description="The topic id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Parameter(
     *     name="postId",
     *     description="The post id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function getPostAction($topicId, $postId)
    {
        $post = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Post')
            ->getOneByTopic($topicId, $postId);

        if (null === $post) {
            throw $this->createNotFoundException();
        }

        return $post;
    }

    /**
     * @Rest\Post(name="post_forum_topic_post", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new post"
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
    public function postPostAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $topic = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Topic')
            ->find($id);

        if (null === $topic) {
            throw $this->createNotFoundException();
        }

        $post = new Post();
        $post->setTopic($topic);

        return $this->processForm($post, $request, TopicPostType::class, 'get_forum_post');
    }

    /**
     * @Rest\Put(name="put_forum_topic_post", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Updates a post"
     * )
     * @SWG\Parameter(
     *     name="topicId",
     *     description="The topic id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Parameter(
     *     name="postId",
     *     description="The post id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function putPostAction($topicId, $postId, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $post = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Post')
            ->getOneByTopic($topicId, $postId);

        if (null === $post) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($post, $request, TopicPostType::class);
    }

    /**
     * @Rest\Delete(name="delete_forum_topic_post", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Deletes a post"
     * )
     * @SWG\Parameter(
     *     name="topicId",
     *     description="The topic id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Parameter(
     *     name="postId",
     *     description="The post id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function deleteTopicAction($topicId, $postId)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository('ForumBundle:Post')->getOneByTopic($topicId, $postId);

        if (null === $post) {
            throw $this->createNotFoundException();
        }

        $em->remove($post);
        $em->flush();

        return null;
    }
}
