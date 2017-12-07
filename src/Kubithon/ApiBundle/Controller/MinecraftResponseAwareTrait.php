<?php

namespace Kubithon\ApiBundle\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait MinecraftResponseAwareTrait
{
    public function errorResponse($error, $message, $code)
    {
        $data = [
            'error' => $error,
            'errorMessage' => $message
        ];

        return new JsonResponse($data, $code);
    }

    public function errorBadRequestResponse()
    {
        return $this->errorResponse('Unsupported Media Type','The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    public function errorInvalidCredentialsResponse()
    {
        return $this->errorResponse('Identifiants invalides',' Le nom d\'utilisateur ou le mot de passe est invalide', Response::HTTP_FORBIDDEN);
    }

    public function errorForbidenResponse()
    {
        return $this->errorResponse('','',403);
    }

}