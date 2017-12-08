<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/home", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $current_gain = 300;
        $max_gain = 2000;

        $goals = $em->createQuery('
            SELECT
                g.description,
                g.amount,
                ((g.amount + 0.0) / (:max_gain + 0.0)) AS percentage
            FROM
                AppBundle:Goal g
            WHERE
                g.amount >= :current_gain AND
                g.amount <= :max_gain
            ORDER BY
                g.amount ASC'
        )->setParameter('current_gain', $current_gain)
         ->setParameter('max_gain', $max_gain)
         ->getResult();

        $streams_repository = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Stream');

        $main_stream = $streams_repository->findOneBy(['is_main' => true]);
        $streams = $streams_repository->findBy(['is_main' => false]);

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'current_gain' => $current_gain,
            'max_gain' => $max_gain,
            'goals' => $goals,
            'main_stream' => $main_stream,
            'streams' => $streams
        ]);
    }

    /**
     * @Route("/", name="homepage_temp")
     */
    public function indexTempAction(Request $request)
    {
        return $this->render('default/index.temp.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
}
