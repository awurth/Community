<?php

namespace ForumBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TopicPostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('topic');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'forum_topic_post';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return PostType::class;
    }
}
