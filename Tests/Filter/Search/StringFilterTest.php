<?php

namespace Samson\Bundle\FilterBundle\Tests\Filter\Search;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Samson\Bundle\FilterBundle\Filter\Search\StringFilter;
use Samson\Bundle\FilterBundle\Filter\Search\String;

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
            array('name', 'Sam', new String(array('value' => 'begins_with')), 'name LIKE :f0', array('f0' => 'Sam%')),
            array('name', 'Samson IT', new String(array('value' => 'equals')), 'name = :f0', array('f0' => 'Samson IT')),
            array('name', 'n IT', new String(array('value' => 'ends_with')), 'name LIKE :f0', array('f0' => '%n IT')),
            array('name', 'mso', new String(array('value' => 'contains')), 'name LIKE :f0', array('f0' => '%mso%')),
        );
    }
}