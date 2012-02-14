<?php

namespace Samson\Bundle\FilterBundle\Controller;

use Samson\Bundle\FrameworkBundle\Dialog\CloseDialogResponse;
use Samson\Bundle\FilterBundle\Form\FilterPresetType;
use Symfony\Component\Form\FormError;
use Samson\Bundle\FilterBundle\Form\SaveFilterPresetType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/filter")
 */
class FilterController extends Controller
{

    /**
     * @Route("/save_preset", name="filter_savePreset")
     * @Template
     */
    public function savePresetAction()
    {
        $session = $this->get('session');
        if (!$session->has('filter_preset') && !$this->getRequest()->getMethod() == 'POST') {
            throw $this->createNotFoundException('A filter preset was not encountered in the session!');
        }
        $filterPreset = array();

        if ($session->has('filter_preset')) {
            $filterPreset = $session->get('filter_preset');
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $filterPreset->setUser($user);

        $form = $this->createForm(new SaveFilterPresetType(), $filterPreset);
        $returnPath = $this->getRequest()->query->get('return_path');

        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                // Extra check vanwege https://github.com/symfony/symfony/issues/1635
                // http://stackoverflow.com/questions/4619071/doctrine2-findby-relationship-object-triggers-string-conversion-error
                $existingPreset = $em->getRepository('SamsonCoreBundle:FilterPreset')->findOneBy(array(
                    'name' => $filterPreset->getName(),
                    'filterType' => $filterPreset->getFilterType(),
                    'user' => $filterPreset->getUser()->getId()
                    ));
                if (null !== $existingPreset) {
                    $error = new FormError('There already is a preset defined for this user and filter with the name "{{ name }}"', array('{{ name }}' => $filterPreset->getName()));
                    $form->addError($error);
                } else {
                    $em->persist($filterPreset);
                    $em->flush();

                    return new CloseDialogResponse($returnPath);
                }
            }
        }

        return array('form' => $form->createView(), 'return_path' => $returnPath);
    }

    /**
     * @Route("/manage_presets", name="filter_managePresets")
     * @Template
     */
    public function managePresetsAction()
    {
        $filterType = $this->getRequest()->query->get('filterType');

        $filter = $this->get('samson.filter');

        $user = $this->get('security.context')->getToken()->getUser();

        $presets = array();
        foreach ($filter->getPresetsForUser(new $filterType, $user) as $preset) {
            if (!$preset->isFixed()) {
                $presets[] = $preset;
            }
        }
        $form = $this->createForm('editable_grid', $presets, array(
            'type' => new FilterPresetType(),
            'allow_delete' => true
            ));

        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $this->get('doctrine')->getEntityManager()->flush();
                $this->redirect($this->get('router')->generate('filter_managePresets', array('filterType' => $filterType)));
            }
        }

        return array('filterType' => $filterType, 'form' => $form->createView());
    }
}