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
            'goals_hash' => $donations_bar_hash
        ]);
    }

    private function getStreamsData()
    {
        $streams_repository = $this
            ->getDoctrine()
            ->getRepository('AppBundle:Stream');

        $main_stream = $streams_repository->findOneBy(['is_main' => true, 'is_enabled' => true]);
        $streams = $streams_repository->findBy(['is_main' => false, 'is_enabled' => true]);

        $streams_data = [
            'main_stream' => $main_stream,
            'streams'     => $streams
        ];

        $streams_hash = sha1(serialize($streams_data));

        return array_merge($streams_data, [
            'streams_hash' => $streams_hash
        ]);
    }

    /**
     * @Route("/", name="homepage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $donations_bar_data = $this->getDonationsBarData();
        $streams_data = $this->getStreamsData();

        return $this->render('default/index.html.twig', array_merge([
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ], $donations_bar_data, $streams_data));
    }

    /**
     * @Route("/update", name="homepage_update")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction()
    {
        $donations_bar_data = $this->getDonationsBarData();
        $stream_data = $this->getStreamsData();

        $twig = $this->container->get('twig');

        try
        {
            $goals_html = $twig->render('default/goals_bar.part.html.twig', $donations_bar_data);
        }
        catch (\Twig_Error $e)
        {
            $goals_html = null;
        }

        try
        {
            $streams_html = $twig->render('default/streams.part.html.twig', $stream_data);
        }
        catch (\Twig_Error $e)
        {
            $streams_html = null;
        }

        return $this->json([
            'current_gain' => $donations_bar_data['current_gain'],
            'max_gain' => $donations_bar_data['max_gain'],

            'goals_hash' => $donations_bar_data['goals_hash'],
            'goals_html' => trim($goals_html),

            'streams_hash' => $stream_data['streams_hash'],
            'streams_html' => trim($streams_html)
        ]);
    }

    /**
     * @Route("/goals-update", name="legacy_goals_update")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function legacyUpdateAction()
    {
        return $this->redirectToRoute('homepage_update');
    }

    /**
     * @Route("/%20", name="broken_link_from_telethon")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function brokenLinkFromTelethonWebsite()
    {
        return $this->redirectToRoute('homepage');
    }
}
