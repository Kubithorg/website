<?php
namespace AppBundle\Command;

use AppBundle\Entity\Config;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class UpdateGainsCommand extends ContainerAwareCommand
{
    //const TELETHON_URL = 'https://soutenir.afm-telethon.fr/les-youtubeuses-se-bougent-pour-le-telethon';
    const TELETHON_URL = 'https://soutenir.afm-telethon.fr/kubithon';

    protected function configure()
    {
        $this
            ->setName('kubithon:update-gain')
            ->setDescription('Updates the gains from the Telethon website')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Loading…' . "\n");
        $doctrine = $this->getContainer()->get('doctrine');

        $em = $doctrine->getManager();
        $repo = $doctrine->getRepository('AppBundle:Config');

        require_once 'simple_html_dom.php'; // yeah I know

        $telethon = str_get_html(file_get_contents(self::TELETHON_URL));
        $amount = 0;

        foreach ($telethon->find('#info-text > span') as $elem)
        {
            $content = $elem->plaintext;
            if (strpos($content, 'euro') !== false)
            {
                $amount = trim(str_replace(' ', '', str_replace('&euro;', '', $content)));
                break;
            }
        }

        $output->writeln('Found value: ' . $amount . ' €.');

        $cfg_amount = $repo->findOneBy(['entry' => Config::$CURRENT_AMOUNT]);
        if (!$cfg_amount)
        {
            $cfg_amount = new Config();
            $cfg_amount->setEntry(Config::$CURRENT_AMOUNT);
            $cfg_amount->setDescription('Les gains courants (auto mise à jour)');
        }

        $cfg_amount->setValue($amount);
        $em->persist($cfg_amount);
        $em->flush();

        $output->writeln('Amount saved.' . "\n\n");


        $dons = [];

        foreach ($telethon->find('.comment-box') as $elem)
        {
            $text_elem = $elem->find('.text-box');
            $amount_elem = $elem->find('.amount-box');

            if (!$text_elem && !$amount_elem)
                continue;

            $don = [
                'from' => null,
                'amount' => 0,
                'message' => null
            ];

            if ($text_elem)
            {
                $bs = $text_elem[0]->find('span b');
                if (count($bs) == 2)
                {
                    $don['from'] = trim($bs[0]->plaintext);
                }

                $message = trim($text_elem[0]->innertext);
                $pos = strpos($message, '<br/><br/>');
                $message = substr($message, 0, $pos);

                $don['message'] = trim($message);
            }

            if ($amount_elem)
            {
                $don['amount'] = trim(str_replace('&euro;', '', $amount_elem[0]->plaintext));
            }

            $dons[] = $don;
            $output->writeln('Donation from ' . ($don['from'] != null ? $don['from'] : '<anonymous>') . ': ' . $don['amount'] . ' €');
            if ($don['message']) $output->writeln('» ' . $don['message']);
        }

        // TODO STORE AND SEND NEW DONATIONS TO REDIS (diff? how?)
    }
}
