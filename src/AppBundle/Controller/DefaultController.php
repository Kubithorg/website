<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Config;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    private function config($entry_key, $default = null)
    {
        $cfg = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Config')
            ->findOneBy(['entry' => $entry_key]);

        if ($cfg == null) return $default;
        return $cfg->getValue();
    }

    private function getDonationsBarData()
    {
        $em = $this->getDoctrine()->getManager();

        $current_gain = $this->config(Config::$CURRENT_AMOUNT, 0);
        $max_gain = $this->config(Config::$MAX_AMOUNT, 2000);

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

        $previous_goal = $em->createQuery('
            SELECT
                g.description,
                g.amount,
                g.achievedMessage,
                ((g.amount + 0.0) / (:max_gain + 0.0)) AS percentage
            FROM
                AppBundle:Goal g
            WHERE
                g.amount < :current_gain
            ORDER BY g.amount DESC
        ')->setParameter('current_gain', $current_gain)
          ->setParameter('max_gain', $max_gain)
          ->setMaxResults(1)
          ->getOneOrNullResult();

        $donations_bar_data = [
            'current_gain'  => $current_gain,
            'max_gain'      => $max_gain,
            'goals'         => $goals,
            'previous_goal' => $previous_goal
        ];

        $donations_bar_hash = sha1(serialize($donations_bar_data));

        return array_merge($donations_bar_data, [
            'hash' => $donations_bar_hash
        ]);
    }

    /**
     * @Route("/", name="homepage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $streams_repository = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Stream');

        $main_stream = $streams_repository->findOneBy(['is_main' => true, 'is_enabled' => true]);
        $streams = $streams_repository->findBy(['is_main' => false, 'is_enabled' => true]);

        $donations_bar_data = $this->getDonationsBarData();

        return $this->render('default/index.html.twig', array_merge([
            'base_dir'      => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'main_stream'   => $main_stream,
            'streams'       => $streams
        ], $donations_bar_data));
    }

    /**
     * @Route("/goals-update", name="goals_update")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateBarAction()
    {
        $donations_bar_data = $this->getDonationsBarData();

        try {
            $goals_html = $this->container->get('twig')->render('default/goals_bar.part.html.twig', $donations_bar_data);
        } catch (\Twig_Error $e) {
            $goals_html = null;
        }

        return $this->json([
            'current_gain' => $donations_bar_data['current_gain'],
            'max_gain' => $donations_bar_data['max_gain'],
            'hash' => $donations_bar_data['hash'],
            'goals_html' => trim($goals_html)
        ]);
    }
}
