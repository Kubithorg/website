<?php

namespace Kubithon\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Request;

trait MinecraftRequestAwareTrait
{

    public function getUserAgent(Request $request)
    {
        $agent = $request->headers->get('User-Agent');
        $agent = explode('/', $agent);

        $return = null;

        if ($agent[0] === 'Minecraft/1')
            $return = 'Minecraft/1';


        return $return;

    }


}