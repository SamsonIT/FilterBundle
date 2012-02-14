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

class RememberListener implements EventSubscriberInterface
{
    private $filter;

    private $filterType;

    private $request;

    public function __construct(Request $request, Filter $filter, $filterType)
    {
        $this->request = $request;
        $this->filter = $filter;
        $this->filterType = $filterType;
    }

    static public function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData'
        );
    }

    public function preSetData(FilterDataEvent $event)
    {
        if (null !== $event->getData()) {
            $setData = $event->getData();
        } else {
            $setData = array();
        }

        $data = $this->filter->getFilterValuesForCurrentUser($this->filterType);
        if (null !== $data) {

            if ($data->getRemember()) {
                $filterValues = $this->filter->deserialize($data->getData());
                if ($data->getData() !== null) {
                    $setData = $filterValues;
                }
            }

        }
        $event->setData($setData);
    }

}