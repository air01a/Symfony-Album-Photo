<?php
// src/Controller/TokenController.php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\TokenCacher;


class TokenController extends AbstractController
{
     /**
     * Matches /
     *
     *
     * @Route("/api/v1/getToken", name="gettoken")
     */
    public function getToken(Request $request, TokenCacher $JWTManager) 
    {
        $user = $this->getUser();
        $token=$JWTManager->createToken($user);
   
        return new JsonResponse(['token' => $token]);
    }
}

