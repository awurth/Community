<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tag;
use AppBundle\Form\Type\TagType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class TagController extends RestController
{
    /**
     * @Rest\View
     *
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
     *     description="Max number of tags per page"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all tags",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Tag::class)
     *     )
     * )
     * @SWG\Tag(name="app")
     */
    public function getTagsAction(ParamFetcher $paramFetcher)
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Tag')
            ->getCollection(
                $paramFetcher->get('per_page'),
                $paramFetcher->get('page'),
                $paramFetcher->get('order')
            );
    }

    /**
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets a tag by it's id",
     *     @Model(type=Tag::class)
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The tag id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="app")
     */
    public function getTagAction($id)
    {
        $tag = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Tag')
            ->find($id);

        if (null === $tag) {
            throw $this->createNotFoundException();
        }

        return $tag;
    }

    /**
     * @Rest\View
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a new tag"
     * )
     * @SWG\Tag(name="app")
     */
    public function postTagAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_AUTHOR');

        return $this->processForm(new Tag(), $request, TagType::class, 'get_tag');
    }

    /**
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Updates a tag"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The tag id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="app")
     */
    public function putTagAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_AUTHOR');

        $tag = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:Tag')
            ->find($id);

        if (null === $tag) {
            throw $this->createNotFoundException();
        }

        return $this->processForm($tag, $request, TagType::class);
    }

    /**
     * @Rest\View
     *
     * @SWG\Response(
     *     response=204,
     *     description="Deletes a tag"
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The tag id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="app")
     */
    public function deleteTagAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_AUTHOR');

        $em = $this->getDoctrine()->getManager();

        $tag = $em->getRepository('AppBundle:Tag')->find($id);

        if (null === $tag) {
            throw $this->createNotFoundException();
        }

        $em->remove($tag);
        $em->flush();

        return null;
    }
}
