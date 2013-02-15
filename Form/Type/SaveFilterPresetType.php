<?php

namespace Samson\Bundle\FilterBundle\Form\Type;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class SaveFilterPresetType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('public', null, array('required' => false))
            ->add('filterType', 'hidden')
            ->add('data', 'hidden');

        $builder->get('data')->addViewTransformer(new CallbackTransformer(function($value) {
            return base64_encode(serialize($value));
        }, function($value) {
            return unserialize(base64_decode($value));
        }));
    }

    public function getName()
    {
        return 'save_preset';
    }
}