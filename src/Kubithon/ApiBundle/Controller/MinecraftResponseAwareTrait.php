<?php

namespace Kubithon\ApiBundle\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;

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

}