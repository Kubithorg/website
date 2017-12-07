<?php

namespace Kubithon\ApiBundle\Controller;

use AppBundle\Entity\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthServerController
 * @package Kubithon\ApiBundle\Controller
 * @Route("/authserver")
 */
class AuthServerController extends Controller
{
    use MinecraftResponseAwareTrait;
    use MinecraftRequestAwareTrait;

    /**
     * @Route("/")
     */
    public function serverAction()
    {
        $info = array(
            'Status'					=>	'OK',
            'Runtime-Mode'				=>	'productionMode',
            'Application-Author' 		=>	'Uneo7',
            'Application-Description'	=>	'Kubithon Auth Server.',
            'Specification-Version'		=>	'42',
            'Application-Name'			=>	'kubithon.auth_server',
            'Implementation-Version' 	=>	'42',
            'Application-Owner' 		=>	'Kubithon',
        );

        return new JsonResponse($info);
    }

    /**
     * @Route("/authenticate")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function authenticateAction(Request $request)
    {
        $agent = $this->getUserAgent($request);

        $password = $request->get('password');
        $user = $request->get('username');
        $clientToken = $request->get('clientToken');

        if (!$user || !$password || !$clientToken)
            $this->errorBadRequestResponse();

        $user_manager = $this->get('fos_user.user_manager');
        $factory = $this->get('security.encoder_factory');

        $user = $request->get('username');
        $user = $user_manager->findUserByUsername($user);

        if($user === null)
            $this->errorAccessDeniedResponse();

        $password = $request->get('password');
        $encoder = $factory->getEncoder($user);

        if(!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt()))
            $this->errorAccessDeniedResponse();

        if($user->getSession()) {

            $em = $this->getDoctrine()->getManager();
            $session = $em->getRepository(Session::class)->find($user->getSession());
            $session->setAccess($this->genUuid());
            $session->setClient($clientToken);
            $session->setUser($user);
            $em->flush();

        } else {

            $session = new Session();
            $session->setAccess($this->genUuid());
            $session->setClient($clientToken);
            $session->setUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($session);
            $em->flush();

            $user->setSession($session);
            $user_manager->updateUser($user);

        }

        $response = array(
            'accessToken' => $session->getAccess(),
            'clientToken' => $session->getClient(),
        );

        return new JsonResponse($response);
    }

    /**
     * @Route("/refresh")
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshAction(Request $request)
    {
        $agent = $this->getUserAgent($request);

        $accessToken = $request->get('accessToken');
        $clientToken = $request->get('clientToken');

        if (!$accessToken || !$clientToken)
            $this->errorBadRequestResponse();

        $em = $this->getDoctrine()->getManager();
        $session = $em
            ->getRepository(Session::class)
            ->findOneBy(['access' => $accessToken, 'client' => $clientToken]);

        if (!$session)
            $this->errorForbidenResponse();

        $session->setAccess($this->genUuid());
        $session->setClient($clientToken);
        $em->flush();

        $response = array(
            'accessToken' => $session->getAccess(),
            'clientToken' => $session->getClient(),
        );

        return new JsonResponse($response);
    }

    /**
     * @Route("/validate")
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function validateAction(Request $request)
    {
        $agent = $this->getUserAgent($request);

        $accessToken = $request->get('accessToken');
        $clientToken = $request->get('clientToken');

        if (!$accessToken || !$clientToken)
            $this->errorForbidenResponse();

        $em = $this->getDoctrine()->getManager();
        $session = $em
            ->getRepository(Session::class)
            ->findOneBy(['access' => $accessToken, 'client' => $clientToken]);

        if (!$session)
            $this->errorForbidenResponse();

        return new Response(null, 204);
    }

    /**
     * @Route("/signout")
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function signoutAction(Request $request)
    {
        $agent = $this->getUserAgent($request);

        $password = $request->get('password');
        $user = $request->get('username');

        if (!$user || !$password)
            $this->errorBadRequestResponse();

        $user_manager = $this->get('fos_user.user_manager');
        $factory = $this->get('security.encoder_factory');

        $user = $request->get('username');
        $user = $user_manager->findUserByUsername($user);

        if($user === null)
            $this->errorAccessDeniedResponse();

        $password = $request->get('password');
        $encoder = $factory->getEncoder($user);

        if(!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt()))
            $this->errorAccessDeniedResponse();

        $session = $user->getSession();

        $user->setSession(null);
        $user_manager->updateUser($user);

        $em = $this
            ->getDoctrine()
            ->getManager();

        $session = $em->getRepository(Session::class)->find($session);
        $em->remove($session);
        $em->flush();

        return new Response(null, 204);
    }

    /**
     * @Route("/invalidate")
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function invalidateAction(Request $request)
    {
        $agent = $this->getUserAgent($request);

        $accessToken = $request->get('accessToken');
        $clientToken = $request->get('clientToken');

        if (!$accessToken || !$clientToken)
            $this->errorForbidenResponse();

        $em = $this->getDoctrine()->getManager();
        $session = $em
            ->getRepository(Session::class)
            ->findOneBy(['access' => $accessToken, 'client' => $clientToken]);

        if (!$session)
            $this->errorForbidenResponse();

        $user = $session->getUser();
        $user_manager = $this->get('fos_user.user_manager');
        $user->setSession(null);
        $user_manager->updateUser($user);

        $em->remove($session);
        $em->flush();

        return new Response(null, 204);

    }

    private function genUuid()
    {
        return sprintf( '%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );

    }

}
