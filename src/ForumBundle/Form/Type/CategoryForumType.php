<?php

namespace ForumBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryForumType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('category');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'forum_category_forum';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ForumType::class;
    }
}
