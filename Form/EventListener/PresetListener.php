<?php

namespace Samson\Bundle\FilterBundle\Form\EventListener;

use Doctrine\Common\Util\Debug;
use Samson\Bundle\CoreBundle\Entity\FilterValues;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Samson\Bundle\AddressBookBundle\Form\Filter\CompanyFilter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Event\DataEvent;
use Samson\Bundle\FilterBundle\Filter\Filter;
use Symfony\Component\Form\Event\FilterDataEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Exception\InvalidPropertyException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

class PresetListener implements EventSubscriberInterface
{
    private $filter;

    private $filterType;

    private $request;

    private $presetChoicelist;

    public function __construct(Request $request, ChoiceListInterface $presetChoicelist, Filter $filter, $filterType)
    {
        $this->request = $request;
        $this->filter = $filter;
        $this->filterType = $filterType;
        $this->presetChoicelist = $presetChoicelist;
    }

    static public function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_BIND => 'postBind',
        );
    }

    public function preSetData(FilterDataEvent $event)
    {
        if ($this->request->getMethod() == 'POST' && $this->request->request->has($event->getForm()->getName())) {
            $data = $this->request->request->get($event->getForm()->getName());
            $filterValues = $this->filter->getFilterValuesForCurrentUser($this->filterType);

            if (isset($data['loadPreset'])) {
                $this->request->request->remove($event->getForm()->getName());

                $setData = array();
                $setData['remember'] = $filterValues->getRemember();

                $values = $this->presetChoicelist->getChoicesForValues(array($data['preset']));

                $setData['data'] = $this->filter->deserialize($this->filter->loadPreset($this->filterType, $values[0]));

                $event->setData($setData);
                $this->filter->saveFilterValues($setData, $this->filterType);
                
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
        $data = $event->getData();
        if ($data['savePreset']) {
            $filterValues = $this->filter->getFilterValuesForCurrentUser($this->filterType);
            $this->filter->savePreset($this->filterType, $filterValues);
        }
    }
}