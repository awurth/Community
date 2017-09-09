<?php

namespace ForumBundle\Repository;

use AppBundle\Repository\PaginationRepository;

class TopicRepository extends PaginationRepository
{
    /**
     * Gets a paginated list of Topics.
     *
     * @param int    $perPage
     * @param int    $page
     * @param string $order
     *
     * @return array
     */
    public function getCollection($perPage = 15, $page = 1, $order = 'asc')
    {
        $qb = $this->createQueryBuilder('t')
            ->orderBy('t.id', $order);

        $qb = $this->paginate($qb, $perPage, $page);

        return $qb->getQuery()->getResult();
    }
}
