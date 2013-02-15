<?php

namespace Samson\Bundle\FilterBundle\Form\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Event\DataEvent;
use Samson\Bundle\FilterBundle\Filter\Filter;
use Symfony\Component\Form\Event\FilterDataEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PresetListener implements EventSubscriberInterface
{
    private $filter;

    private $request;

    public function __construct(Request $request, Filter $filter)
    {
        $this->request = $request;
        $this->filter = $filter;
    }

    static public function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => array('preSetData', -100),
            FormEvents::POST_BIND => 'postBind',
        );
    }

    public function preSetData(FilterDataEvent $event)
    {
        $filterDataForm = $event->getForm()->get('data');

        if ($this->request->getMethod() == 'POST' && $this->request->request->has($event->getForm()->getName())) {
            $data = $this->request->request->get($event->getForm()->getName());
            $filterValues = $this->filter->getFilterValuesForCurrentUser($filterDataForm);

            if (isset($data['loadPreset'])) {
                $this->request->request->remove($event->getForm()->getName());

                $setData = array();
                $setData['remember'] = $filterValues->getRemember();

                $presetChoicelist = $this->filter->getPresetChoiceList($filterDataForm);
                $values = $presetChoicelist->getChoicesForValues(array($data['preset']));

                $setData['data'] = $this->filter->deserialize($this->filter->loadPreset($filterDataForm, $values[0]));

                $event->setData($setData);
                $this->filter->saveFilterValues($setData, $filterDataForm);

                $response = new RedirectResponse($this->request->getRequestUri());
                throw new \Samson\Bundle\CoreBundle\Exception\UnexpectedResponseException($response);
            }
        }
    }

    public function getPresetName($form, $value)
    {
        foreach (array_reverse($form->getClientTransformers()) as $transformer) {
            $value = $transformer->reverseTransform($value);
        }

        return $value;
    }

    public function preBind(DataEvent $event)
    {
        $data = $event->getData();
    }

    public function postBind(DataEvent $event)
    {
        $filterDataForm = $event->getForm()->get('data');
        $data = $event->getData();
        if ($data['savePreset']) {
            $filterValues = $this->filter->getFilterValuesForCurrentUser($filterDataForm);
            $this->filter->savePreset($filterDataForm, $filterValues);
        }
    }
}