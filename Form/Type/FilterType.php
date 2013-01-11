<?php

namespace Samson\Bundle\FilterBundle\Form\Type;

use Samson\Bundle\FilterBundle\Filter\Filter;
use Samson\Bundle\FilterBundle\Form\EventListener\PresetListener;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
        $filter = $this->filter;
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

        $request = $this->container->get('request');
        $presetListener = new PresetListener($request, $filter);
        $builder->addEventSubscriber($presetListener);
        $factory = $builder->getFormFactory();
        $builder->addEventListener(FormEvents::SET_DATA, function(FormEvent $e) use ($factory, $options, $filter) {
                $dataForm = $factory->createNamed('data', $options['filter_type'], null, array(
                    'data_class' => get_class($options['filter_data'])
                    ));


                $childOptions = $dataForm->getConfig()->getOptions();

                if (isset($childOptions['data']) && null !== $childOptions['data']) {
                    $filterData = $childOptions['data'];
                } else {
                    $filterData = $options['filter_data'];
                }

                $rememberedData = $filter->getFilterValuesForCurrentUser($dataForm);
                if (null !== $rememberedData) {

                    if ($rememberedData->getRemember()) {
                        $filterData = $filter->deserialize($rememberedData->getData());
                    }
                }

                // HACK: Not sure why, but without this, the form, though filtering correctly, will show up empty
                $refl = new \ReflectionProperty('Symfony\Component\Form\FormConfigBuilder', 'dataLocked');
                $refl->setAccessible(true);
                $refl->setValue($dataForm->getConfig(), false);
                $dataForm->setData($filterData);
                $refl->setValue($dataForm->getConfig(), true);
                // /HACK

                $e->getForm()->add($dataForm);

                $choiceList = $filter->getPresetChoiceList($e->getForm()->get('data'));
                $e->getForm()->add($factory->createNamed('preset', 'choice', null, array(
                        'choice_list' => $choiceList,
                        'required' => true
                    )));
                $e->getForm()->add($factory->createNamed('savePreset', 'hidden'));
                $e->getForm()->add($factory->createNamed('loadPreset', 'hidden'));

                $e->setData(array('data' => $filterData));
            });


        if ($options['use_remember']) {
            $builder->add('remember', 'checkbox', array(
                'data' => true,
                'required' => false
            ));
        }

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $e) use ($filter, $filterType) {
                $data = $e->getData();
                $filter->saveFilterValues($data, $e->getForm()->get('data'));
            });

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