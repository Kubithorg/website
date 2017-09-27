<?php

namespace Kubithon\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="products")
 */
class Products
{
    private $id;

    private $name;

    private $price;

    private $image;

    private $description;



}