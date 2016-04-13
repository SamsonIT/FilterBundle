<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Samson\Bundle\FilterBundle\Filter\FieldSearch;

/**
 * @Annotation
 */
class NotNullFieldSearch extends FieldSearch
{
    public $skiponfalse = false;
}