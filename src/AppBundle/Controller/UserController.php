<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

class UserController extends RestController
{
    /**
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all users",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=User::class)
     *     )
     * )
     * @SWG\Tag(name="user")
     */
    public function getUsersAction()
    {
        return $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
            ->findAll();
    }

    /**
     * @Rest\View
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets a user by it's id",
     *     @Model(type=User::class)
     * )
     * @SWG\Parameter(
     *     name="id",
     *     description="The user id",
     *     in="path",
     *     type="integer",
     *     required=true
     * )
     * @SWG\Tag(name="user")
     */
    public function getUserAction($id)
    {
        $user = $this->getDoctrine()
            ->getManager()
            ->getRepository('AppBundle:User')
            ->find($id);

        if (null === $user) {
            throw $this->createNotFoundException();
        }

        return $user;
    }
}
