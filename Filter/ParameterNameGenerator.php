<?php

namespace Samson\Bundle\FilterBundle\Filter;

class ParameterNameGenerator
{
    private $counter = 0;

    private $prefix;

    public function __construct($prefix = 'f')
    {
        $this->prefix = $prefix;
    }

    public function generate()
    {
        return $this->prefix.($this->counter++);
    }
}