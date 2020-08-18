<?php
// src/Controller/mapController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\TokenCacher;

class mapController extends AbstractController
{
     /**
     * Matches /
     *
     *
     * @Route("/map", name="map")
     */
    public function mapShow(TokenCacher $JWTManager) 
    {
       
        $user = $this->getUser();
        $token=$JWTManager->createToken($user);

        return $this->render('map/map.html.twig', ['token'=>$token]);
    }
}

