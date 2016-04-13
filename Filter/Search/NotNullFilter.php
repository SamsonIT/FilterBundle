<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Doctrine\ORM\Query\Expr;
use Samson\Bundle\FilterBundle\Filter\FieldFilter;

class NotNullFilter extends FieldFilter
{

    public function filter($field, $value, NotNullFieldSearch $notNullSearch)
    {
        if($value === null){
            return null;
        }
        if ($notNullSearch->skiponfalse && $value == false) {
            return;
        }
        // FIXME: filter should send true/false but currently sends 0/1 instead?
        if ($value == false) {
            return array(new Expr\Comparison($field, 'IS', 'NULL'), array());
        }
        return array(new Expr\Comparison($field, 'IS', 'NOT NULL'), array());
    }
}