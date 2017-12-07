<?php

namespace Kubithon\ApiBundle\Controller;

use AppBundle\Entity\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        $request = $this->parseRequest($request);

        $user = $request->username ?? null;
        $password = $request->password ?? null;
        $clientToken = $request->clientToken ?? null;

        if (!$user || !$password || !$clientToken)
            return $this->errorBadRequestResponse();

        $user_manager = $this->get('fos_user.user_manager');
        $factory = $this->get('security.encoder_factory');

        $user = $user_manager->findUserByUsername($user);

        if($user === null)
            $this->errorInvalidCredentialsResponse();

        $encoder = $factory->getEncoder($user);

        if(!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt()))
            $this->errorInvalidCredentialsResponse();

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
            'selectedProfile' => [
                'id' => $user->getUuid(),
                'name' => $user->getUsername(),
                'legacy' => false
            ]
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
        $request = $this->parseRequest($request);

        $accessToken = $request->accessToken ?? null;
        $clientToken = $request->clientToken ?? null;

        if (!$accessToken || !$clientToken)
            $this->errorBadRequestResponse();

        $em = $this->getDoctrine()->getManager();
        $session = $em
            ->getRepository(Session::class)
            ->findOneBy(['access' => $accessToken, 'client' => $clientToken]);

        if (!$session)
            $this->errorInvalidCredentialsResponse();

        $session->setAccess($this->genUuid());
        $session->setClient($clientToken);
        $em->flush();

        $response = array(
            'accessToken' => $session->getAccess(),
            'clientToken' => $session->getClient(),
            'selectedProfile' => [
                'id' => $session->getUser()->getUuid(),
                'name' => $session->getUser()->getUsername(),
                'legacy' => false
            ]
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
        $request = $this->parseRequest($request);

        $accessToken = $request->accessToken ?? null;
        $clientToken = $request->clientToken ?? null;

        if (!$accessToken || !$clientToken)
            $this->errorBadRequestResponse();

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
        $request = $this->parseRequest($request);

        $user = $request->username ?? null;
        $password = $request->password ?? null;

        if (!$user || !$password)
            $this->errorBadRequestResponse();

        $user_manager = $this->get('fos_user.user_manager');
        $factory = $this->get('security.encoder_factory');


        $user = $user_manager->findUserByUsername($user);

        if($user === null)
            $this->errorInvalidCredentialsResponse();

        $encoder = $factory->getEncoder($user);

        if(!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt()))
            $this->errorInvalidCredentialsResponse();

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
        $request = $this->parseRequest($request);

        $accessToken = $request->accessToken ?? null;
        $clientToken = $request->clientToken ?? null;

        if (!$accessToken || !$clientToken)
            $this->errorForbidenResponse();

        $em = $this->getDoctrine()->getManager();
        $session = $em
            ->getRepository(Session::class)
            ->findOneBy(['access' => $accessToken, 'client' => $clientToken]);

        if (!$session)
            $this->errorInvalidCredentialsResponse();

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
