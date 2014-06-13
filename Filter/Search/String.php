<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Samson\Bundle\FilterBundle\Filter\FieldSearch;

/**
 * @Annotation
 */
class String extends FieldSearch
{
    public $type = 'contains';

    public $multiterm = false;

    public function getDefaultValue()
    {
        return 'type';
    }
}