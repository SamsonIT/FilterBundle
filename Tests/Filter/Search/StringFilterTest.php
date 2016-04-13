<?php

namespace Samson\Bundle\FilterBundle\Tests\Filter\Search;

use Samson\Bundle\FilterBundle\Filter\Search\StringFieldSearch;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Samson\Bundle\FilterBundle\Filter\Search\StringFilter;

class StringFilterTest extends FieldFilterTest
{

    /**
     * @dataProvider data
     */
    public function testFilter($field, $value, $fieldSearch, $expectedExpr, $expectedParameters)
    {
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();
        $parameterNameGenerator = $this->getParameterNameGenerator();

        $filter = new StringFilter($parameterNameGenerator, $qb);
        list($expr, $parameters) = $filter->filter($field, $value, $fieldSearch);

        $this->assertEquals($expectedExpr, (string) $expr);
        $this->assertEquals($expectedParameters, $parameters);
    }

    public function data()
    {
        return array(
            array('name', 'Sam', new StringFieldSearch(array('value' => 'begins_with')), 'name LIKE :f0', array('f0' => 'Sam%')),
            array('name', 'Samson IT', new StringFieldSearch(array('value' => 'equals')), 'name = :f0', array('f0' => 'Samson IT')),
            array('name', 'n IT', new StringFieldSearch(array('value' => 'ends_with')), 'name LIKE :f0', array('f0' => '%n IT')),
            array('name', 'mso', new StringFieldSearch(array('value' => 'contains')), 'name LIKE :f0', array('f0' => '%mso%')),
        );
    }
}