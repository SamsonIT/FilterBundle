<?php

namespace Samson\Bundle\FilterBundle\Filter;

use Doctrine\ORM\QueryBuilder;

abstract class FieldFilter
{
    private $parameterNameGenerator;

    private $queryBuilder;

    final public function __construct(ParameterNameGenerator $parameterNameGenerator, QueryBuilder $queryBuilder)
    {
        $this->parameterNameGenerator = $parameterNameGenerator;
        $this->queryBuilder = $queryBuilder;
    }

    public function generateParameterName()
    {
        return $this->parameterNameGenerator->generate();
    }

    protected function getQueryBuilder()
    {
        return $this->queryBuilder;
    }
}