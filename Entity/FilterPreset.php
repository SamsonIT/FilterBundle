<?php

namespace Samson\Bundle\FilterBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"user_id","name","filterType"})})
 */
class FilterPreset implements FilterDataInterface
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
     * @ORM\Column
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $data;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $filterType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $public = true;

    private $fixed = false;

    public function getId()
    {
        return $this->id;
    }

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
        $this->data = $data;
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

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param field_type $name
     * @return string
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function isPublic()
    {
        return $this->public;
    }

    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * @return string
     */
    public function isFixed()
    {
        return $this->fixed;
    }

    /**
     * @param field_type $fixed
     * @return string
     */
    public function setFixed($fixed)
    {
        $this->fixed = $fixed;
    }
}