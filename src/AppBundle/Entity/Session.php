<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sessions")
 */
class Session
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    private $access;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $client;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", mappedBy="session")
     */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\JoinSession", inversedBy="session", cascade={"remove"})
     * @ORM\JoinColumn(name="join_session_id", referencedColumnName="id", nullable=true)
     */
    private $joinSession;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param mixed $access
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getJoinSession()
    {
        return $this->joinSession;
    }

    /**
     * @param mixed $joinSession
     */
    public function setJoinSession($joinSession)
    {
        $this->joinSession = $joinSession;
    }

}