<?php

namespace NewsBundle\Repository;

use AppBundle\Repository\PaginationRepository;

class CategoryRepository extends PaginationRepository
{
    /**
     * Gets a paginated list of Categories.
     *
     * @param int    $perPage
     * @param int    $page
     * @param string $order
     *
     * @return array
     */
    public function getCollection($perPage = 15, $page = 1, $order = 'asc')
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.id', $order);

        $qb = $this->paginate($qb, $perPage, $page);

        return $qb->getQuery()->getResult();
    }
}
