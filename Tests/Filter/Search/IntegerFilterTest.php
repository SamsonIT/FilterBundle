<?php

namespace Samson\Bundle\FilterBundle\Tests\Filter\Search;

use Samson\Bundle\FilterBundle\Filter\Search\IntegerFieldSearch;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Samson\Bundle\FilterBundle\Filter\Search\IntegerFilter;

class IntegerFilterTest extends FieldFilterTest
{

    /**
     * @dataProvider data
     */
    public function testFilter($field, $value, $fieldSearch, $expectedExpr, $expectedParameters)
    {

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();
        $parameterNameGenerator = $this->getParameterNameGenerator();
        $filter = new IntegerFilter($parameterNameGenerator, $qb);

        list($expr, $parameters) = $filter->filter($field, $value, $fieldSearch);

        $this->assertEquals($expectedExpr, (string) $expr);
        $this->assertEquals($expectedParameters, $parameters);
    }

    public function data()
    {
        return array(
            array('id', '4', new IntegerFieldSearch(array('value' => 'equals')), 'id = :f0', array('f0' => '4')),
            array('id', '4', new IntegerFieldSearch(array('value' => 'is not equal to')), 'id <> :f0', array('f0' => '4')),
            array('test', '100', new IntegerFieldSearch(array('value' => '>')), 'test > :f0', array('f0' => '100')),
            array('id', '4', new IntegerFieldSearch(array('value' => '<=')), 'id <= :f0', array('f0' => '4')),
            array('id', '4', new IntegerFieldSearch(array('value' => 'is greater than or equal to')), 'id >= :f0', array('f0' => '4')),
        );
    }
}