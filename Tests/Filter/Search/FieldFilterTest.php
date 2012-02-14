<?php

namespace Samson\Bundle\FilterBundle\Tests\Filter\Search;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FieldFilterTest extends WebTestCase
{

    public function getParameterNameGenerator()
    {

        $p = $this->getMock('Samson\Bundle\FilterBundle\Filter\ParameterNameGenerator');
        $p->expects($this->any())->method('generate')->will($this->returnValue('f0'));

        return $p;
    }
}