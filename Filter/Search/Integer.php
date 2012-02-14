<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Samson\Bundle\FilterBundle\Filter\FieldSearch;

/**
 * @Annotation
 */
class Integer extends FieldSearch
{
    public $type = 'equals';

    public function getDefaultValue()
    {
        return 'type';
    }
}