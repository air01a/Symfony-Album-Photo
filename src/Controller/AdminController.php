<?php
// src/Controller/AdminController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Services\TokenCacher;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AdminController extends AbstractController
{
     /**
     * Matches /
     *
     *
     * @Route("/admin", name="adminview")
     */
    public function adminview(Request $request, TokenCacher $JWTManager) 
    {
       // if (!$this->getUser()->isGranted('ROLE_ADMIN'))
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
            throw $this->createAccessDeniedException('GET OUT!');
            
        $id_album=$request->query->get('id_album');
        if ($id_album==NULL)
            $id_album='false';

        $user = $this->getUser();
        $token=$JWTManager->createToken($user);

        return $this->render('album/album.html.twig', [
            'idAlbum'=>$id_album,
            'token'=>$token,
            'tokenAlbum'=>false,
            'admin'=>true
        ]);
    }

   
}

