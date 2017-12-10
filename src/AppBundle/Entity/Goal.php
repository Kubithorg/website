<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Goal
 *
 * @ORM\Table(name="goal")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GoalRepository")
 */
class Goal
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=512)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * @var bool
     *
     * @ORM\Column(name="achieved", type="boolean")
     */
    private $achieved = false;

    /**
     * @var string
     *
     * @ORM\Column(name="achieved_message", type="text", nullable=true)
     */
    private $achievedMessage = null;

    /**
     * @var string
     *
     * @ORM\Column(name="achieved_longmessage", type="text", nullable=true)
     */
    private $achievedLongmessage = null;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Goal
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     *
     * @return Goal
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set achieved
     *
     * @param boolean $achieved
     *
     * @return Goal
     */
    public function setAchieved($achieved)
    {
        $this->achieved = $achieved;

        return $this;
    }

    /**
     * Get achieved
     *
     * @return bool
     */
    public function getAchieved()
    {
        return $this->achieved;
    }

    /**
     * Set achievedMessage
     *
     * @param string $achievedMessage
     *
     * @return Goal
     */
    public function setAchievedMessage($achievedMessage)
    {
        $this->achievedMessage = $achievedMessage;

        return $this;
    }

    /**
     * Get achievedMessage
     *
     * @return string
     */
    public function getAchievedMessage()
    {
        return $this->achievedMessage;
    }

    /**
     * Set achievedLongmessage
     *
     * @param string $achievedLongmessage
     *
     * @return Goal
     */
    public function setAchievedLongmessage($achievedLongmessage)
    {
        $this->achievedLongmessage = $achievedLongmessage;

        return $this;
    }

    /**
     * Get achievedLongmessage
     *
     * @return string
     */
    public function getAchievedLongmessage()
    {
        return $this->achievedLongmessage;
    }
}
