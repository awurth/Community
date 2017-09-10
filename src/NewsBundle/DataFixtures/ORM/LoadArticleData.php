<?php

namespace NewsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use NewsBundle\Entity\Article;
use NewsBundle\Entity\Category;
use UserBundle\Entity\User;

class LoadArticleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $admin */
        $admin = $this->getReference('admin');

        for ($i = 1; $i <= 3; $i++) {
            /** @var Category $category */
            $category = $this->getReference("news-category-$i");

            for ($j = 1; $j <= 3; $j++) {
                $article = new Article();
                $article->setTitle("Category $i - Article $j");
                $article->setContent("This is the article $j of category $i");
                $article->setCategory($category);
                $article->setAuthor($admin);

                $manager->persist($article);

                $this->addReference("news-category-$i-article-$j", $article);
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
