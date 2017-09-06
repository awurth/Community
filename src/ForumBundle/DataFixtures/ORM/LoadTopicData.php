<?php

namespace ForumBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ForumBundle\Entity\Forum;
use ForumBundle\Entity\Topic;

class LoadTopicData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 3; $i++) {
            for ($j = 1; $j <= 3; $j++) {
                /** @var Forum $forum */
                $forum = $this->getReference("category-$i-forum-$j");

                for ($k = 1; $k <= 3; $k++) {
                    $topic = new Topic();
                    $topic->setTitle("Category $i - Forum $j - Topic $k");
                    $topic->setDescription("This is the topic $k of forum $j of category $i");
                    $topic->setForum($forum);

                    $manager->persist($topic);

                    $this->addReference("category-$i-forum-$j-topic-$k", $topic);
                }
            }
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4;
    }
}
