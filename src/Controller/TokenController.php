<?php
// src/Controller/TokenController.php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;


class TokenController extends AbstractController
{
     /**
     * Matches /
     *
     *
     * @Route("/getToken", name="gettoken")
     */
    public function getToken(Request $request, JWTTokenManagerInterface $JWTManager) 
    {
        $user = $this->getUser();
        $user->setApiKey("ezeez");

        //$JWTManager = $this->container->get('lexik_jwt_authentication.jwt_manager');

        $token = $JWTManager
           ->create($user);
        return new JsonResponse(['token' => $token]);
    }
}

