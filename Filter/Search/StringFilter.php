<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Doctrine\ORM\Query\Expr;
use Samson\Bundle\FilterBundle\Filter\FieldFilter;

class StringFilter extends FieldFilter
{

    public function filter($field, $value, String $stringSearch)
    {
        if ($value == '' || null === $value) {
            return;
        }
        $validTypes = array('begins_with', 'contains', 'ends_with', 'equals');

        if (!in_array($stringSearch->type, $validTypes)) {
            throw new \InvalidArgumentException('Type should be one of '.implode(',', $validTypes));
        }
        $parameter = $value;
        $parameterName = $this->generateParameterName();

        if ($stringSearch->type == 'equals') {
            return array(new Expr\Comparison($field, '=', ':'.$parameterName), array($parameterName => $parameter));
        }

        switch ($stringSearch->type) {
            case 'begins_with':
                $parameter = "$value%";
                break;
            case 'ends_with':
                $parameter = "%$value";
                break;
            case 'contains':
                $parameter = "%$value%";
                break;
        }


        return array(new Expr\Comparison($field, 'LIKE', ':'.$parameterName), array($parameterName => $parameter));
    }
}