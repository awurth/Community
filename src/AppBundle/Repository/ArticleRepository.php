<?php

namespace AppBundle\Repository;

class ArticleRepository extends PaginationRepository
{
    /**
     * Gets a paginated list of Articles.
     *
     * @param int    $perPage
     * @param int    $page
     * @param string $order
     *
     * @return array
     */
    public function getCollection($perPage = 15, $page = 1, $order = 'asc')
    {
        $qb = $this->createQueryBuilder('a')
            ->orderBy('a.id', $order);

        $qb = $this->paginate($qb, $perPage, $page);

        return $qb->getQuery()->getResult();
    }
}
