<?php

namespace Samson\Bundle\FilterBundle\Filter;

use Doctrine\Common\Annotations\Annotation;

abstract class FieldSearch
{
    public $propertyPaths = array();

    public function filteredBy()
    {
        return get_class($this).'Filter';
    }

    public final function __construct(array $data)
    {
        $defaultValue = $this->getDefaultValue();
        foreach ($data as $key => $value) {
            if ($defaultValue !== null && $key == 'value' && $value !== null) {
                $key = $defaultValue;
            }

            if ($key == 'propertyPath') {
                $key = 'propertyPaths';
                $value = (array) $value;
            }

            $this->$key = $value;
        }
    }

    public function getDefaultValue()
    {
        return null;
    }

    /**
     * Error handler for unknown property accessor in Annotation class.
     *
     * @param string $name Unknown property name
     */
    public function __get($name)
    {
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, get_class($this))
        );
    }

    /**
     * Error handler for unknown property mutator in Annotation class.
     *
     * @param string $name  Unkown property name
     * @param mixed  $value Property value
     */
    public function __set($name, $value)
    {
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, get_class($this))
        );
    }
}