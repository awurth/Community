<?php

namespace ForumBundle\Repository;

use AppBundle\Repository\PaginationRepository;
use ForumBundle\Entity\Post;

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

    /**
     * Gets a paginated list of posts of the given topic.
     *
     * @param string $topicId
     * @param int    $perPage
     * @param int    $page
     * @param string $order
     *
     * @return array
     */
    public function getByTopic($topicId, $perPage = 15, $page = 1, $order = 'asc')
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.topic = :topicId')
            ->setParameter('topicId', $topicId)
            ->orderBy('p.id', $order);

        $qb = $this->paginate($qb, $perPage, $page);

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets a post by it's topic and id.
     *
     * @param string|int $topicId
     * @param string|int $postId
     *
     * @return Post|null
     */
    public function getOneByTopic($topicId, $postId)
    {
        return $this->createQueryBuilder('p')
            ->where('p.topic = :topicId')
            ->andWhere('p.id = :postId')
            ->setParameter('topicId', $topicId)
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
