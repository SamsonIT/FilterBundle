<?php

namespace Samson\Bundle\FilterBundle\Filter\Search;

use Samson\Bundle\FilterBundle\Filter\FieldSearch;

/**
 * @Annotation
 */
class BooleanFieldSearch extends FieldSearch
{
    public $skiponfalse = false;
}