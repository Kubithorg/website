<?php

namespace Kubithon\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;

trait MinecraftRequestAwareTrait
{

    public function parseRequest(Request $request)
    {
        $content = $request->getContent();

        return json_decode($content);
    }


}