<?php

namespace Samson\Bundle\FilterBundle\Tests\Filter\Search;

use Samson\Bundle\FilterBundle\Filter\Search\DateFieldSearch;
use Samson\Bundle\FilterBundle\Filter\Search\DateFilter;

class DateFilterTest extends FieldFilterTest
{

    /**
     * @dataProvider data
     */
    public function testFilter($field, $value, $fieldSearch, $expectedExpr, $expectedParameters)
    {
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();
        $parameterNameGenerator = $this->getParameterNameGenerator();
        $filter = new DateFilter($parameterNameGenerator, $qb);

        list($expr, $parameters) = $filter->filter($field, $value, $fieldSearch);

        $this->assertEquals($expectedExpr, (string) $expr);
        $this->assertEquals($expectedParameters, $parameters);
    }

    public function data()
    {
        return array(
            array('startTime', '2010-01-01', new DateFieldSearch(array('value' => 'equals')), 'DATE(startTime) = :f0', array('f0' => '2010-01-01')),
            array('startTime', '2011-10-07', new DateFieldSearch(array('value' => 'is not equal to')), 'DATE(startTime) <> :f0', array('f0' => '2011-10-07')),
        );
    }
}