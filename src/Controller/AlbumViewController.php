<?php
// src/Controller/AlbumViewController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Service\TokenManager;

class AlbumViewController extends AbstractController
{
     /**
     * Matches /
     *
     *
     * @Route("/album", name="albumview")
     */
    public function albumview(Request $request) 
    {
        $tokenManager = new TokenManager();
        $user = $this->getUser();
        $token=$tokenManager->createToken('USER',$user->getId());
        $id_album=$request->query->get('id_album');
        if ($id_album==NULL)
            $id_album='false';
          //  var_dump($id_album);die;

        return $this->render('album/album.html.twig', [
            'idAlbum'=>$id_album,
            'token'=>$token
        ]);
    }

     /**
     * Matches /
     *
     *
     * @Route("/albumpublic/{idAlbum}", name="albumpublicview")
     * requirements = {"idAlbum"="\d"}

     */
    public function albumPublicView(int $idAlbum,Request $request) 
    {

        $hash=$request->query->get('hash');
        if ($hash==NULL)
            $hash='false';

        return $this->render('public.html.twig', [
            'idAlbum'=>$idAlbum,
            'hash'=>$hash
        ]);
    }
}

