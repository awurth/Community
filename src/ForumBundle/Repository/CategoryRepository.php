<?php

namespace ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function getCollection($perPage = 15, $page = 1, $order = 'asc')
    {
        if ($perPage < 1) {
            $perPage = 15;
        }

        if ($page < 1) {
            $page = 1;
        }

        return $this->createQueryBuilder('c')
            ->orderBy('c.id', $order)
            ->setFirstResult($perPage * $page - $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }
}
