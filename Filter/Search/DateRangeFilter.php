<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Doctrine\ORM\Query\Expr;
use Samson\Bundle\FilterBundle\Filter\FieldFilter;

class DateRangeFilter extends FieldFilter
{

    public function filter($field, $value, DateRangeFieldSearch $stringSearch)
    {
        $start = $end = null;
        if (null !== $value) {
            $start = array_key_exists('date_start', $value) ? $value['date_start'] : null;
            $end = array_key_exists('date_end', $value) ? $value['date_end'] : null;
        }

        if ($start !== null && $end !== null) {
            $parameterName1 = $this->generateParameterName();
            $parameterName2 = $this->generateParameterName();

            return array("DATE($field) BETWEEN DATE(:$parameterName1) AND DATE(:$parameterName2)", array($parameterName1 => $value['date_start'], $parameterName2 => $value['date_end']));
        } else if ($start !== null) {
            $parameterName1 = $this->generateParameterName();
            return array(new Expr\Comparison('DATE('.$field.')', '>=', ':'.$parameterName1), array($parameterName1 => $value['date_start']));
        } else if ($end !== null) {
            $parameterName1 = $this->generateParameterName();
            return array(new Expr\Comparison('DATE('.$field.')', '<=', ':'.$parameterName1), array($parameterName1 => $value['date_end']));
        } else {
            return null;
        }
    }
}