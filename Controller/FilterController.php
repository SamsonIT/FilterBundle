<?php

namespace Samson\Bundle\FilterBundle\Controller;

use Samson\Bundle\FrameworkBundle\Dialog\CloseDialogResponse;
use Samson\Bundle\FilterBundle\Form\Type\FilterPresetType;
use Symfony\Component\Form\FormError;
use Samson\Bundle\FilterBundle\Form\Type\SaveFilterPresetType;
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

        if ($form->handleRequest($this->getRequest())->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($filterPreset);
            $em->flush();

            return new CloseDialogResponse($returnPath);
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
        $filterType = new $filterType;

        $filter = $this->get('samson.filter');

        $user = $this->get('security.context')->getToken()->getUser();

        $presets = $filter->getConfiguredPresetsForUser($filterType->getName(), $user);

        $form = $this->createForm(
            'editable_grid',
            $presets,
            array(
                'type' => new FilterPresetType(),
                'allow_delete' => true
            )
        );

        if ($form->handleRequest($this->getRequest())->isValid()) {
            $this->get('doctrine')->getManager()->flush();
            $this->redirect(
                $this->get('router')->generate('filter_managePresets', array('filterType' => $filterType))
            );
        }

        return array('filterType' => get_class($filterType), 'form' => $form->createView());
    }
}
