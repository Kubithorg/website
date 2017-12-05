<?php

namespace Kubithon\ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    use MinecraftResponseAwareTrait;

    /**
     * @Route("/")
     */
    public function authenticateAction()
    {
        $infos = array(
            'Status'					=>	'OK',
            'Runtime-Mode'				=>	'productionMode',
            'Application-Author' 		=>	'Uneo7',
            'Application-Description'	=>	'Kubithon Auth Server.',
            'Specification-Version'		=>	'42',
            'Application-Name'			=>	'kubithon.auth_server',
            'Implementation-Version' 	=>	'42',
            'Application-Owner' 		=>	'Kubithon',
        );

        return new JsonResponse($infos);
    }



}
