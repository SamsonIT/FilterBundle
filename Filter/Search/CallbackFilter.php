<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Doctrine\ORM\Query\Expr;
use Samson\Bundle\FilterBundle\Filter\FieldFilter;

class CallbackFilter extends FieldFilter
{

    public function filter($field, $value, CallbackFieldSearch $callbackSearch)
    {
        $callback = $callbackSearch->callback;

        $filter = $this;
        $parameterNameGenerator = function() use ($filter) {
            return $filter->generateParameterName();
        };
        
        return call_user_func($callback, $this->getQueryBuilder(), $value, $parameterNameGenerator);
    }
}