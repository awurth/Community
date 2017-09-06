<?php

namespace ForumBundle\Controller;

use AppBundle\Controller\RestController;
use ForumBundle\Entity\Post as ForumPost;
use ForumBundle\Form\Type\PostType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Entity\User;

class PostController extends RestController
{
    /**
     * @Rest\Get(name="get_forum_posts", options={ "method_prefix" = false })
     * @Rest\View
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
    public function getPostsAction()
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('ForumBundle:Post')
            ->findAll();
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

        return $this->processForm(new ForumPost(), $request, PostType::class, 'get_forum_post');
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

        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN') && $user !== $post->getAuthor()) {
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

        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN') && $user !== $post->getAuthor()) {
            throw $this->createAccessDeniedException('This post does not belong to you');
        }

        $em->remove($post);
        $em->flush();

        return null;
    }
}
