<?php

namespace Kubithon\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use MinecraftResponseAwareTrait;

    private $content;

    /**
     * AuthController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $content = $request->getContent();

        if(empty($content))
            return $this->errorMediaTypeResponse();

        $this->content = json_decode($content);
    }

    /**
     * @Route("/authenticate")
     * @Method("POST")
     */
    public function authenticateAction()
    {
        if ($this->content->username === null || $this->content->password === null || $this->content->clientToken === null)
            return $this->errorMediaTypeResponse();

        $user = $this->content->username;
        $password = $this->content->password;
        $clientToken = $this->content->clientToken;

        $response = array(
            'accessToken' => $this->genUuid(),
            'clientToken' => $clientToken,

        );

        return new JsonResponse();

    }

    /**
     * @Route("/refresh")
     * @Method("POST")
     */
    public function refreshAction()
    {
        if ($this->content->username);
    }

    /**
     * @Route("/validate")
     * @Method("POST")
     */
    public function validateAction()
    {
        if ($this->content->username);
    }

    /**
     * @Route("/signout")
     * @Method("POST")
     */
    public function signoutAction()
    {
        if ($this->content->username);
    }

    /**
     * @Route("/invalidate")
     * @Method("POST")
     */
    public function invalidateAction()
    {
        if ($this->content->username);
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
