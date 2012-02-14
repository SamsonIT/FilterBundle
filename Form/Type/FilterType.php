<?php

namespace Samson\Bundle\FilterBundle\Form\Type;

use Samson\Bundle\FilterBundle\Filter\Filter;
use Samson\Bundle\FilterBundle\Form\EventListener\PresetListener;
use Samson\Bundle\FilterBundle\Form\EventListener\RememberListener;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\FilterDataEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormViewInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FilterType extends AbstractType
{
    private $filter;

    public function __construct(Filter $filter, Container $container, array $config)
    {
        $this->filter = $filter;
        $this->container = $container;
        $this->config = $config;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $filterType = $options['filter_type'];

        $filterData = $options['filter_data'];

        if (is_string($filterType)) {
            $filterType = $builder->getFormFactory()->getType($filterType);
        }

        $def = $filterType->getDefaultOptions(array());
        if (isset($def['data']) && null !== $def['data']) {
            $filterData = $def['data'];
        } else {
            $filterData = $options['filter_data'];
        }

        $builder->add('data', $filterType, array('data' => $filterData, 'data_class' => get_class($options['filter_data'])));

        if ($options['use_remember']) {
            $builder->add('remember', 'checkbox', array(
                'data' => true,
                'required' => false
            ));
        }
        $builder->get('data')->addEventSubscriber(new RememberListener($this->container->get('request'), $this->filter, $filterType));

        if ($options['use_preset']) {
            $choiceList = $this->filter->getPresetChoiceList($filterType);

            $builder->add('preset', 'choice', array(
                'choice_list' => $choiceList,
                'required' => true
            ));

            $builder->add('savePreset', 'hidden');
            $builder->add('loadPreset', 'hidden');

            $listener = new PresetListener($this->container->get('request'), $choiceList, $this->filter, $filterType);
            $builder->addEventSubscriber($listener);
        }

        $filter = $this->filter;
        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event) use ($filter, $filterType) {
                $data = $event->getData();
                $filter->saveFilterValues($data, $filterType);
            });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FilterDataEvent $e) use ($filterData) {
                $data = $e->getData();

                $data['data'] = $filterData;
                $e->setData($data);
            }, 256);

        $builder->setAttribute('filterType', get_class($filterType));
    }

    public function getParent()
    {
        return 'form';
    }

    public function getName()
    {
        return 'filter';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'filter_type' => null,
            'filter_data' => null,
            'use_remember' => $this->config['use_remember'],
            'use_preset' => $this->config['use_preset']
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['filterType'] = $form->getAttribute('filterType');
    }
}