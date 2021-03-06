<?php

namespace Kubithon\ApiBundle\Controller;

use AppBundle\Entity\JoinSession;
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
 * @Route("/sessionserver")
 */
class SessionServerController extends Controller
{
    use MinecraftResponseAwareTrait;
    use MinecraftRequestAwareTrait;

    /**
     * @Route("/join")
     * @Method("POST")
     * @param Request $req
     * @return Response
     */
    public function joinAction(Request $req)
    {
        $request = $this->parseRequest($req);

        $accessToken = $request->accessToken ?? null;
        $selectedProfile = $request->selectedProfile ?? null;
        $serverId = $request->serverId ?? null;

        if (!$accessToken || !$selectedProfile || !$serverId)
            return $this->errorBadRequestResponse();

        $session = $this
            ->getDoctrine()
            ->getRepository(Session::class)
            ->findOneBy([
                'access' => $accessToken
            ]);

        if (!$session)
            return $this->errorForbidenResponse();

        $user = $session->getUser();

        if ($user->getUuid() !== $selectedProfile)
            return $this->errorForbidenResponse();

        if ($session->getJoinSession() != null) {

            $em = $this->getDoctrine()->getManager();
            $session = $em->getRepository(Session::class)->find($session->getId());
            $joinId = $session->getJoinSession()->getId();
            $session->setJoinSession(null);
            $em->flush();

            $em = $this->getDoctrine()->getManager();
            $join = $em->getRepository(JoinSession::class)->find($joinId);
            $em->remove($join);
            $em->flush();
        }

        $join = new JoinSession();
        $join->setAccess($accessToken);
        $join->setServerId($serverId);
        $join->setIp($req->getClientIp());
        $join->setUsername($user);
        $join->setSession($session);

        $em = $this->getDoctrine()->getManager();
        $em->persist($join);
        $em->flush();

        $em = $this->getDoctrine()->getManager();
        $session = $em->getRepository(Session::class)->find($session);
        $session->setJoinSession($join);
        $em->flush();


        return new Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/hasJoined")
     * @Method("GET")
     * @param Request $request
     * @return Response
     */
    public function hasJoinedAction(Request $request)
    {


        $username = $request->get('username') ?? null;
        $serverId = $request->get('serverId') ?? null;

             
        $em = $this->getDoctrine()->getManager();

        $joinSession = $em
            ->getRepository(JoinSession::class)
            ->findOneBy([
                'username' => $username,
                'serverId' => $serverId
            ]);

        if ($joinSession) {

            $uuid = $joinSession->getSession()->getUser()->getUuid();

            $em = $this->getDoctrine()->getManager();
            $session = $em->getRepository(Session::class)->find($joinSession->getSession()->getId());
            $session->setJoinSession(null);
            $em->flush();

            $em->remove($joinSession);
            $em->flush();

            $uuid = $joinSession->getSession()->getUser()->getUuid();

            return new JsonResponse([
                'id' => $uuid,
                'name' => $username,
                'properties' => [$this->profile($username, $uuid)]
            ]);

        } else {
            $mojangResponse = file_get_contents('https://api.mojang.com/users/profiles/minecraft/' . $username);
            $mojangResponse = json_decode($mojangResponse);

            if (isset($mojangResponse->name)) {
                $mojangResponse = file_get_contents("https://sessionserver.mojang.com/session/minecraft/hasJoined?username=$username&serverId=$serverId");
                return new Response($mojangResponse);

            }
        }


        return new Response();
            
    }

}
