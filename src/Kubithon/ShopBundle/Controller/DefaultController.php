<?php

namespace Kubithon\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class DefaultController
 * @package Kubithon\ShopBundle\Controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="shop_home")
     */
    public function indexAction()
    {
        return $this->render('shop/home.html.twig');
    }

    /**
     * @Route("/items", name="shop_items")
     */
    public function itemsAction()
    {
        $items = $this
            ->getDoctrine()
            ->getRepository('KubithonShopBundle:Product')
            ->findByActivated(true);

        return $this->render('shop/items.html.twig', ['items' => $items]);
    }
}
