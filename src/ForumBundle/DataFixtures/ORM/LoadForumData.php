<?php

namespace ForumBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ForumBundle\Entity\Category;
use ForumBundle\Entity\Forum;

class LoadForumData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 3; $i++) {
            for ($j = 1; $j <= 3; $j++) {
                /** @var Category $category */
                $category = $this->getReference("category-$i");

                $forum = new Forum();
                $forum->setTitle("Category $i - Forum $j");
                $forum->setDescription("This is the forum $j of category $i");
                $forum->setCategory($category);

                $manager->persist($forum);

                $this->addReference("category-$i-forum-$j", $forum);
            }
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3;
    }
}
