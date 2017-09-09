<?php

namespace ForumBundle\Repository;

use AppBundle\Repository\PaginationRepository;
use ForumBundle\Entity\Forum;

class ForumRepository extends PaginationRepository
{
    /**
     * Gets a paginated list of forums.
     *
     * @param int    $perPage
     * @param int    $page
     * @param string $order
     *
     * @return array
     */
    public function getCollection($perPage = 15, $page = 1, $order = 'asc')
    {
        $qb = $this->createQueryBuilder('f')
            ->orderBy('f.id', $order);

        $qb = $this->paginate($qb, $perPage, $page);

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets a paginated list of forums of the given category.
     *
     * @param string $categoryId
     * @param int    $perPage
     * @param int    $page
     * @param string $order
     *
     * @return array
     */
    public function getByCategory($categoryId, $perPage = 15, $page = 1, $order = 'asc')
    {
        $qb = $this->createQueryBuilder('f')
            ->where('f.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('f.id', $order);

        $qb = $this->paginate($qb, $perPage, $page);

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets a forum by it's category and id.
     *
     * @param string|int $categoryId
     * @param string|int $forumId
     *
     * @return Forum|null
     */
    public function getOneByCategory($categoryId, $forumId)
    {
        return $this->createQueryBuilder('f')
            ->where('f.category = :categoryId')
            ->andWhere('f.id = :forumId')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('forumId', $forumId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
