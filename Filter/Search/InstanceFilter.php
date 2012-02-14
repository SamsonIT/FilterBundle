<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Doctrine\ORM\Query\Expr;
use Samson\Bundle\FilterBundle\Filter\FieldFilter;

class InstanceFilter extends FieldFilter
{

    public function filter($field, $value)
    {

        if (!count($value)) {
            return null;
        }
        
        $aliasAndField = explode(".", $field);
        $alias = $aliasAndField[0];

        if (is_array($value)) {
            $instances = implode(",", $value);
        } else {
            $instances = $value;
        }
        return array(new Expr\Orx($alias." INSTANCE OF ( $instances )"), array());
    }
}