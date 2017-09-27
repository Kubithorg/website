<?php

namespace Kubithon\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class DefaultController
 * @package Kubithon\ShopBundle\Controller
 * @Route("/boutique")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('KubithonShopBundle:Default:index.html.twig');
    }
}
