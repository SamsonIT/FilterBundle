<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Doctrine\ORM\Query\Expr;
use Samson\Bundle\FilterBundle\Filter\FieldFilter;

class BooleanFilter extends FieldFilter
{

    public function filter($field, $value, BooleanFieldSearch $booleanSearch)
    {
        if (null === $value) {
            return;
        }

        if ($booleanSearch->skiponfalse && $value === false) {
            return;
        }

        $parameterName = $this->generateParameterName();

        if ($value === true) {
            $value = true;
        } elseif ($value === false) {
            $value = false;
        } else {
            $value = (boolean) $value;
        }
        return array(new Expr\Comparison($field, '=', ':'.$parameterName), array($parameterName => $value));
    }
}