<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Doctrine\ORM\Query\Expr;
use Samson\Bundle\FilterBundle\Filter\FieldFilter;

class DateFilter extends IntegerFilter
{

    public function filter($field, $value, Integer $integerSearch)
    {
        return parent::filter('DATE('.$field.')', $value, $integerSearch);
    }
}