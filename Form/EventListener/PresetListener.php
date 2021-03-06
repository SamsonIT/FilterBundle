<?php

namespace Samson\Bundle\FilterBundle\Form\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormEvent;
use Samson\Bundle\FilterBundle\Filter\Filter;
use Samson\Bundle\UnexpectedResponseBundle\Exception\UnexpectedResponseException;
use Symfony\Component\Form\Event\FilterDataEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Automatically handle persisting and flushing saved filtervalues when a form is submitted.
 *
 * Class PresetListener
 * @package Samson\Bundle\FilterBundle\Form\EventListener
 */
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
//            FormEvents::POST_BIND => 'postBind',
        );
    }

    /**
     * Load pre-set choice lists for forms.
     * Intercepts the current request if it's a POST request for the form
     * Saves the search parameters as new pre-set filters.
     *
     * !! Warning !!
     * If your form contains a 'reset' key, then this function will ABORT your current request,
     * resets the form parameters (basically clear it) and HTTP 302 redirect back to the page that's initiated the
     * POST.
     *
     * @throws UnexpectedResponseException RedirectResponse to the current request uri if there is a 'reset' parameter
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $filterDataForm = $event->getForm()->get('data');

        if ($this->request->getMethod() == 'POST' && $this->request->request->has($event->getForm()->getName())) {
            $data = $this->request->request->get($event->getForm()->getName());
            $filterValues = $this->filter->getFilterValuesForCurrentUser($filterDataForm);

            if (isset($data['reset'])) {
                $data['loadPreset'] = 1;
                $data['preset'] = '_reset_';
            }

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
                throw new UnexpectedResponseException($response);
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

    public function preBind(FormEvent $event)
    {
        $data = $event->getData();
    }

    public function postBind(FormEvent $event)
    {
        $filterDataForm = $event->getForm()->get('data');
        $data = $event->getData();
        if ($data['savePreset']) {
            $filterValues = $this->filter->getFilterValuesForCurrentUser($filterDataForm);
            $this->filter->savePreset($filterDataForm, $filterValues);
        }
    }
}
