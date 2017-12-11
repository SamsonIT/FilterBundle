<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Doctrine\Entity;
use Samson\Bundle\FilterBundle\Filter\FieldSearch;
use Doctrine\ORM\Query\Expr;
use Samson\Bundle\FilterBundle\Filter\FieldFilter;

class ChoiceListFilter extends FieldFilter
{

    public function filter($field, $value, ChoiceListFieldSearch $stringSearch)
    {
        if (null === $value) {
            return;
        }

        $parameterName = $this->generateParameterName();
        if (!count($value)) {
            return null;
        }
        
        if ($value instanceof \Doctrine\Common\Collections\Collection ){
            $value = $value->toArray();
        } else if (!is_array($value)) {
            $value = [$value];
        }
        
        return array(new Expr\Func($field.' IN', ':'.$parameterName), array($parameterName => array_values($value)));
    }
}