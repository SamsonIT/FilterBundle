<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Samson\Bundle\FilterBundle\Filter\FieldSearch;

/**
 * @Annotation
 */
class CallbackFieldSearch extends FieldSearch
{
    public $callback = null;
    
    public function getDefaultValue()
    {
        return 'callback';
    }
}