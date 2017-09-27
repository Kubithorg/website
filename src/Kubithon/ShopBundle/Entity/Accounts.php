<?php

namespace Kubithon\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="accounts")
 */
class Accounts
{
    private $id;

    private $username;

    private $uuid;

    private $balance;

}