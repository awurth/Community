<?php

namespace ForumBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ForumBundle\Entity\Post;
use ForumBundle\Entity\Topic;
use UserBundle\Entity\User;

class LoadPostData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $author */
        $author = $this->getReference('user');

        for ($i = 1; $i <= 3; $i++) {
            for ($j = 1; $j <= 3; $j++) {
                for ($k = 1; $k <= 3; $k++) {
                    /** @var Topic $topic */
                    $topic = $this->getReference("category-$i-forum-$j-topic-$k");

                    for ($l = 1; $l <= 3; $l++) {
                        $post = new Post();
                        $post->setContent("This is the post $l of topic $k of forum $j of category $i");
                        $post->setTopic($topic);
                        $post->setAuthor($author);

                        $manager->persist($post);

                        $this->addReference("category-$i-forum-$j-topic-$k-post-$l", $post);
                    }
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
        return 5;
    }
}
