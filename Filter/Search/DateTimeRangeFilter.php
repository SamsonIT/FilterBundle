<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Doctrine\ORM\Query\Expr;
use Samson\Bundle\FilterBundle\Filter\FieldFilter;

class DateTimeRangeFilter extends FieldFilter
{

    public function filter($field, $value, DateTimeRangeFieldSearch $stringSearch)
    {

        if (@$value['datetime_start'] && @$value['datetime_end']) {
            $parameterName1 = $this->generateParameterName();
            $parameterName2 = $this->generateParameterName();

            return array("$field BETWEEN :$parameterName1 AND :$parameterName2", array($parameterName1 => $value['datetime_start'], $parameterName2 => $value['datetime_end']));
        } else if (@$value['datetime_start']) {
            $parameterName1 = $this->generateParameterName();
            return array(new Expr\Comparison($field, '>=', ':'.$parameterName1), array($parameterName1 => $value['datetime_start']));
        } else if (@$value['datetime_end']) {
            $parameterName1 = $this->generateParameterName();
            return array(new Expr\Comparison($field, '<=', ':'.$parameterName1), array($parameterName1 => $value['datetime_end']));
        }

        else
            return null;
    }
}