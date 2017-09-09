<?php

namespace ForumBundle\Repository;

use AppBundle\Repository\PaginationRepository;
use ForumBundle\Entity\Topic;

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

    /**
     * Gets a paginated list of topics of the given forum.
     *
     * @param string $forumId
     * @param int    $perPage
     * @param int    $page
     * @param string $order
     *
     * @return array
     */
    public function getByForum($forumId, $perPage = 15, $page = 1, $order = 'asc')
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.forum = :forumId')
            ->setParameter('forumId', $forumId)
            ->orderBy('t.id', $order);

        $qb = $this->paginate($qb, $perPage, $page);

        return $qb->getQuery()->getResult();
    }

    /**
     * Gets a topic by it's forum and id.
     *
     * @param string|int $forumId
     * @param string|int $topicId
     *
     * @return Topic|null
     */
    public function getOneByForum($forumId, $topicId)
    {
        return $this->createQueryBuilder('t')
            ->where('t.forum = :forumId')
            ->andWhere('t.id = :topicId')
            ->setParameter('forumId', $forumId)
            ->setParameter('topicId', $topicId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
