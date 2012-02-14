<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Doctrine\ORM\Query\Expr;
use Samson\Bundle\FilterBundle\Filter\FieldFilter;

class IntegerFilter extends FieldFilter
{

    public function filter($field, $value, Integer $integerSearch)
    {
        if ($value === '' || null === $value) {
            return;
        }
        $typeMap = array(
            'equals' => "=",
            'is less than' => "<",
            "is less than or equal to" => "<=",
            "is greater than" => ">",
            "is greater than or equal to" => ">=",
            "is not equal to" => "<>"
        );

        $type = $integerSearch->type;
        if (in_array($integerSearch->type, array_keys($typeMap))) {
            $type = $typeMap[$type];
        }

        if (!in_array($type, array_values($typeMap))) {
            throw new \InvalidArgumentException('Type should be one of '.implode(',', array_merge(array_keys($typeMap), array_values($typeMap))).', not '.$integerSearch->type);
        }
        $parameter = $value;


        $parameterName = $this->generateParameterName();

        return array(new Expr\Comparison($field, $type, ':'.$parameterName), array($parameterName => $parameter));
    }
}