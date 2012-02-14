<?php

namespace Samson\Bundle\FilterBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class FilterValues implements FilterDataInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Samson\Bundle\SecurityBundle\Entity\User")
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="boolean")
     */
    private $remember = true;

    /**
     * @ORM\Column(type="string")
     */
    private $filterType;

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param field_type $user
     * @return string
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param field_type $data
     * @return string
     */
    public function setData($data)
    {
        if (is_object($data)) {
            throw new \Exception('?');
        }
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getRemember()
    {
        return $this->remember;
    }

    /**
     * @param field_type $remember
     * @return string
     */
    public function setRemember($remember)
    {
        $this->remember = $remember;
    }

    /**
     * @return string
     */
    public function getFilterType()
    {
        return $this->filterType;
    }

    /**
     * @param field_type $filterType
     * @return string
     */
    public function setFilterType($filterType)
    {
        $this->filterType = $filterType;
    }
}