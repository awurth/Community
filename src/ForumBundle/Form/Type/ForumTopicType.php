<?php

namespace ForumBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ForumTopicType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('forum');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'forum_forum_topic';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TopicType::class;
    }
}
