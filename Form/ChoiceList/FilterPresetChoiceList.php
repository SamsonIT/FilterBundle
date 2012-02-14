<?php

namespace Samson\Bundle\FilterBundle\Form\ChoiceList;

use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;

class FilterPresetChoiceList extends SimpleChoiceList
{

    public function __construct(array $presets)
    {

        $choices = array(
            '_reset_' => '[reset]',
        );

        $private = $public = $fixed = array();

        foreach ($presets as $preset) {
            if ($preset->isFixed()) {
                $fixed[$preset->getName()] = $preset->getName();
            } elseif ($preset->isPublic()) {
                $public[$preset->getId()] = $preset->getName();
            } else {
                $private[$preset->getId()] = $preset->getName();
            }
        }

        if (count($fixed)) {
            $choices['predefined'] = $fixed;
        }

        if (count($public)) {
            $choices['public'] = $public;
        }
        if (count($private)) {
            $choices['private'] = $private;
        }

        parent::__construct($choices);
    }
}
