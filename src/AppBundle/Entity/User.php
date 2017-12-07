<?php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    private $crack;

    /**
     * @ORM\Column(type="string")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string")
     */
    private $balance;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Session", inversedBy="user")
     * @ORM\JoinColumn(name="session_id", referencedColumnName="id", nullable=true)
     */
    private $session;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param mixed $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return mixed
     */
    public function getCrack()
    {
        return $this->crack;
    }

    /**
     * @param mixed $crack
     */
    public function setCrack($crack)
    {
        $this->crack = $crack;
    }

    /**
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param $session
     */
    public function setSession($session)
    {
        $this->session = $session;
    }


}