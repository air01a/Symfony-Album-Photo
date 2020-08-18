<?php
// src/Controller/AlbumViewController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Services\TokenCacher;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AlbumViewController extends AbstractController
{
     /**
     * Matches /
     *
     *
     * @Route("/album", name="albumview")
     */
    public function albumview(Request $request, TokenCacher $JWTManager) 
    {
        $id_album=$request->query->get('id_album');
        if ($id_album==NULL)
            $id_album='false';

        $user = $this->getUser();
        $token=$JWTManager->createToken($user);

        return $this->render('album/album.html.twig', [
            'idAlbum'=>$id_album,
            'token'=>$token,
            'tokenAlbum'=>false
        ]);
    }

     /**
     * Matches /
     *
     *
     * @Route("/albumpublic/{idAlbum}", name="albumpublicview")
     * requirements = {"idAlbum"="\d"}

     */
    public function albumPublicView(int $idAlbum,Request $request, TokenCacher $JWTManager) 
    {

        $tokenAlbum=$request->query->get('token');
        if ($tokenAlbum==NULL)
            $tokenAlbum='false';
        else {
            $user = $this->getUser();
            if ($user==null)
            {
                $user = $this->getDoctrine()
                    ->getRepository('App:User')
                    ->find(0);
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    $this->container->get('security.token_storage')->setToken($token);
                    $this->container->get('session')->set('_security_main', serialize($token));
            }
            $user->setApiKey($tokenAlbum);
            $token=$JWTManager->createToken($user);
        }

            return $this->render('album/album.html.twig', [
                'idAlbum'=>$idAlbum,
                'token'=>$token,
                'tokenAlbum'=>$tokenAlbum
            ]);
    }
}

