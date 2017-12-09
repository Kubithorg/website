<?php

namespace AppBundle\Controller;

use AppBundle\Entity\LoginSession;
use AppBundle\Entity\User;
use Predis\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginController extends Controller
{

    /**
     * @Route("/mojang/login", name="mojang_login")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $username = $request->get('_username');

        $mojangResponse = file_get_contents("https://api.mojang.com/users/profiles/minecraft/$username");
        $mojangResponse = json_decode($mojangResponse);

        if (!isset($mojangResponse->id)) {
            $this->addFlash('error', '<stong>Erreur : </stong> Ce pseudo ne semble pas etre attribué à un compte minecraft officiel');
            return $this->redirectToRoute('fos_user_security_login');
        }

        $em = $this->getDoctrine()->getManager();

        $login = $em->getRepository(LoginSession::class)
            ->findOneBy([
                'username' => $username,
                'uuid' => $mojangResponse->id
            ]);

        if ($login)
            $em->remove($login);
        $em->flush();

        $login = new LoginSession();
        $login->setUsername($username);
        $login->setUuid($mojangResponse->id);
        $login->setToken($this->genUuid());

        $em = $this->getDoctrine()->getManager();
        $em->persist($login);
        $em->flush();

        $redis = new Client([
            'scheme' => 'tcp',
            'host' => '31.172.164.45',
            'port' => 38714,
            'password' => 'T{]3}fKYyxXDH>dIfzvHVjij[>]F8N_jPL4JHxmhgNeg316YTH(8NOvmUvNtvIK}',
        ]);

        $redis->publish('login', json_encode([
            'uuid' => $mojangResponse->id,
            'url' => $this->generateUrl('mojang_confirm', ['token' => $login->getToken()], UrlGeneratorInterface::ABSOLUTE_URL)
        ]));


        $this->addFlash('success', '<stong>Succès : </stong> Pour continuer la connexion confirmez votre connexion en jeu.');
        return $this->redirectToRoute('fos_user_security_login');
    }

    private function genUuid()
    {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * @Route("/mojang/confirm/{token}", name="mojang_confirm")
     * @param LoginSession|null $loginSession
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function confirmAction(LoginSession $loginSession = null, Request $request)
    {

        if (!$loginSession) {
            $this->addFlash('error', '<stong>Erreur : </stong> Impossible de confirmer votre compte, veuillez réesayer !');
            return $this->redirectToRoute('fos_user_security_login');
        }

        $em = $this->getDoctrine()->getManager();

        $user = $em
            ->getRepository(User::class)
            ->findOneByUuid($loginSession->getUuid());


        if (!$user) {
            $user = new User();
            $user->setUsername($loginSession->getUsername());
            $user->setEmail(uniqid());
            $user->setUuid($loginSession->getUuid());
            $user->setPlainPassword($this->genUuid());
            $user->setCrack(false);
            $user->setBalance(0);
            $user->setRoles([]);
            $em->persist($user);
            $em->flush();
        }


        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));

        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

        $em = $this->getDoctrine()->getManager();
        $em->remove($loginSession);
        $em->flush();

        $this->addFlash('success', '<stong>Succès : </stong> Vous êtes désormais connecté !');
        return $this->redirectToRoute('shop_home');
    }
}
