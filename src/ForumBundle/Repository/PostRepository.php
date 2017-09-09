<?php

namespace ForumBundle\Repository;

use AppBundle\Repository\PaginationRepository;

class PostRepository extends PaginationRepository
{
    /**
     * Gets a paginated list of Posts.
     *
     * @param int    $perPage
     * @param int    $page
     * @param string $order
     *
     * @return array
     */
    public function getCollection($perPage = 15, $page = 1, $order = 'asc')
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.id', $order);

        $qb = $this->paginate($qb, $perPage, $page);

        return $qb->getQuery()->getResult();
    }
}
