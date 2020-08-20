<?php
// src/Controller/RightController.php
namespace App\Controller;

use App\Entity\Album;
use App\Entity\Right;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;


class RightController extends AbstractFOSRestController
{
    

    /**
     * @Rest\Post("/api/v1/albums/{id}/rights")
     * @Rest\View

     */
    public function updateAction(Album $album, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $album);
        $em=$this->getDoctrine()->getManager();

        foreach($album->getRights() as $right)
        {
          $em->remove($right);
        }
        $em->flush();
        $datas = json_decode($request->getContent());
        foreach ($datas as $data) {
            $right = new Right();
            $user = $em->getRepository('App:User')->findOneBy(array('id'=>$data->id));
            $right->setUser($user);
            $right->setAlbum($album);
            $em->persist($right);
        }
        $em->flush();


        return $this->getAction($album);
    }



    /**
     * @Rest\Get("/api/v1/albums/{id}/rights")
     *  requirements = {"id"="\d+"}
     * @Rest\View
     * 
     */
    public function getAction(Album $album) {
        $rights = $this->getDoctrine()->getRepository('App:Right')->search($album->getId());
        $result=array();        
        foreach($rights as $right){
            $line=array('id'=>$right->getId(),'idUser'=>$right->getUser()->getId(),'userName'=>$right->getUser()->getUsername());
            array_push($result,$line);
        }
        return $result;
    }
}
