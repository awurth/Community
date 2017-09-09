<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Post as ForumPost;
use ForumBundle\Form\Type\PostType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class PostController extends RestController
{
    /**
     * @Rest\Get(name="get_forum_posts", options={ "method_prefix" = false })
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
     *     description="Returns all forum posts",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=ForumPost::class)
     *     )
     * )
     * @SWG\Tag(name="forum")
     */
    public function getPostsAction(ParamFetcher $paramFetcher)
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Post')
            ->getCollection(
                $paramFetcher->get('per_page'),
                $paramFetcher->get('page'),
                $paramFetcher->get('order')
            );
    }

    /**
     * @Rest\Get(name="get_forum_post", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets a forum post by it's id",
     *     @Model(type=ForumPost::class)
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The post id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function getPostAction($id)
    {
        $post = $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Post')
            ->find($id);

        if (null === $post) {
            throw $this->createNotFoundException();
        }

        return $post;
    }

    /**
     * @Rest\Post(name="post_forum_post", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new forum post"
     * )
     * @SWG\Tag(name="forum")
     */
    public function postPostAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $post = new ForumPost();
        $post->setAuthor($this->getUser());

        return $this->processForm($post, $request, PostType::class, 'get_forum_post');
    }

    /**
     * @Rest\Put(name="put_forum_post", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Updates a forum post"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The post id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function putPostAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository('ForumBundle:Post')->find($id);

        if (null === $post) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $post->getAuthor()) {
            throw $this->createAccessDeniedException('This post does not belong to you');
        }

        return $this->processForm($post, $request, PostType::class);
    }

    /**
     * @Rest\Delete(name="delete_forum_post", options={ "method_prefix" = false })
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Deletes a forum post"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The post id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="forum")
     */
    public function deletePostAction($id)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em = $this->getDoctrine()->getManager();

        $post = $em->getRepository('ForumBundle:Post')->find($id);

        if (null === $post) {
            throw $this->createNotFoundException();
        }

        if (!$this->isGranted('ROLE_ADMIN') && $this->getUser() !== $post->getAuthor()) {
            throw $this->createAccessDeniedException('This post does not belong to you');
        }

        $em->remove($post);
        $em->flush();

        return null;
    }
}
