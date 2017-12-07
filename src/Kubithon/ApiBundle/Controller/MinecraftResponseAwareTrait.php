<?php

namespace Kubithon\ApiBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

trait MinecraftResponseAwareTrait
{
    public function errorBadRequestResponse()
    {
        throw new BadRequestHttpException('The server is refusing to service the request because the entity of the request is in a format not supported by the requested resource for the requested method');
    }

    public function errorAccessDeniedResponse()
    {
        throw new AccessDeniedException(' Invalid credentials. Invalid username or password. ');
    }

    public function errorForbidenResponse()
    {
        throw new AccessDeniedHttpException();
    }

}