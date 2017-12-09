<?php

namespace Kubithon\ShopBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

    /**
     * @Route("/credit", name="shop_credit")
     * @Security("has_role('ROLE_USER')")
     */
    public function creditAction()
    {
        return $this->render('shop/credits.html.twig');
    }
}
