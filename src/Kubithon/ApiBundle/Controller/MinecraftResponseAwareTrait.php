<?php

namespace Kubithon\ApiBundle\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait MinecraftResponseAwareTrait
{
    public function errorResponse($error, $message, $code)
    {
        $datas = [
            'error' => $error,
            'errorMessage' => $message
        ];

        return new JsonResponse($datas, $code);
    }

    public function errorMediaTypeResponse()
    {
        return $this->errorResponse('Unsupported Media Type','The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

}